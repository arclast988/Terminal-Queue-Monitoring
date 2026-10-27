'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const playwright = require('playwright');
const { root, assetContentType, phpBinary } = require('./harness.cjs');
const engine = process.env.TQ_BROWSER || 'chromium';
let browser, server, origin;

function fixture(name, state = '', role = '') {
  const rendered = execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme', state, '', role, 'shared-dialogs'], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
  // Retain real picker and dialog handlers while isolating unrelated polling.
  return rendered.replace(/<script\b([^>]*)>([\s\S]*?)<\/script>/g, (tag, attrs, code) => {
    if (/\bsrc\s*=/.test(attrs)) return /(?:bootstrap\.bundle\.min|interaction-motion|dispatch-rule-times|departure-rule-days|debounce-passengers|queue-sync)\.js/.test(attrs) ? tag : '';
    return /function confirmLogout|window\.showSystemAlert|Time Picker Popover Creation|window\.TerminalMotion|var systemWarningModalEl|btn-toggle-select-rules|const filterBtns|window.showDeleteRuleModal|Role Theme Palettes Sync|Global modal openers for Departure Rules|queueResetTimeToggle|const inputs = document\.querySelectorAll\('\.otp-input'\)/.test(code) ? tag : '';
  });
}

test.before(async () => {
  browser = await playwright[engine].launch({ headless: true, ...(engine === 'chromium' && process.env.TQ_BROWSER_CHANNEL ? { channel: process.env.TQ_BROWSER_CHANNEL } : {}) });
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname === '/fixture.svg') {
      res.setHeader('Content-Type', 'image/svg+xml');
      return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#475569"/></svg>');
    }
    if (url.pathname.startsWith('/fixture/')) {
      res.setHeader('Content-Type', 'text/html');
      return res.end(fixture(url.pathname.slice('/fixture/'.length), url.searchParams.get('state') || '', url.searchParams.get('role') || ''));
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.statusCode = 404; return res.end(); }
    res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
});
test.after(async () => {
  await browser?.close();
  if (server) { server.closeAllConnections(); await new Promise(resolve => server.close(resolve)); }
});

async function digitsFit(inputs, label) {
  const measurements = await inputs.evaluateAll(elements => {
    const ctx = document.createElement('canvas').getContext('2d');
    return elements.map(input => {
      const style = getComputedStyle(input);
      ctx.font = `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
      const available = input.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
      const text = Math.max(...['00', '12', '59'].map(value => ctx.measureText(value).width + (parseFloat(style.letterSpacing) || 0) * value.length));
      return { id: input.id, available, text, height: input.getBoundingClientRect().height };
    });
  });
  assert.equal(measurements.length, 2, label);
  assert.ok(measurements.every(m => m.available >= m.text + 2 && m.height >= 43.9), `${label}: clipped digits ${JSON.stringify(measurements)}`);
}

async function controlsFit(picker, label) {
  const result = await picker.evaluate(el => {
    const outer = el.getBoundingClientRect();
    return {
      insideScreen: outer.left >= 0 && outer.right <= innerWidth + 1,
      clipped: [...el.querySelectorAll('input.tp-input, .tp-btn, .tp-period-btn, .tp-set-btn')].filter(control => {
        const r = control.getBoundingClientRect();
        return r.left < outer.left || r.right > outer.right + 1 || r.top < outer.top || r.bottom > outer.bottom + 1;
      }).map(control => control.id || control.className),
    };
  });
  assert.equal(result.insideScreen, true, label);
  assert.deepEqual(result.clipped, [], label);
}

test('12-hour picker keeps both digits and Set usable in create and edit forms on narrow screens', { timeout: 90000 }, async () => {
  for (const name of ['staff-rule-create', 'staff-rule-edit']) {
    for (const width of [320, 360, 375, 414, 768, 1280]) {
      const page = await browser.newPage({ viewport: { width, height: 900 } });
      const errors = []; page.on('pageerror', error => errors.push(error.message));
      await page.goto(`${origin}/fixture/${name}?state=custom-theme`);
      await page.evaluate(() => document.fonts.ready);
      if (width === 375) await page.evaluate(() => { document.documentElement.style.fontSize = '20px'; });
      for (const [field, hour, minute, period] of [['time_from', '12', '00', 'AM'], ['time_to', '11', '59', 'PM']]) {
        await page.locator(`[data-clock-field="${field}"] [data-clock-toggle]`).click();
        const picker = page.locator(`#${field}Picker`);
        await picker.waitFor({ state: 'visible' });
        await digitsFit(picker.locator('.tp-input'), `${name} ${width} ${field}`);
        await controlsFit(picker, `${name} ${width} ${field}`);
        await page.locator(`#${field}_tp_hour`).fill(hour);
        await page.locator(`#${field}_tp_min`).fill(minute);
        await picker.locator(`[data-period="${period}"]`).click();
        await picker.locator('[data-clock-set]').click();
        assert.equal(await page.locator(`#${field}`).inputValue(), `${hour}:${minute} ${period}`);
        assert.equal(await picker.isVisible(), false);
      }
      const data = await page.locator('#departureRuleForm').evaluate(form => {
        const values = new FormData(form); return { from: values.get('time_from'), to: values.get('time_to') };
      });
      assert.deepEqual(data, { from: '12:00 AM', to: '11:59 PM' });
      assert.deepEqual(errors, [], `${name} ${width}`);
      await page.close();
    }
  }
});

test('moving an edited departure rule offers the destination next round and submits it without an overlap', { timeout: 90000 }, async () => {
  for (const [name, role] of [['staff-rule-edit', ''], ['admin-rule-edit', ''], ['admin-rule-edit', 'super_admin']]) {
    for (const width of [375, 1280]) {
      const page = await browser.newPage({ viewport: { width, height: 900 } });
      const errors = []; page.on('pageerror', error => errors.push(error.message));
      try {
        await page.goto(`${origin}/fixture/${name}?state=moving-round&role=${role}`);
        const round = page.locator('#round_number');
        const data = () => page.locator('#departureRuleForm').evaluate(form => {
          const values = new FormData(form);
          return { route: values.get('route_id'), round: values.get('round_number') };
        });
        assert.deepEqual(await data(), { route: '2', round: '1' });
        await page.locator('#route_id').selectOption('1');
        assert.deepEqual(await round.locator('option').allTextContents(), ['New round 2']);
        assert.equal(await round.isVisible(), false);
        assert.equal(await page.locator('#round_number_display').isVisible(), true);
        assert.equal(await page.locator('#round_number_display').inputValue(), 'New round 2');
        assert.equal(await page.locator('#round_number_label').getAttribute('for'), 'round_number_display');
        assert.deepEqual(await data(), { route: '1', round: '2' });
        assert.equal(await page.locator('#rule-contradiction-alert').isVisible(), false);
        assert.deepEqual(await round.locator('option').evaluateAll(options => options.map(option => option.value)), ['2']);
        assert.equal(await page.locator('#rule-contradiction-alert').isVisible(), false);
        assert.equal(await page.locator('#departureRuleForm').evaluate(form => form.checkValidity()), true);
        await page.locator('#route_id').selectOption('2');
        assert.deepEqual(await data(), { route: '2', round: '1' });
        await page.locator('#route_id').selectOption('3');
        assert.deepEqual(await data(), { route: '3', round: '1' });
        assert.equal(await page.locator('#round_number_display').isVisible(), true);
        await page.locator('#route_id').selectOption('1');
        assert.deepEqual(await data(), { route: '1', round: '2' });
        await page.goto(`${origin}/fixture/${name}?role=${role}`);
        assert.equal(await round.locator('option').count(), 1);
        assert.equal(await round.isVisible(), false);
        assert.equal(await page.locator('#round_number_display').inputValue(), 'Round 1');
        assert.equal((await data()).round, '1');
        await page.goto(`${origin}/fixture/${name}?state=scoped-rounds&role=${role}`);
        assert.equal(await round.isVisible(), true);
        assert.equal(await page.locator('#round_number_display').isVisible(), false);
        await round.selectOption('1');
        assert.deepEqual(await data(), { route: '2', round: '1' });
        assert.deepEqual(errors, [], `${name} ${role} ${width}`);
      } finally { await page.close(); }
    }
  }
});


test('round assignments reserve a destination number only on overlapping selected weekdays', { timeout: 90000 }, async () => {
  for (const [name, role] of [['staff-rule-edit',''], ['admin-rule-edit',''], ['admin-rule-edit','super_admin']]) {
    for (const width of [375,1280]) {
      const page = await browser.newPage({viewport:{width,height:900}});
      const errors=[]; page.on('pageerror', error=>errors.push(error.message));
      try {
        await page.goto(origin + '/fixture/' + name + '?state=unique-day-rounds&role=' + role);
        await page.locator('#route_id').selectOption('1');
        const round = page.locator('#round_number');
        assert.deepEqual(await round.locator('option').evaluateAll(options=>options.map(option=>option.value)), ['2']);
        await page.locator('#ruleEveryDay').uncheck();
        await page.locator('#ruleDays input[type="checkbox"][value="2"]').check();
        const data = () => page.locator('#departureRuleForm').evaluate(form=>{
          const values=new FormData(form); return {round:values.get('round_number'),days:values.getAll('days_of_week[]')};
        });
        assert.deepEqual(await data(), {round:'1',days:['2']});
        await page.locator('#ruleDays input[type="checkbox"][value="1"]').check();
        assert.deepEqual(await data(), {round:'2',days:['1','2']});
        assert.deepEqual(await round.locator('option').evaluateAll(options=>options.map(option=>option.value)), ['2']);
        assert.equal(await page.locator('#departureRuleForm').evaluate(form=>form.checkValidity()), true);
        await page.locator('#ruleEveryDay').check();
        await page.locator('#route_id').selectOption('2');
        assert.deepEqual(await data(), {round:'1',days:['1','2','3','4','5','6','7']});
        assert.deepEqual(errors, []);
      } finally { await page.close(); }
    }
  }
});

test('24-hour picker and interval fields remain readable for Admin and SuperAdmin', { timeout: 120000 }, async () => {
  for (const [name, role] of [['admin-rule-create', ''], ['admin-rule-edit', 'super_admin']]) {
    for (const width of [320, 375, 1280]) {
      const page = await browser.newPage({ viewport: { width, height: 900 } });
      const errors = []; page.on('pageerror', error => errors.push(error.message));
      await page.goto(`${origin}/fixture/${name}?state=custom-theme&role=${role}`);
      await page.evaluate(() => document.fonts.ready);
      for (const [field, hour, minute] of [['time_from', '00', '00'], ['time_to', '23', '59'], ['wait_duration', '00', '25']]) {
        await page.locator(`[data-time-target="${field}"]`).click();
        const picker = page.locator('.military-time-popover.open');
        await picker.waitFor({ state: 'visible' });
        await digitsFit(picker.locator('.tp-input'), `${name} ${width} ${field}`);
        await controlsFit(picker, `${name} ${width} ${field}`);
        await page.locator(`#${field}_popover_hh`).fill(hour);
        await page.locator(`#${field}_popover_mm`).fill(minute);
        if (engine === 'chromium' && field === 'time_from' && [375,1280].includes(width)) {
          const dir=path.join(__dirname,'artifacts'); fs.mkdirSync(dir,{recursive:true});
          await picker.screenshot({path:path.join(dir,`rule-timepicker-${name}-${width}.png`)});
          await page.screenshot({path:path.join(dir,`rule-timepicker-page-${name}-${width}.png`),fullPage:false});
        }
        await picker.locator('.tp-set-btn').click();
        assert.equal(await page.locator(`#${field}`).inputValue(), `${hour}:${minute}`);
        const clock = page.locator(`#${field}`);
        await clock.fill('23:::');
        assert.equal(await clock.inputValue(), '23:');
        await clock.blur();
        assert.equal(await clock.inputValue(), '23:00');
        await clock.fill('18:88');
        assert.equal(await clock.inputValue(), '18:59');
        await clock.fill('99:99');
        assert.equal(await clock.inputValue(), '23:59');
        if (field === 'wait_duration') {
          await clock.fill('30'); await clock.blur();
          assert.equal(await clock.inputValue(), '00:30');
        }
        await clock.fill(`${hour}:${minute}`);
      }
      assert.deepEqual(errors, [], `${name} ${width}`);
      await page.close();
    }
  }
});

async function themeTokens(page) {
  return page.locator('body').evaluate(body => {
    const probe = document.createElement('span'); body.appendChild(probe);
    const values = {};
    for (const token of ['primary', 'primary-dark', 'primary-soft', 'on-primary']) {
      probe.style.color = `var(--${token})`; values[token] = getComputedStyle(probe).color;
    }
    probe.remove(); return values;
  });
}

test('help accents and queue totals follow the role theme while Boarding remains green', {timeout:60000}, async () => {
  for (const name of ['staff-guide','admin-guide','staff-manual','admin-manual','staff-queue','staff-password']) {
    const context = await browser.newContext({viewport:{width:375,height:900}});
    try {
      const page = await context.newPage(), errors = [];
      page.on('pageerror', error => errors.push(error.message));
      await page.goto(`${origin}/fixture/${name}?state=${name === 'staff-queue' ? 'many' : 'custom-theme'}`);
      await page.addScriptTag({url:origin + '/js/ws-client.js'});
      if (name === 'staff-password') {
        assert.equal(await page.locator('#btnSubmitPassword').evaluate(el=>getComputedStyle(el).backgroundColor), 'rgb(226, 232, 240)');
        await page.locator('#current_password').fill('FixturePrevious123');
        await page.locator('#verification_code').fill('123456');
        await page.locator('#new_password').fill('FixtureUpdated123');
        await page.locator('#confirm_password').fill('FixtureUpdated123');
        assert.equal(await page.locator('#btnSubmitPassword').evaluate(el=>el.classList.contains('is-valid-form')), true);
      }
      for (const primary of ['#7040b0','#fbbf24']) {
        const staff = name.startsWith('staff');
        await page.evaluate(({primary,staff}) => applyLiveBranding({category:'theme', [staff ? 'theme_staff_primary' : 'theme_admin_primary']:primary}), {primary,staff});
        await page.evaluate(() => Promise.all(document.getAnimations().filter(animation => animation.effect?.getTiming().iterations !== Infinity).map(animation => animation.finished.catch(() => {}))));
        const colors = await themeTokens(page);
        if (name.endsWith('guide')) {
          const actual = await page.locator('.help-header-icon').evaluate(icon => ({background:getComputedStyle(icon).backgroundColor,color:getComputedStyle(icon).color}));
          assert.deepEqual(actual, {background:colors['primary-soft'],color:colors['primary-dark']});
          assert.equal(await page.locator('.accordion-header-icon').first().evaluate(el=>getComputedStyle(el).color), colors.primary);
        } else if (name.endsWith('manual')) {
          const step = await page.locator('.step-number').first().evaluate(el => ({background:getComputedStyle(el).backgroundColor,color:getComputedStyle(el).color}));
          assert.deepEqual(step,{background:colors['primary-soft'],color:colors['primary-dark']});
          assert.equal(await page.locator('.manual-pill-btn.active').evaluate(el=>getComputedStyle(el).backgroundColor), colors.primary);
          assert.equal(await page.locator('.manual-pill-btn.active').evaluate(el=>getComputedStyle(el).color), colors['on-primary']);
          assert.equal(await page.locator('.manual-header .manual-title').evaluate(el=>getComputedStyle(el).color), colors['on-primary']);
          assert.equal(await page.locator('.manual-header .manual-subtitle').evaluate(el=>getComputedStyle(el).color), colors['on-primary']);
          assert.ok((await page.locator('.manual-header .btn-back').evaluateAll(elements=>elements.map(el=>getComputedStyle(el).color))).every(color=>color === colors['on-primary']));
          assert.equal(await page.locator('.manual-header .btn-print-outline').evaluate(el=>getComputedStyle(el).color), colors['on-primary']);
        } else if (name === 'staff-queue') {
          const count = await page.locator('[data-queue-order-count]').first().evaluate(el => ({background:getComputedStyle(el).backgroundColor,color:getComputedStyle(el).color}));
          assert.deepEqual(count,{background:colors['primary-soft'],color:colors['primary-dark']});
          assert.equal(await page.locator('.queue-order-boarding-badge').first().evaluate(el=>getComputedStyle(el).backgroundColor), 'rgb(25, 135, 84)');
        } else {
          assert.equal(await page.locator('#btnSubmitPassword').evaluate(el=>getComputedStyle(el).backgroundColor), colors.primary);
          assert.equal(await page.locator('#btnSubmitPassword').evaluate(el=>getComputedStyle(el).color), colors['on-primary']);
          assert.equal(await page.locator('#systemWarningOkBtn').evaluate(el=>getComputedStyle(el).color), colors['on-primary']);
        }
      }
      assert.deepEqual(errors, []);
    } finally {await context.close();}
  }
});

test('logout accents and shared notices follow saved and live role themes, including light colors', { timeout: 90000 }, async () => {
  for (const [name, role] of [['staff-rule-edit', ''], ['admin-rules', ''], ['admin-rules', 'super_admin']]) {
    for (const width of [320, 1280]) {
      const page = await browser.newPage({ viewport: { width, height: 900 } });
      const errors = []; page.on('pageerror', error => errors.push(error.message));
      await page.goto(`${origin}/fixture/${name}?state=custom-theme&role=${role}`);
      for (const live of [false, true]) {
        if (live) {
          await page.addScriptTag({ url: origin + '/js/ws-client.js' });
          await page.evaluate(() => applyLiveBranding({ category: 'theme', theme_staff_primary: '#fbbf24', theme_staff_nav_bg: '#164e63', theme_staff_nav_text: '#ffffff', theme_admin_primary: '#fbbf24', theme_admin_nav_bg: '#563084', theme_admin_nav_text: '#ffffff' }));
        }
        const colors = await themeTokens(page);
        await page.evaluate(() => new Promise(resolve => {
          document.getElementById('logoutModal').addEventListener('shown.bs.modal', () => resolve(), { once: true });
          confirmLogout();
        }));
        await page.locator('#logoutModal.show').waitFor();
        await page.mouse.move(0, 0);
        await page.locator('#logoutModal').focus();
        await page.waitForFunction(primary => getComputedStyle(document.querySelector('.logout-btn-confirm')).backgroundColor === primary, colors.primary);
        const actual = await page.locator('#logoutModal').evaluate(modal => {
          const styles = selector => getComputedStyle(modal.querySelector(selector));
          return { stripe: styles('.logout-stripe').backgroundColor, icon: styles('.logout-icon-wrapper').color,
            iconBackground: styles('.logout-icon-wrapper').backgroundColor, tag: styles('.logout-role-tag').color,
            tagBackground: styles('.logout-role-tag').backgroundColor, button: styles('.logout-btn-confirm').backgroundColor,
            text: styles('.logout-btn-confirm').color, childText: styles('.logout-btn-confirm i').color };
        });
        assert.deepEqual(actual, { stripe: colors.primary, icon: colors['primary-dark'], iconBackground: colors['primary-soft'],
          tag: colors['primary-dark'], tagBackground: colors['primary-soft'], button: colors.primary,
          text: colors['on-primary'], childText: colors['on-primary'] }, `${name} ${role} ${width} ${live ? 'live' : 'saved'}`);
        await page.locator('.logout-btn-confirm').hover();
        await page.waitForFunction(dark => getComputedStyle(document.querySelector('.logout-btn-confirm')).backgroundColor === dark, colors['primary-dark']);
        assert.equal(await page.locator('.logout-btn-confirm').evaluate(el => getComputedStyle(el).backgroundColor), colors['primary-dark']);
        await page.locator('.logout-btn-cancel').click();
        await page.locator('#logoutModal').waitFor({ state: 'hidden' });
        assert.equal(new URL(page.url()).pathname, '/fixture/' + name);
        await page.evaluate(() => new Promise(resolve => {
          document.getElementById('systemAlertModal').addEventListener('shown.bs.modal', () => resolve(), { once: true });
          showSystemAlert({ message: 'Fixture notice' });
        }));
        await page.locator('#systemAlertModal.show').waitFor();
        await page.mouse.move(0, 0);
        await page.waitForFunction(primary => getComputedStyle(document.querySelector('#systemAlertOkBtn')).backgroundColor === primary, colors.primary);
        assert.equal(await page.locator('#systemAlertOkBtn').evaluate(el => getComputedStyle(el).backgroundColor), colors.primary);
        assert.equal(await page.locator('#systemAlertOkBtn').evaluate(el => getComputedStyle(el).color), colors['on-primary']);
        await page.locator('#systemAlertOkBtn').click();
        await page.locator('#systemAlertModal').waitFor({ state: 'hidden' });
      }
      assert.deepEqual(errors, [], `${name} ${role} ${width}`);
      await page.close();
    }
  }
});


test('departure rule Select deletes only checked rows and resets after route changes', {timeout: 90000}, async () => {
  for (const name of ['admin-rules', 'staff-rules']) for (const width of [320, 375, 1280]) {
    const page = await browser.newPage({viewport:{width,height:900}});
    try {
      await page.goto(`${origin}/fixture/${name}?state=bulk-rules`);
      assert.equal(await page.locator('.modern-card-header #btn-toggle-select-rules').count(), 1);
      assert.equal(await page.locator('.page-header-modern #btn-toggle-select-rules').count(), 0);
      assert.equal(await page.locator('#rule-bulk-toolbar').isVisible(), false);
      assert.equal(await page.locator('.rule-select-cell').first().isVisible(), false);
      await page.locator('#btn-toggle-select-rules').click();
      assert.equal(await page.locator('#rule-bulk-toolbar').isVisible(), true);
      assert.equal(await page.locator('#btn-select-rules-text').textContent(), 'Exit Select');
      assert.equal(await page.locator('#btn-toggle-select-rules').getAttribute('aria-expanded'), 'true');
      assert.equal(await page.locator('#rule-selected-count').textContent(), '0 rules selected');
      assert.equal(await page.locator('#btn-bulk-delete-rules').isDisabled(), true);
      const toolbarFits = await page.locator('#rule-bulk-toolbar').evaluate(el => {
        const bar = el.getBoundingClientRect();
        return [...el.querySelectorAll('label, .bulk-count-badge, button')].every(node => {
          const box = node.getBoundingClientRect();
          return box.left >= bar.left && box.right <= bar.right + 1 && box.right <= innerWidth + 1;
        });
      });
      assert.equal(toolbarFits, true, `${name} toolbar must fit at ${width}px`);
      if (width !== 320) {
        const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, {recursive:true});
        await page.locator('#rule-bulk-toolbar').scrollIntoViewIfNeeded();
        await page.screenshot({path:path.join(dir, `${engine}-${name}-selection-${width}.png`), animations:'disabled'});
      }
      const boxes = page.locator('.rule-select-checkbox:visible:not(:disabled)');
      await boxes.first().check();
      assert.equal(await page.locator('#rule-selected-count').textContent(), '1 rule selected');
      if (await boxes.count() > 1) assert.equal(await page.locator('#select-all-rules').evaluate(el => el.indeterminate), true);
      await page.locator('#select-all-rules').check();
      assert.equal(await page.locator('.rule-select-checkbox:checked').count(), await boxes.count());
      if (await boxes.count() > 1) await boxes.first().uncheck();
      const selected = await page.locator('.rule-select-checkbox:checked').count();
      assert.ok(selected > 0);
      // Bootstrap ignores hide() while its opening transition is still running.
      // Listen before clicking so reduced-motion browsers cannot finish first.
      await page.evaluate(() => {
        window.ruleDeleteModalShown = new Promise(resolve => {
          document.getElementById('deleteRuleConfirmModal').addEventListener('shown.bs.modal', () => resolve(), {once:true});
        });
      });
      await page.locator('#btn-bulk-delete-rules').click();
      await page.evaluate(() => window.ruleDeleteModalShown);
      assert.match(await page.locator('#deleteRuleConfirmMessage').textContent(), /selected departure rules/);
      assert.equal(await page.locator('#ruleBulkDeleteForm input[name="ids[]"]').count(), selected);
      assert.deepEqual(await page.locator('#ruleBulkDeleteForm input[name="ids[]"]').evaluateAll(nodes => nodes.map(node => node.value)),
        await page.locator('.rule-select-checkbox:checked').evaluateAll(nodes => nodes.map(node => node.value)));
      await page.locator('#deleteRuleConfirmModal [data-bs-dismiss="modal"]').click();
      await page.locator('#deleteRuleConfirmModal').waitFor({state:'hidden'});
      await page.locator('#btn-cancel-select-rules').click();
      assert.equal(await page.locator('#rule-bulk-toolbar').isVisible(), false);
      assert.equal(await page.locator('#btn-select-rules-text').textContent(), 'Select');
      assert.equal(await page.locator('.rule-select-checkbox:checked').count(), 0);
      assert.equal(await page.locator('#ruleBulkDeleteForm input[name="ids[]"]').count(), 0);
      await page.locator('#btn-toggle-select-rules').click();
      await page.locator('#select-all-rules').check();
      await page.locator('[data-route-filter="general"]').click();
      assert.equal(await page.locator('.rule-select-checkbox:checked').count(), 0);
      assert.equal(await page.locator('#rule-bulk-toolbar').isVisible(), false);
      assert.equal(await page.locator('#ruleBulkDeleteForm .rule-return-route').inputValue(), 'general');
    } finally { await page.close(); }
  }
});

test('activity logs use readable labels and omit action controls from reports', {timeout:90000}, async () => {
  const page = await browser.newPage();
  try {
    await page.goto(`${origin}/fixture/admin-logs?state=friendly-logs`);
    assert.match(await page.locator('#logsTable').textContent(), /Signed in/);
    assert.match(await page.locator('#logsTable').textContent(), /Dispatcher signed in/);
    assert.equal(await page.locator('#logReportForm [name="action_type"]').count(), 0);
    await page.goto(`${origin}/fixture/admin-logs-report`);
    assert.deepEqual(await page.locator('table thead th').allTextContents(), ['Date & Time','User','Details']);
    assert.match(await page.locator('table tbody').textContent(), /Dispatcher signed in/);
  } finally {await page.close();}
});

test('text fill, outline labels and date chips follow live primary colors', {timeout:90000}, async () => {
  const page = await browser.newPage();
  try {
    await page.goto(`${origin}/fixture/admin-logs?state=custom-theme`);
    await page.addScriptTag({url:origin + '/js/ws-client.js'});
    for (const primary of ['#fbbf24', '#7040b0']) {
      await page.evaluate(primary => applyLiveBranding({category:'theme',theme_admin_primary:primary}), primary);
      const tokens = await themeTokens(page);
      const chip = page.locator('.quick-chip.active');
      await page.waitForFunction(color => getComputedStyle(document.querySelector('.quick-chip.active')).backgroundColor === color, tokens.primary);
      assert.equal(await chip.evaluate(el=>getComputedStyle(el).color), tokens['on-primary']);
      const probe = await page.evaluate(() => {
        const el = document.createElement('span'); el.className='text-primary'; document.body.appendChild(el);
        const result={color:getComputedStyle(el).color,fill:getComputedStyle(el).webkitTextFillColor}; el.remove(); return result;
      });
      assert.deepEqual(probe,{color:tokens.primary,fill:tokens.primary});
    }
    await page.evaluate(()=>applyLiveBranding({category:'theme',theme_admin_nav_text:'#25c17a'}));
    const subtitle=page.locator('#site-header .logo-text p').first();
    assert.deepEqual(await subtitle.evaluate(el=>({color:getComputedStyle(el).color,fill:getComputedStyle(el).webkitTextFillColor})),{color:'rgb(37, 193, 122)',fill:'rgb(37, 193, 122)'});
    assert.equal(await page.locator('.profile-trigger-caret').first().evaluate(el=>getComputedStyle(el).color),'rgba(37, 193, 122, 0.9)');
  } finally {await page.close();}
});


test('commuter departure filters react on touch release and keep swipes and keyboard input usable', {timeout:90000}, async () => {
  const page=await browser.newPage({viewport:{width:375,height:850}});
  try {
    await page.goto(`${origin}/fixture/guest?state=guest-route-rules`);
    await page.locator('#routeAverageCard').click();
    await page.locator('#routeAverageModal.is-open').waitFor();
    const result=await page.evaluate(() => {
      const ormoc=document.querySelector('.rules-route-chip[data-route="ormoc"]');
      const events=(type,x)=>new PointerEvent(type,{bubbles:true,pointerType:'touch',pointerId:1,isPrimary:true,clientX:x,clientY:20});
      ormoc.dispatchEvent(events('pointerdown',20)); ormoc.dispatchEvent(events('pointerup',20));
      // Assert synchronously, before any click or timer can run.
      return {selected:ormoc.getAttribute('aria-pressed'), visible:[...document.querySelectorAll('#routeAverageList .route-average-item')].filter(el=>el.style.display!=='none').map(el=>el.dataset.ruleDest),transition:getComputedStyle(ormoc).transitionDuration,touchAction:getComputedStyle(ormoc).touchAction};
    });
    assert.equal(result.selected,'true'); assert.deepEqual(result.visible,['ormoc']);
    assert.equal(result.transition,'0s'); assert.equal(result.touchAction,'manipulation');
    await page.evaluate(() => {
      const bato=document.querySelector('.rules-route-chip[data-route="bato"]');
      for(const [type,x] of [['pointerdown',20],['pointermove',60],['pointerup',60]]) bato.dispatchEvent(new PointerEvent(type,{bubbles:true,pointerType:'touch',pointerId:2,isPrimary:true,clientX:x,clientY:20}));
    });
    assert.equal(await page.locator('.rules-route-chip.active').getAttribute('data-route'),'ormoc');
    await page.locator('.rules-route-chip[data-route="bato"]').focus(); await page.keyboard.press('Enter');
    assert.equal(await page.locator('.rules-route-chip.active').getAttribute('data-route'),'bato');
    assert.deepEqual(await page.locator('#routeAverageList .route-average-item:visible').evaluateAll(items=>items.map(el=>el.dataset.ruleDest)),['bato']);
  } finally {await page.close();}
});


test('commuter departure filters keep the dialog and route controls steady with long lists', {timeout:90000}, async () => {
  for (const viewport of [{width:320,height:568},{width:375,height:667},{width:667,height:375},{width:768,height:900},{width:1280,height:900}]) {
    const page=await browser.newPage({viewport});
    try {
      await page.goto(`${origin}/fixture/guest?state=guest-route-rules-many`);
      await page.locator('#routeAverageCard').click();
      const geometry=()=>page.evaluate(()=>{
        const box=document.querySelector('.route-average-dialog').getBoundingClientRect();
        const close=document.getElementById('routeAverageClose').getBoundingClientRect();
        const filter=document.getElementById('rulesFilterBar').getBoundingClientRect();
        return {top:box.top,height:box.height,closeTop:close.top,filterTop:filter.top};
      });
      assert.equal(await page.locator('body').evaluate(el=>{
        const style=getComputedStyle(el,'::before');
        return style.animationName==='none' || style.animationPlayState==='paused';
      }),true,'covered backgrounds must be static or paused');
      const initial=await geometry();
      assert.ok(initial.top>=0 && initial.top+initial.height<=viewport.height+1);
      await page.locator('.route-average-body').evaluate(el=>{el.scrollTop=el.scrollHeight;});
      assert.ok(await page.locator('.route-average-body').evaluate(el=>el.scrollTop>0));
      for (const route of ['ormoc','bato','general','all','ormoc']) {
        await page.locator(`.rules-route-chip[data-route="${route}"]`).click();
        const current=await geometry();
        for (const key of Object.keys(initial)) assert.ok(Math.abs(current[key]-initial[key])<1, `${viewport.width} ${route}: ${key} moved`);
        assert.equal(await page.locator('.route-average-body').evaluate(el=>el.scrollTop),0);
        const visible=await page.locator('#routeAverageList .route-average-item:visible').evaluateAll(items=>items.map(el=>el.dataset.ruleDest));
        assert.ok(visible.length>0);
        if(route!=='all') assert.ok(visible.every(destination=>destination===route));
      }
      assert.equal(await page.locator('#routeAverageModal').evaluate(el=>getComputedStyle(el).backdropFilter),'none');
      await page.locator('#routeAverageClose').click();
      assert.equal(await page.locator('#routeAverageModal').isVisible(),false);
      assert.equal(await page.locator('#routeAverageCard').evaluate(el=>el===document.activeElement),true);
      assert.equal(await page.locator('body').evaluate(el=>getComputedStyle(el,'::before').animationPlayState),'running');
    } finally {await page.close();}
  }
});


test('terminal names become uppercase while typing on both admin forms', {timeout:90000}, async () => {
  const page=await browser.newPage({viewport:{width:375,height:850}});
  try {
    for(const name of ['admin-terminal-create','admin-terminal-edit']) {
      await page.goto(`${origin}/fixture/${name}`);
      await page.locator('#name').fill('villaba town terminal');
      assert.equal(await page.locator('#name').inputValue(),'VILLABA TOWN TERMINAL');
      assert.equal(await page.locator('#name').evaluate(el=>new FormData(el.form).get('name')),'VILLABA TOWN TERMINAL');
    }
  } finally {await page.close();}
});

test('terminal contacts allow separate numbers and emails without losing the form payload', {timeout:30000}, async () => {
  for (const width of [375,1280]) {
    const page=await browser.newPage({viewport:{width,height:850}});
    try {
      await page.goto(`${origin}/fixture/admin-settings?state=multiple-contacts#identity`);
      assert.equal(await page.locator('#terminalPhonesList input').count(),2);
      assert.equal(await page.locator('#terminalEmailsList input').count(),2);
      await page.locator('[data-contact-add="terminalPhonesList"]').click();
      await page.locator('#terminalPhonesList input').last().fill('09181234567');
      await page.locator('[data-contact-add="terminalEmailsList"]').click();
      await page.locator('#terminalEmailsList input').last().fill('extra@example.com');
      await page.locator('#terminalEmailsList [data-contact-remove]').first().click();
      const data=await page.locator('#formIdentity').evaluate(form=>{const values=new FormData(form);return {phones:values.getAll('contact_phones[]'),emails:values.getAll('contact_emails[]')};});
      assert.deepEqual(data,{phones:['+639171234567','(053) 555-8376','09181234567'],emails:['dispatch@example.com','extra@example.com']});
      assert.equal(await page.locator('#formIdentity').evaluate(form=>form.checkValidity()),true);
    } finally {await page.close();}
  }
});

test('browser theme and authentication button shadows follow saved and live primary colors', {timeout:90000}, async () => {
  for (const name of ['login','forgot','verify','reset']) {
    const page=await browser.newPage({viewport:{width:375,height:850}});
    try {
      await page.goto(`${origin}/fixture/${name}?state=blue-theme`);
      assert.equal(await page.locator('meta[name="theme-color"]').count(),1);
      assert.equal(await page.locator('meta[name="theme-color"]').getAttribute('content'),'#2563eb');
      const manifest=new URL(await page.locator('link[data-app-manifest]').getAttribute('href'),origin);
      assert.equal(manifest.pathname,'/manifest.webmanifest');
      assert.equal(manifest.search,'');
      assert.equal(await page.locator('link[data-app-manifest]').getAttribute('crossorigin'),'use-credentials');
      const button=page.locator('form button[type="submit"]').first();
      if (name === 'verify') for (const input of await page.locator('.otp-input').all()) await input.fill('1');
      assert.equal(await button.isEnabled(),true,name);
      assert.match(await button.evaluate(el=>getComputedStyle(el).boxShadow),/37, 99, 235/,name);
      await page.addScriptTag({url:origin+'/js/ws-client.js'});
      await page.evaluate(()=>applyLiveBranding({category:'theme',theme_guest_primary:'#7040b0'}));
      assert.equal(await page.locator('meta[name="theme-color"]').getAttribute('content'),'#7040b0');
      assert.equal(await page.locator('link[data-app-manifest]').getAttribute('href'),manifest.pathname);
      await page.waitForFunction(()=>getComputedStyle(document.querySelector('form button[type="submit"]')).boxShadow.includes('112, 64, 176'));
      await button.hover();
      await page.waitForFunction(()=>getComputedStyle(document.querySelector('form button[type="submit"]')).boxShadow.includes('112, 64, 176'));
    } finally {await page.close();}
  }
});

test('browser theme follows the current guest, dispatcher, admin and super admin palette', {timeout:90000}, async () => {
  for (const [name,role,primary] of [['guest','guest','#2563eb'],['staff-password','staff','#0d9488'],['admin-rule-create','admin','#7040b0'],['admin-rule-create','super_admin','#7040b0']]) {
    const page=await browser.newPage({viewport:{width:375,height:850}});
    try {
      await page.goto(`${origin}/fixture/${name}?state=blue-theme&role=${role}`);
      assert.equal(await page.locator('meta[name="theme-color"]').count(),1,role);
      assert.equal(await page.locator('meta[name="theme-color"]').getAttribute('content'),primary,role);
      const manifest=new URL(await page.locator('link[data-app-manifest]').getAttribute('href'),origin);
      assert.equal(manifest.pathname,'/manifest.webmanifest');
      assert.equal(manifest.search,'');
      assert.equal(await page.locator('link[data-app-manifest]').getAttribute('crossorigin'),'use-credentials');
      await page.addScriptTag({url:origin+'/js/ws-client.js'});
      await page.evaluate(role=>applyLiveBranding({category:'theme',['theme_'+(role==='super_admin'?'admin':role)+'_primary']:'#c0550d'}),role);
      assert.equal(await page.locator('meta[name="theme-color"]').getAttribute('content'),'#c0550d',role);
      assert.equal(await page.locator('link[data-app-manifest]').getAttribute('href'),manifest.pathname,role);
    } finally {await page.close();}
  }
});

test('header clock follows saved and live navigation text colors on mobile and desktop', {timeout:90000}, async () => {
  for (const [name,role] of [['admin-settings','admin'],['staff-password','staff']]) {
    for (const width of [375,1280]) {
      const page=await browser.newPage({viewport:{width,height:900}});
      try {
        await page.goto(`${origin}/fixture/${name}?state=custom-theme`);
        await page.addScriptTag({url:origin+'/js/ws-client.js'});
        const clock=page.locator(width<769 ? '.profile-operations-time' : '.header-left-cluster > .operations-header-time');
        assert.equal(await clock.isVisible(),true);
        for (const palette of [null,{bg:'#ffffff',text:'#1e293b'},{bg:'#1e293b',text:'#fbbf24'}]) {
          if (palette) await page.evaluate(({role,palette})=>applyLiveBranding({category:'theme',
            [`theme_${role}_nav_bg`]:palette.bg,[`theme_${role}_nav_text`]:palette.text}),{role,palette});
          const colors=await clock.evaluate(el=>{
            const probe=document.createElement('span');el.appendChild(probe);
            probe.style.color='var(--nav-text)'; const text=getComputedStyle(probe).color;
            probe.style.color='var(--nav-text-soft)'; const icon=getComputedStyle(probe).color;probe.remove();
            const time=getComputedStyle(el.querySelector('time'));
            return {text,icon,color:time.color,fill:time.webkitTextFillColor,iconColor:getComputedStyle(el.querySelector('i')).color};
          });
          assert.equal(colors.color,colors.text,`${name} ${width}: clock text`);
          assert.equal(colors.fill,colors.text,`${name} ${width}: clock text fill`);
          assert.equal(colors.iconColor,colors.icon,`${name} ${width}: clock icon`);
        }
      } finally {await page.close();}
    }
  }
});

test('daily queue clearing offers an editable Philippine time and an enable switch', {timeout:90000}, async () => {
  for (const width of [320, 375, 768, 1280]) {
  const page=await browser.newPage({viewport:{width,height:850}});
  try {
    await page.goto(`${origin}/fixture/admin-settings#operations`);
    assert.equal(await page.locator('#inputQueueResetTime').isVisible(),true);
    const layout = await page.locator('.queue-reset-layout').evaluate(el => {
      const checkbox = el.querySelector('#inputQueueResetEnabled').getBoundingClientRect();
      const copy = el.querySelector('.queue-reset-copy').getBoundingClientRect();
      return {gap:copy.left-checkbox.right, aligned:Math.abs(copy.top-checkbox.top)<=4,
        fits:[...el.querySelectorAll('.queue-reset-option, #inputQueueResetTime')].every(node=>{
          const box=node.getBoundingClientRect(); return box.left>=0&&box.right<=innerWidth+1;
        })};
    });
    assert.ok(layout.gap>=8 && layout.gap<=16 && layout.aligned, JSON.stringify({width,layout}));
    assert.equal(layout.fits,true);
    assert.equal(await page.locator('#inputQueueResetTime').inputValue(),'00:00');
    assert.equal(await page.locator('#inputQueueResetTime').getAttribute('type'),'text');
    const card = page.locator('.queue-reset-layout').locator('..');
    const before = await card.boundingBox();
    await page.locator('#queueResetTimeToggle').click();
    const picker = page.locator('#queueResetTimePicker');
    assert.equal(await picker.locator('select').count(),0);
    assert.ok(Math.abs((await card.boundingBox()).height-before.height)<1, 'Opening the picker must not enlarge the card.');
    await digitsFit(picker.locator('.tp-input'), `Queue clearing ${width}`);
    await controlsFit(picker, `Queue clearing ${width}`);
    await picker.locator('[aria-label="Decrease hours"]').click();
    assert.equal(await page.locator('#queueResetHour').inputValue(),'23');
    await picker.locator('[aria-label="Increase hours"]').click();
    assert.equal(await page.locator('#queueResetHour').inputValue(),'00');
    await page.locator('#queueResetHour').fill('18');
    await page.locator('#queueResetMinute').fill('30');
    await page.locator('#queueResetTimeApply').click();
    assert.equal(await page.locator('#inputQueueResetTime').inputValue(),'18:30');
    assert.equal(await page.locator('#queueResetTimePicker').isVisible(),false);
    await page.locator('#inputQueueResetTime').fill('24:00');
    assert.equal(await page.locator('#inputQueueResetTime').inputValue(),'23:00');
    assert.equal(await page.locator('#inputQueueResetTime').evaluate(el=>el.checkValidity()),true);
    await page.locator('#inputQueueResetTime').fill('23:::');
    assert.equal(await page.locator('#inputQueueResetTime').inputValue(),'23:');
    await page.locator('#inputQueueResetTime').blur();
    assert.equal(await page.locator('#inputQueueResetTime').inputValue(),'23:00');
    await page.locator('#inputQueueResetTime').fill('11111');
    assert.equal(await page.locator('#inputQueueResetTime').inputValue(),'11:11');
    await page.locator('#inputQueueResetTime').fill('9999');
    await page.locator('#inputQueueResetTime').blur();
    assert.equal(await page.locator('#inputQueueResetTime').inputValue(),'23:59');
    await page.locator('#inputQueueResetTime').fill('00:00');
    if (width===375 || width===1280) {
      const dir=path.join(__dirname,'artifacts'); fs.mkdirSync(dir,{recursive:true});
      await page.locator('.queue-reset-layout').scrollIntoViewIfNeeded();
      await page.screenshot({path:path.join(dir,`${engine}-queue-clearing-${width}.png`),animations:'disabled'});
    }
    await page.locator('#inputQueueResetTime').fill('18:30');
    await page.locator('#inputQueueResetEnabled').uncheck();
    assert.deepEqual(await page.locator('#formOperations').evaluate(form=>{const data=new FormData(form);return [data.get('queue_reset_time'),data.getAll('queue_reset_enabled').at(-1)];}),['18:30','0']);
    await page.locator('#inputQueueResetEnabled').check();
    assert.equal(await page.locator('#formOperations').evaluate(form=>new FormData(form).getAll('queue_reset_enabled').at(-1)),'1');
  } finally {await page.close();}
  }
});

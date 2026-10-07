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
    if (/\bsrc\s*=/.test(attrs)) return /(?:bootstrap\.bundle\.min|interaction-motion|dispatch-rule-times|departure-rule-days)\.js/.test(attrs) ? tag : '';
    return /function confirmLogout|window\.showSystemAlert|Time Picker Popover Creation|window\.TerminalMotion/.test(code) ? tag : '';
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
  assert.ok(measurements.every(m => m.available >= m.text + 2 && m.height >= 44), `${label}: clipped digits ${JSON.stringify(measurements)}`);
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
        assert.equal(await round.isVisible(), true);
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

test('24-hour picker and interval fields remain readable for Admin and SuperAdmin', { timeout: 90000 }, async () => {
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

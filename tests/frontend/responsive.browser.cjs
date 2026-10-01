'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const playwright = require('playwright');
const root = path.resolve(__dirname, '../..');
const engine = process.env.TQ_BROWSER || 'chromium';
const managementPages = ['admin-users', 'admin-vehicles', 'admin-routes', 'admin-terminals', 'admin-announcements', 'admin-rules', 'admin-history', 'admin-logs', 'admin-settings'];
const pages = ['login', 'forgot', 'reset', 'verify', 'guest', 'fares', 'schedules', 'search', 'staff-queue', 'staff-schedules', 'admin-schedules', ...managementPages];
const authPages = new Set(pages.slice(0, 4));
const cache = new Map();
const report = { engine, fixtures: 'production PHP views with seeded helper/data values', cases: [], beforeAfter: {} };
let browser, server, origin, beforeCSS;
function scriptsOf(html) { return [...html.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(m => m[1]).filter(s => s && !s.includes('maze-universal-loader')); }
function fixture(name, long, baseline) {
  const key = name + ':' + long;
  if (!cache.has(key)) {
    const rendered = execFileSync('php', [path.join(__dirname, 'render-responsive-fixture.php'), name, long ? 'long' : 'normal'], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
    let scripts;
    if (authPages.has(name) || managementPages.includes(name)) scripts = scriptsOf(rendered);
    else if (!name.startsWith('staff') && !name.startsWith('admin')) {
      // Keep actual guest navigation/announcement controls; isolate realtime reads.
      const header = fs.readFileSync(path.join(root, 'app/Views/templates/guest_header.php'), 'utf8');
      scripts = [header.slice(header.indexOf('var guestMenuCloseSequence = 0;'), header.lastIndexOf('</script>'))];
    } else {
      // Header handlers remain real; queue API actions are covered by existing tests.
      scripts = scriptsOf(rendered).filter(s => s.includes('initOperationsHeaderClock'));
    }
    let html = rendered.replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, '');
    html = html.replace(/https:\/\/cdn\.jsdelivr\.net\/npm\/bootstrap@5\.3\.0\/dist\/css\/bootstrap\.min\.css/g, '/fixture/bootstrap.css');
    html = html.replace('</head>', '<script src="/assets/js/interaction-motion.js"></script></head>');
    html = html.replace('</body>', '<script src="/fixture/bootstrap.js"></script>' + scripts.map(s => '<script>' + s + '</script>').join('') + '<script src="/assets/js/global-loader.js"></script></body>');
    cache.set(key, html);
  }
  const html = cache.get(key);
  return baseline ? html.replace(/responsive\.css[^"']*/g, 'responsive.css?fixture-baseline=1') : html;
}
test.before(async () => {
  const base = process.env.TQ_RESPONSIVE_BASE_REF || '2cfa15b98ed55fe20128eacfc66577b49cbfc0a5';
  beforeCSS = execFileSync('git', ['show', base + ':public/assets/css/responsive.css'], { cwd: root, encoding: 'utf8' });
  // Render all fixtures eagerly so missing helper/data assumptions fail visibly.
  for (const name of pages) { fixture(name, false, false); fixture(name, true, false); }
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    const target = url.pathname;
    if (target === '/fixture.svg') { res.setHeader('Content-Type', 'image/svg+xml'); res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#b71c1c"/></svg>'); return; }
    if (target === '/fixture/bootstrap.css') { res.setHeader('Content-Type', 'text/css'); res.end(fs.readFileSync(path.join(__dirname, 'node_modules/bootstrap/dist/css/bootstrap.min.css'))); return; }
    if (target === '/fixture/bootstrap.js') { res.setHeader('Content-Type', 'text/javascript'); res.end(fs.readFileSync(path.join(__dirname, 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'))); return; }
    if (target.startsWith('/assets/')) {
      const file = path.resolve(root, 'public', '.' + target);
      if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file)) { res.writeHead(404); res.end(); return; }
      res.setHeader('Content-Type', file.endsWith('.js') ? 'text/javascript' : 'text/css');
      res.end(target === '/assets/css/responsive.css' && url.searchParams.has('fixture-baseline') ? beforeCSS : fs.readFileSync(file)); return;
    }
    const name = target.slice(1);
    if (!pages.includes(name)) { res.writeHead(404); res.end(); return; }
    res.setHeader('Content-Type', 'text/html'); res.end(fixture(name, url.searchParams.has('long'), url.searchParams.has('baseline')));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = `http://127.0.0.1:${server.address().port}`;
  browser = await playwright[engine].launch({ headless: true });
  report.browserVersion = browser.version();
});
test.after(async () => {
  if (browser) await browser.close();
  if (server) { server.closeAllConnections(); await new Promise(resolve => server.close(resolve)); }
  const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
  fs.writeFileSync(path.join(dir, 'responsive-' + engine + '.json'), JSON.stringify(report, null, 2));
});
async function open(t, name, width, height, query = 'long=1', mode = 'full') {
  const context = await browser.newContext({ viewport: { width, height }, reducedMotion: mode === 'reduced' ? 'reduce' : 'no-preference' });
  t.after(() => context.close());
  await context.addInitScript(({ lite }) => {
    Object.defineProperty(Navigator.prototype, 'hardwareConcurrency', { configurable: true, get: () => lite ? 2 : 8 });
    Object.defineProperty(Navigator.prototype, 'deviceMemory', { configurable: true, get: () => lite ? 2 : 8 });
    document.addEventListener('shown.bs.modal', event => { event.target.dataset.fixtureShown = '1'; });
    document.addEventListener('hidden.bs.modal', event => { delete event.target.dataset.fixtureShown; });
  }, { lite: mode === 'lite' });
  await context.route('**/*', route => route.request().url().startsWith(origin) || route.request().url().startsWith('data:') ? route.continue() : route.abort());
  const page = await context.newPage();
  const errors = []; page.on('pageerror', e => errors.push(e.message));
  await page.goto(`${origin}/${name}?${query}`);
  assert.equal(await page.locator('link[rel="stylesheet"][href*="/interaction-motion.css"]').count(), 1, `${name} has duplicate motion stylesheets`);
  await page.evaluate(() => Promise.all(document.getAnimations().filter(a => a.effect?.getTiming().iterations !== Infinity).map(a => a.finished.catch(() => {}))));
  return { page, context, errors };
}
async function bounds(page, selectors) {
  return page.evaluate(selectors => {
    const width = document.documentElement.clientWidth;
    return selectors.flatMap(s => [...document.querySelectorAll(s)].map(el => {
      const r = el.getBoundingClientRect(), style = getComputedStyle(el);
      if (!r.width || style.display === 'none' || style.visibility === 'hidden') return null;
      return { selector: s, left: r.left, right: r.right, width: r.width, fits: r.left >= -1 && r.right <= width + 1 };
    }).filter(Boolean));
  }, selectors);
}
function checkBounds(items, label) { assert.ok(items.length > 0, label + ' has no visible targets'); assert.ok(items.every(e => e.fits), label + ': ' + JSON.stringify(items.filter(e => !e.fits))); }
async function screenshot(page, name, fullPage = true) {
  const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
  await page.screenshot({ path: path.join(dir, `${engine}-${name}.png`), fullPage, animations: 'disabled' });
}

test('management pages keep long values and primary controls inside phone, tablet and desktop layouts', { timeout: 90000 }, async t => {
  const failures = [];
  for (const name of managementPages) {
    for (const [width, height] of [[320, 568], [768, 1024], [844, 390], [1280, 800]]) {
      const { page, context, errors } = await open(t, name, width, height);
      let passed = true;
      try {
        checkBounds(await bounds(page, ['.main-content', '.page-header-modern', '.modern-card', '.settings-page', '.route-card-item']), `${name} ${width}`);
        if (width <= 768) {
          const clipped = await page.evaluate(() => [...document.querySelectorAll('td[data-label]')].flatMap(td => {
            if (!/^(Name|Location|Label|Destination|Details|Message|User|Operator|Driver|Routes?|Assigned Route|Terminal)/.test(td.dataset.label)) return [];
            const r = td.getBoundingClientRect(); if (!r.width || !r.height) return [];
            const range = document.createRange(); range.selectNodeContents(td);
            const text = range.getBoundingClientRect();
            return text.left < r.left - 1 || text.right > r.right + 1 ? [{ label: td.dataset.label, text: td.textContent.trim(), cell: { left: r.left, right: r.right }, content: { left: text.left, right: text.right } }] : [];
          }));
          assert.deepEqual(clipped, [], `${name} ${width} clipped values`);
          if (name === 'admin-routes') {
            const clippedTitles = await page.locator('.route-card-item > .modern-card-header > .d-flex:first-child > .fw-bold').evaluateAll(titles => titles.flatMap(title => {
              const header = title.closest('.modern-card-header').getBoundingClientRect();
              const range = document.createRange(); range.selectNodeContents(title);
              const text = range.getBoundingClientRect();
              return text.left < header.left - 1 || text.right > header.right + 1 ? [title.textContent.trim()] : [];
            }));
            assert.deepEqual(clippedTitles, [], `route headings ${width} clipped values`);
          }
          if (name === 'admin-vehicles') {
            const labels = await page.evaluate(() => {
              const canvas = document.createElement('canvas'), ctx = canvas.getContext('2d');
              return ['Operator Name', 'Driver Name', 'Assigned Route'].map(label => {
                const td = document.querySelector(`#vehicles-table td[data-label="${label}"]`), style = getComputedStyle(td, '::before');
                ctx.font = style.font || `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
                const spacing = parseFloat(style.letterSpacing) || 0;
                const wordWidth = Math.max(...label.toUpperCase().split(' ').map(word => ctx.measureText(word).width + spacing * word.length));
                return { label, wordWidth, available: parseFloat(style.width) };
              });
            });
            assert.ok(labels.every(label => label.wordWidth <= label.available + 1), `label words are squeezed: ${JSON.stringify(labels)}`);
          }
        }
        assert.deepEqual(errors, [], name);
      } catch (error) {
        passed = false; failures.push({ page: name, width, message: error.message, scriptErrors: errors });
        const detail = page.locator('#vehicles-table td[data-label="Operator Name"]');
        if (name === 'admin-vehicles') await detail.scrollIntoViewIfNeeded();
        await screenshot(page, `${name}-failure-${width}`, false);
      }
      report.cases.push({ page: name, width, height, passed });
      if (width === 320 && passed) {
        const target = {
          'admin-vehicles': '#vehicles-table td[data-label="Operator Name"]',
          'admin-routes': '.route-card-item .modern-card-header',
          'admin-terminals': '#terminals-table td[data-label="Name"]',
          'admin-announcements': '#announcements-table td[data-label="Message"]',
          'admin-rules': '#departure-rules-table td[data-label="Destination"]',
          'admin-history': 'td[data-label="Driver"]',
          'admin-logs': 'td[data-label="Details"]'
        }[name];
        if (target) await page.locator(target).first().scrollIntoViewIfNeeded();
        await screenshot(page, `${name}-phone`, false);
      }
      await context.close();
    }
  }
  report.managementFailures = failures;
  assert.deepEqual(failures, []);
});

test('vehicle modals preserve native fields, validation, focus and dismissal at small sizes and motion modes', { timeout: 60000 }, async t => {
  for (const [width, height, mode] of [[320, 568, 'full'], [844, 390, 'full'], [1280, 800, 'full'], [320, 568, 'lite'], [320, 568, 'reduced']]) {
    const { page, context, errors } = await open(t, 'admin-vehicles', width, height, 'long=1', mode);
    const posts = []; page.on('request', request => { if (request.method() === 'POST') posts.push(request.url()); });
    const trigger = page.locator('[data-bs-target="#registerVehicleModal"]');
    await trigger.click();
    const modal = page.locator('#registerVehicleModal');
    await page.waitForFunction(() => document.querySelector('#registerVehicleModal').dataset.fixtureShown === '1');
    assert.equal(await modal.evaluate(el => el.contains(document.activeElement)), true);
    checkBounds(await bounds(page, ['#registerVehicleModal .modal-content', '#registerVehicleModal .modal-footer', '#registerVehicleModal input:not([type="hidden"]):not([type="file"])', '#registerVehicleModal select', '#registerVehicleModal button[type="submit"]']), `registration ${width} ${mode}`);
    const contract = await page.locator('#registerVehicleForm').evaluate(form => ({ action: form.getAttribute('action'), method: form.method, enctype: form.enctype, csrf: new FormData(form).get('csrf_fixture'), required: [...form.querySelectorAll('[required]')].map(el => el.name), valid: form.checkValidity() }));
    assert.equal(contract.action, '/admin/vehicles/store'); assert.equal(contract.method, 'post'); assert.equal(contract.enctype, 'multipart/form-data'); assert.equal(contract.csrf, 'unchanged');
    for (const field of ['plate_number', 'operator_name', 'type', 'route_id', 'capacity']) assert.ok(contract.required.includes(field));
    assert.equal(contract.valid, false);
    await page.locator('#registerVehicleModal button[type="submit"]').click();
    assert.deepEqual(posts, []); assert.equal(await page.locator('#registerVehicleModal button[type="submit"]').isEnabled(), true);
    await page.locator('#plate_number').fill('abc-1234'); assert.equal(await page.locator('#plate_number').inputValue(), 'ABC-1234');
    await page.locator('#registerVehicleModal .btn-close').click();
    await page.waitForFunction(() => !document.querySelector('#registerVehicleModal').classList.contains('show') && !document.querySelector('.modal-backdrop'));
    assert.equal(await trigger.evaluate(el => el === document.activeElement), true);
    await page.locator('[data-bs-target="#manageVehicleTypesModal"]').click();
    await page.waitForFunction(() => document.querySelector('#manageVehicleTypesModal').dataset.fixtureShown === '1');
    checkBounds(await bounds(page, ['#manageVehicleTypesModal .modal-content', '#manageVehicleTypesModal .modal-footer']), `types ${width} ${mode}`);
    await page.locator('#manageVehicleTypesModal .modal-footer [data-bs-dismiss="modal"]').click();
    await page.waitForFunction(() => !document.querySelector('.modal.show') && !document.querySelector('.modal-backdrop'));
    assert.deepEqual(errors, []); assert.deepEqual(posts, []);
    report.cases.push({ page: 'vehicle modal controls', width, height, mode, passed: true }); await context.close();
  }
});

test('settings tabs and terminal bulk selection retain their real state and keyboard controls', { timeout: 60000 }, async t => {
  for (const [width, height] of [[320, 568], [844, 390], [1280, 800]]) {
    const settings = await open(t, 'admin-settings', width, height);
    for (const tab of ['identity', 'media', 'themes', 'footer', 'operations']) {
      const button = settings.page.locator(`#tabBtn-${tab}`); await button.focus(); await button.press('Enter');
      assert.equal(await settings.page.locator(`#tab-${tab}`).isVisible(), true);
      assert.equal(await settings.page.evaluate(() => location.hash), '#' + tab);
      assert.equal(await settings.page.evaluate(() => localStorage.getItem('sb_active_tab')), tab);
      checkBounds(await bounds(settings.page, [`#tab-${tab}`, `#tab-${tab} input:not([type="hidden"]):not([type="checkbox"]):not([type="file"]):not([type="radio"])`, `#tab-${tab} select`, `#tab-${tab} button[type="submit"]`]), `settings ${tab} ${width}`);
      if (width === 320 && tab === 'themes') await screenshot(settings.page, 'settings-colors-phone', false);
    }
    assert.deepEqual(settings.errors, []); await settings.context.close();
    const terminals = await open(t, 'admin-terminals', width, height);
    await terminals.page.locator('#btn-toggle-select-terminals').click();
    const checkbox = terminals.page.locator('.terminal-row-checkbox'); await checkbox.check();
    assert.equal(await terminals.page.locator('#terminal-selected-count').textContent(), '1');
    assert.equal(await terminals.page.locator('#btn-bulk-delete-terminals').isEnabled(), true);
    await terminals.page.locator('#btn-bulk-delete-terminals').click();
    await terminals.page.waitForFunction(() => !!document.querySelector('.modal.show[data-fixture-shown="1"]'));
    checkBounds(await bounds(terminals.page, ['.modal.show .modal-content', '.modal.show .modal-footer']), `bulk selection ${width}`);
    const payload = await terminals.page.locator('.modal.show form').evaluate(form => Object.fromEntries(new FormData(form)));
    assert.equal(payload['ids[]'], '1'); assert.equal(payload.csrf_fixture, 'unchanged');
    await terminals.page.locator('.modal.show [data-bs-dismiss="modal"]').first().click();
    await terminals.page.waitForFunction(() => !document.querySelector('.modal.show') && !document.querySelector('.modal-backdrop'));
    assert.equal(await checkbox.isChecked(), true);
    assert.deepEqual(terminals.errors, []); await terminals.context.close();
    report.cases.push({ page: 'settings and bulk controls', width, height, passed: true });
  }
});

test('guest navigation has no missing breakpoint interval and its real drawer opens and closes', { timeout: 60000 }, async t => {
  for (const width of [320, 390, 768, 900, 901, 1200, 1201, 1280, 1281, 1440, 2560]) {
    const { page, context, errors } = await open(t, 'guest', width, 800);
    const toggle = page.locator('.guest-header .mobile-toggle');
    if (width <= 900) {
      assert.equal(await toggle.isVisible(), true, `toggle at ${width}`);
      await toggle.click();
      await page.locator('#navMenu').waitFor({ state: 'visible', timeout: 2000 });
      try {
        for (const href of ['/guest', '/schedules', '/fares', '/login']) {
          await page.locator(`#navMenu > a[href="${href}"]`).waitFor({ state: 'visible', timeout: 2000 });
        }
      } catch (error) {
        report.drawerFailure = await page.evaluate(() => {
          const elements = [...document.querySelectorAll('#navMenu, #navMenu > a, .mobile-toggle')];
          return elements.map(el => { const s = getComputedStyle(el), r = el.getBoundingClientRect(); return { tag: el.tagName, class: el.className, href: el.getAttribute('href'), display: s.display, visibility: s.visibility, transform: s.transform, width: r.width, height: r.height, x: r.x, y: r.y }; });
        });
        console.log('Drawer failure diagnostics:', JSON.stringify(report.drawerFailure));
        await screenshot(page, `drawer-failure-${width}`);
        throw error;
      }
      await page.locator('.guest-nav-close-btn').click();
      await page.waitForFunction(() => !document.querySelector('.mobile-toggle').classList.contains('is-closing'));
      assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
    } else {
      assert.equal(await toggle.isVisible(), false, `desktop toggle at ${width}`);
      for (const href of ['/guest', '/schedules', '/fares', '/login']) assert.equal(await page.locator(`#navMenu > a[href="${href}"]`).isVisible(), true, `${href} at ${width}`);
    }
    checkBounds(await bounds(page, ['.guest-header', '.guest-header .logo', '#headerClock']), `header ${width}`);
    report.cases.push({ page: 'guest navigation', width, height: 800, passed: true });
    if (width === 1280) await screenshot(page, 'guest-tablet');
    assert.deepEqual(errors, []); await context.close();
  }
});

test('authentication fields fit small and landscape screens while retaining their real controls', { timeout: 60000 }, async t => {
  for (const name of ['login', 'forgot', 'reset', 'verify']) {
    for (const [width, height] of [[320, 568], [390, 844], [768, 1024], [844, 390], [1440, 900]]) {
      const { page, context, errors } = await open(t, name, width, height);
      const entrance = await page.locator(name === 'login' ? '.login-card .card-head' : '.auth > .card').evaluate(el => {
        const style = getComputedStyle(el);
        return Math.max(...style.animationDuration.split(',').map((duration, i) => parseFloat(duration) + parseFloat(style.animationDelay.split(',')[i] || style.animationDelay.split(',')[0]))) * 1000;
      });
      assert.ok(entrance <= 300, `${name} entrance ${entrance}ms`);
      checkBounds(await bounds(page, ['.brand', '.brand-name', '.brand-title', '.brand-sub', '.login-card', '.auth > .card', 'form input:not([type="hidden"]):not([type="checkbox"])', 'form button[type="submit"]']), `${name} ${width}x${height}`);
      const controls = await page.locator('form input:not([type="hidden"]):not([type="checkbox"])').all();
      assert.ok(controls.length);
      for (const input of controls) { await input.focus(); assert.equal(await input.evaluate(el => document.activeElement === el), true); }
      for (const toggle of await page.locator('.field.password .toggle').all()) {
        const padding = await toggle.evaluate(el => {
          const input = el.parentElement.querySelector('input'), r = input.getBoundingClientRect(), b = el.getBoundingClientRect();
          return { actual: parseFloat(getComputedStyle(input).paddingRight), needed: r.right - b.left };
        });
        assert.ok(padding.actual >= padding.needed, JSON.stringify(padding));
        await toggle.click(); assert.equal(await toggle.getAttribute('aria-pressed'), 'true');
      }
      if (name === 'verify') {
        const otp = page.locator('.otp-input');
        assert.equal(await otp.count(), 6);
        for (let i = 0; i < 6; i++) await otp.nth(i).fill(String(i + 1));
        assert.equal(await page.locator('input[name="reset_code"]').inputValue(), '123456');
        assert.equal(await page.locator('#btnVerify').isEnabled(), true);
      }
      if (name === 'login') {
        await page.locator('#username').fill('fixture-user'); await page.locator('#password').fill('fixture-password');
        const payload = await page.evaluate(() => Object.fromEntries(new FormData(document.querySelector('#loginForm'))));
        assert.equal(payload.username, 'fixture-user'); assert.equal(payload.password, 'fixture-password'); assert.equal(payload.csrf_fixture, 'unchanged');
      }
      report.cases.push({ page: name, width, height, passed: true });
      if (name === 'login' && width === 320) await screenshot(page, 'login-phone');
      assert.deepEqual(errors, []); await context.close();
    }
  }
});

test('guest content and operational table values fit without hiding long data', { timeout: 60000 }, async t => {
  for (const name of ['guest', 'fares', 'schedules', 'search', 'staff-schedules', 'admin-schedules', 'staff-queue']) {
    for (const [width, height] of [[320, 568], [768, 1024], [844, 390], [1280, 800]]) {
      const { page, context, errors } = await open(t, name, width, height);
      checkBounds(await bounds(page, ['body > .container', '.main-content', '.queue-card', '.schedule-card', '.fare-card', '.q-card']), `${name} ${width}`);
      if (width <= 768 && name.endsWith('schedules') && name !== 'schedules') {
        const result = await page.locator('td[data-label]').evaluateAll(cells => cells.map(td => {
          const label = getComputedStyle(td, '::before');
          const ctx = document.createElement('canvas').getContext('2d');
          ctx.font = `${label.fontWeight} ${label.fontSize} ${label.fontFamily}`;
          const text = label.textTransform === 'uppercase' ? td.dataset.label.toUpperCase() : td.dataset.label;
          const textWidth = ctx.measureText(text).width + (parseFloat(label.letterSpacing) || 0) * Math.max(0, text.length - 1);
          const available = parseFloat(label.width);
          return {
            label: td.dataset.label,
            labelClipped: label.whiteSpace === 'nowrap' && Number.isFinite(available) && textWidth > available + 1,
            clipped: td.scrollWidth > td.clientWidth + 1 || [...td.children].some(el => {
              if (el.clientWidth > 0 && el.scrollWidth > el.clientWidth + 1) return true;
              if (!el.matches('.schedule-route-display')) return false;
              const outer = el.getBoundingClientRect();
              return [...el.children].some(child => { const r = child.getBoundingClientRect(); return r.left < outer.left - 1 || r.right > outer.right + 1; });
            }),
          };
        }));
        assert.ok(result.length); assert.ok(result.every(r => !r.clipped && !r.labelClipped), JSON.stringify(result));
      }
      const hiddenClear = page.locator('.guest-clear-search-btn');
      if (await hiddenClear.count()) for (const btn of await hiddenClear.all()) assert.equal(await btn.isVisible(), false);
      report.cases.push({ page: name, width, height, passed: true });
      if (name === 'staff-schedules' && width === 320) {
        await page.locator('td[data-label="Route"]').scrollIntoViewIfNeeded();
        await screenshot(page, 'staff-phone', false);
      }
      assert.deepEqual(errors, []); await context.close();
    }
  }
});

test('low-hardware and reduced-motion policies work in each browser engine', async t => {
  for (const mode of ['lite', 'reduced']) {
    const { page, context, errors } = await open(t, 'login', 390, 844, '', mode);
    assert.equal(await page.evaluate(() => TerminalMotion.getMode()), mode);
    await page.locator('#username').fill('fixture-user'); await page.locator('#password').fill('fixture-password');
    const pending = await page.evaluate(() => {
      const btn = document.querySelector('#loginForm button[type="submit"]'); btn.focus();
      GlobalLoader.showButtonSpinner(btn, 'Signing in…', true);
      return { focus: document.activeElement === btn, nativeEnabled: !btn.disabled, spinnerAnimation: getComputedStyle(btn.querySelector('.gl-btn-spinner')).animationName };
    });
    assert.deepEqual(pending, { focus: true, nativeEnabled: true, spinnerAnimation: 'none' });
    assert.deepEqual(errors, []); await context.close();
  }
});

test('before-after fixtures reproduce breakpoint and input-padding defects', async t => {
  for (const baseline of [true, false]) {
    const { page, context } = await open(t, 'guest', 1280, 800, baseline ? 'baseline=1' : '');
    report.beforeAfter[baseline ? 'beforeNavLinks' : 'afterNavLinks'] = await page.locator('#navMenu > a').evaluateAll(links => links.filter(a => getComputedStyle(a).display !== 'none').length);
    await context.close();
    const login = await open(t, 'login', 390, 844, baseline ? 'baseline=1' : '');
    report.beforeAfter[baseline ? 'beforePasswordPadding' : 'afterPasswordPadding'] = await login.page.locator('#password').evaluate(el => parseFloat(getComputedStyle(el).paddingRight));
    await login.context.close();
    const verify = await open(t, 'verify', 320, 568, baseline ? 'baseline=1' : '');
    report.beforeAfter[baseline ? 'beforeOtpPadding' : 'afterOtpPadding'] = await verify.page.locator('.otp-input').first().evaluate(el => parseFloat(getComputedStyle(el).paddingLeft) + parseFloat(getComputedStyle(el).paddingRight));
    await verify.context.close();
    const schedule = await open(t, 'staff-schedules', 320, 568, baseline ? 'long=1&baseline=1' : 'long=1');
    const route = schedule.page.locator('.schedule-route-display');
    await route.scrollIntoViewIfNeeded();
    report.beforeAfter[baseline ? 'beforeRouteClipped' : 'afterRouteClipped'] = await route.evaluate(el => {
      const outer = el.getBoundingClientRect();
      return el.scrollWidth > el.clientWidth + 1 || [...el.children].some(child => { const r = child.getBoundingClientRect(); return r.left < outer.left - 1 || r.right > outer.right + 1; });
    });
    await schedule.context.close();
  }
  console.log('Responsive before-after:', JSON.stringify(report.beforeAfter));
  assert.equal(report.beforeAfter.beforeNavLinks, 1); assert.equal(report.beforeAfter.afterNavLinks, 4);
  assert.ok(report.beforeAfter.beforePasswordPadding < 48); assert.equal(report.beforeAfter.afterPasswordPadding, 56);
  assert.ok(report.beforeAfter.beforeOtpPadding > 0); assert.equal(report.beforeAfter.afterOtpPadding, 0);
  assert.equal(report.beforeAfter.beforeRouteClipped, true); assert.equal(report.beforeAfter.afterRouteClipped, false);
});

'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const engine = process.env.TQ_BROWSER || 'chromium';
const browserType = require('playwright')[engine];
const { root, assetContentType, phpBinary } = require('./harness.cjs');
const nativeOnly = engine === 'chromium';
const routes = new Map([
  ['/guest', 'guest'], ['/fares', 'fares'], ['/schedules', 'schedules'],
  ['/login', 'login'], ['/forgot-password', 'forgot'],
  ['/staff/dashboard', 'staff-dashboard'], ['/staff/queue', 'staff-queue'],
  ['/admin/dashboard', 'admin-users'], ['/admin/routes', 'admin-routes'],
  ['/admin/settings', 'admin-settings'],
  ['/admin/vehicles', 'admin-vehicles'],
]);
const fixtures = new Map();
const slideshowFixtures = new Map();
const pending = [];
const navigationReports = [];
let browser, server, origin;
let streamSettings = false, settingsPrefixArrived, settingsResponse, settingsTail;

test.before(async () => {
  for (const [url, name] of routes) {
    for (const slideshow of [false, true]) {
    const html = execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme', 'custom-theme', slideshow ? 'slideshow' : '', 'super_admin'], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
    // Render the real page heads/navigation; isolate animation from live queue polling.
    // Keep the production policy in its original parser-blocking head position.
    const isolated = html.replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, script => {
      if (/window.TerminalMotion|initOperationsHeaderClock|syncHeaderHeight|data-settings-initial-tab/.test(script)) return script;
      const guestNavigation = script.indexOf('var guestMenuCloseSequence = 0;');
      return guestNavigation < 0 ? '' : '<script>' + script.slice(guestNavigation, script.lastIndexOf('</script>')) + '</script>';
    });
    const loader = '<script src="/assets/js/global-loader.js"></script>';
    (slideshow ? slideshowFixtures : fixtures).set(url, isolated.includes('</body>') ? isolated.replace('</body>', loader + '</body>') : isolated + loader + '</body></html>');
    }
  }
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname === '/slow') { pending.push(res); return; }
    if (url.pathname === '/navigation-report') {
      let body = ''; req.on('data', chunk => body += chunk); req.on('end', () => {
        navigationReports.push(JSON.parse(body)); res.statusCode = 204; res.end();
      }); return;
    }
    if (routes.has(url.pathname)) {
      res.setHeader('Content-Type', 'text/html');
      const html = (url.searchParams.has('slideshow') ? slideshowFixtures : fixtures).get(url.pathname);
      if (streamSettings && url.pathname === '/admin/settings') {
        const split = html.indexOf('<div class="settings-page">');
        settingsResponse = res; settingsTail = html.slice(split);
        res.write(html.slice(0, split)); settingsPrefixArrived?.(); return;
      }
      return res.end(html);
    }
    if (/^\/fixture(?:-second|-third)?\.svg$/.test(url.pathname)) {
      res.setHeader('Content-Type', 'image/svg+xml');
      return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#b71c1c"/></svg>');
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.statusCode = 404; return res.end(); }
    res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
  browser = await browserType.launch({ headless: true, ...(nativeOnly && process.env.TQ_BROWSER_CHANNEL ? { channel: process.env.TQ_BROWSER_CHANNEL } : {}), ...(process.env.TQ_BROWSER_EXECUTABLE ? { executablePath:process.env.TQ_BROWSER_EXECUTABLE } : {}) });
});
test.after(async () => {
  if (settingsResponse && !settingsResponse.writableEnded) settingsResponse.end(settingsTail);
  for (const response of pending) if (!response.writableEnded) response.end(fixtures.get('/schedules'));
  await browser?.close(); server?.closeAllConnections();
  if (server) await new Promise(resolve => server.close(resolve));
});

async function open(t, mode = 'full', captureFrames = false, viewport = { width: 1365, height: 900 }) {
  const context = await browser.newContext({ viewport, reducedMotion: mode === 'reduced' ? 'reduce' : 'no-preference' });
  t.after(() => context.close());
  await context.route('**/*', route => route.request().url().startsWith(origin) || route.request().url().startsWith('data:') ? route.continue() : route.abort());
  await context.addInitScript(({ mode, captureFrames }) => {
    Object.defineProperty(Navigator.prototype, 'hardwareConcurrency', { get: () => mode === 'lite' ? 2 : 8 });
    Object.defineProperty(Navigator.prototype, 'deviceMemory', { get: () => mode === 'lite' ? 2 : 8 });
    window.transitionRecords = [];
    window.layoutShifts = [];
    if (typeof PerformanceObserver !== 'undefined' && PerformanceObserver.supportedEntryTypes.includes('layout-shift')) {
      new PerformanceObserver(list => window.layoutShifts.push(...list.getEntries().map(entry => entry.value)))
        .observe({ type: 'layout-shift', buffered: true });
    }
    window.addEventListener('pagereveal', event => {
      if (!event.viewTransition) return;
      event.viewTransition.ready.then(() => {
        const record = {
          duration: getComputedStyle(document.documentElement, '::view-transition-new(root)').animationDuration,
          oldOpacity: getComputedStyle(document.documentElement, '::view-transition-old(root)').opacity,
          oldAnimation: getComputedStyle(document.documentElement, '::view-transition-old(root)').animationName,
          entry: document.querySelector('.main-content, .auth .card, .login-card .card-head, .guest-theme > .container') && getComputedStyle(document.querySelector('.main-content, .auth .card, .login-card .card-head, .guest-theme > .container')).animationName,
        };
        window.transitionRecords.push(record);
        if (captureFrames) {
          window.heldTransitions = document.getAnimations().filter(animation => animation.effect?.target === document.documentElement);
          window.heldTransitions.forEach(animation => { animation.pause(); animation.currentTime = 0; });
        }
      }, error => window.transitionRecords.push({ skipped: true, reason: error.message }));
    });
  }, { mode, captureFrames });
  const page = await context.newPage();
  const errors = []; page.on('pageerror', e => errors.push(e.message));
  return { page, context, errors };
}

async function navigate(page, to) {
  const direct = page.locator(`#site-header a[href="${to}"]:visible, .sticky-top-wrapper a[href="${to}"]:visible`);
  if (!await direct.count()) {
    const drawer = page.locator('#siteNavHamburgerBtn:visible, .mobile-toggle:visible');
    await drawer.click();
  }
  await page.locator(`#site-header a[href="${to}"]:visible, .sticky-top-wrapper a[href="${to}"]:visible, #siteSidebarDrawer a[href="${to}"]:visible`).first().click();
  await page.waitForURL(origin + to);
}

test('opening System Themes from the profile waits for the complete selected panel before revealing it', { skip: !nativeOnly, timeout:30000 }, async t => {
  const { page, context, errors } = await open(t);
  await context.addInitScript(() => localStorage.setItem('sb_active_tab', 'themes'));
  await page.goto(origin + '/admin/dashboard');
  await page.locator('#userProfileBtn').click();
  await page.locator('#userProfileMenu').waitFor({state:'visible'});
  const prefix = new Promise(resolve => { settingsPrefixArrived = resolve; });
  streamSettings = true;
  try {
    const click = page.locator('#userProfileMenu a[href="/admin/settings"]').click({noWaitAfter:true});
    await prefix;
    await page.waitForURL(origin + '/admin/settings', {waitUntil:'commit'});
    await page.waitForFunction(() => Array.isArray(window.transitionRecords), null, {polling:20});
    // Hold the HTML body after the header, reproducing an incremental response.
    await page.waitForTimeout(200);
    assert.equal(await page.locator('.settings-page').count(), 0);
    assert.equal(await page.evaluate(() => window.transitionRecords.length), 0, 'do not snapshot a header-only destination');
    settingsResponse.end(settingsTail);
    await click; await page.waitForLoadState('load');
    await page.waitForFunction(() => window.transitionRecords.some(record => record.duration));
    const record = await page.evaluate(() => window.transitionRecords.find(record => record.duration));
    assert.equal(record.entry, 'none', 'the full page already exists when its snapshot is captured');
    assert.deepEqual(await page.locator('.settings-page .tab-content').evaluateAll(panels => panels.filter(panel => getComputedStyle(panel).display !== 'none').map(panel => panel.id)), ['tab-themes']);
    assert.equal(await page.locator('.main-content').evaluate(el => getComputedStyle(el).animationName), 'none');
    assert.deepEqual(errors, []);
  } finally {
    streamSettings = false; settingsPrefixArrived = null;
    if (settingsResponse && !settingsResponse.writableEnded) settingsResponse.end(settingsTail);
  }
});

test('the original fonts stay consistent on a cold load, refresh and another page', { skip: !nativeOnly, timeout: 30000 }, async t => {
  const { page, context, errors } = await open(t);
  // A font arriving after the optional-font deadline must still be used.
  await context.route('**/assets/vendor/fonts/*.woff2', async route => {
    await new Promise(resolve => setTimeout(resolve, 450));
    await route.continue();
  });
  const cdp = await context.newCDPSession(page);
  await cdp.send('DOM.enable'); await cdp.send('CSS.enable');
  async function renderedFonts(selector) {
    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
    const { root } = await cdp.send('DOM.getDocument');
    const { nodeId } = await cdp.send('DOM.querySelector', { nodeId: root.nodeId, selector });
    assert.ok(nodeId, 'missing text: ' + selector);
    return (await cdp.send('CSS.getPlatformFontsForNode', { nodeId })).fonts.filter(font => font.glyphCount > 0);
  }
  async function checkVehicleFonts() {
    await page.evaluate(() => document.fonts.ready);
    const body = await renderedFonts('.table-modern tbody td[data-label="Operator Name"] .driver-cell');
    const title = await renderedFonts('.page-title-modern');
    assert.ok(body.some(font => font.isCustomFont && /Outfit/.test(font.familyName)), JSON.stringify(body));
    assert.ok(title.some(font => font.isCustomFont && /Bricolage/.test(font.familyName)), JSON.stringify(title));
  }
  await page.goto(origin + '/admin/vehicles'); await checkVehicleFonts();
  await page.reload(); await checkVehicleFonts();
  await page.goto(origin + '/admin/routes'); await page.evaluate(() => document.fonts.ready);
  assert.ok((await renderedFonts('.page-title-modern')).some(font => font.isCustomFont && /Bricolage/.test(font.familyName)));
  await page.goto(origin + '/login'); await page.evaluate(() => document.fonts.ready);
  assert.ok((await renderedFonts('#loginForm label')).some(font => font.isCustomFont && /Inter/.test(font.familyName)));
  assert.deepEqual(errors, []);
});

test('settings refresh restores its selected panel before paint and tab clicks stay responsive', { timeout:20000 }, async t => {
  const { page, context, errors } = await open(t);
  await context.addInitScript(() => {
    localStorage.setItem('sb_active_tab', 'media');
    window.firstSettingsPanel = null;
    function readPanel() {
      const panels = [...document.querySelectorAll('.settings-page .tab-content')];
      if (!panels.length) return requestAnimationFrame(readPanel);
      window.firstSettingsPanel = panels.filter(panel => getComputedStyle(panel).display !== 'none').map(panel => panel.id);
    }
    requestAnimationFrame(readPanel);
  });
  await page.goto(origin + '/admin/settings');
  await page.waitForFunction(() => window.firstSettingsPanel !== null);
  assert.deepEqual(await page.evaluate(() => window.firstSettingsPanel), ['tab-media']);
  await page.locator('#tabBtn-themes').click();
  assert.equal(await page.locator('#tab-themes').evaluate(el => getComputedStyle(el).animationName), 'tq-content-fade');
  await page.reload();
  await page.waitForFunction(() => window.firstSettingsPanel !== null);
  assert.deepEqual(await page.evaluate(() => window.firstSettingsPanel), ['tab-themes']);
  assert.equal(await page.locator('#tab-themes').evaluate(el => getComputedStyle(el).animationName), 'none');
  assert.deepEqual(errors, []);
});

test('refresh keeps content fully painted and preserves the current background photo', { timeout:20000 }, async t => {
  const { page, context, errors } = await open(t);
  // Test the carried slideshow phase independently of test-machine load time.
  await context.addInitScript(now => { Date.now = () => now; }, Date.now());
  await page.goto(origin + '/staff/dashboard?slideshow=1');
  // Start in the middle of the second photo, without sleeping for a slideshow cycle.
  await page.evaluate(() => sessionStorage.setItem('tq-background-epoch', String(Date.now() - 7500)));
  await page.reload();
  await page.evaluate(() => document.getAnimations().filter(animation => animation.animationName === 'terminalBgSlideshow').forEach(animation => { animation.pause(); animation.currentTime = 0; }));
  assert.equal(await page.locator('html').getAttribute('data-tq-navigation-reveal'), '');
  assert.equal(await page.locator('.main-content').evaluate(el => getComputedStyle(el).animationName), 'none');
  assert.match(await page.evaluate(() => getComputedStyle(document.body, '::before').backgroundImage), /fixture-second\.svg/);
  await page.locator('a[href="/staff/queue"]:visible').first().click();
  await page.waitForURL(origin + '/staff/queue');
  // Normal single-image pages remain static, even after a slideshow page.
  assert.equal(await page.evaluate(() => getComputedStyle(document.body, '::before').animationName), 'none');
  await page.goto(origin + '/staff/dashboard?slideshow=1');
  await page.evaluate(() => document.getAnimations().filter(animation => animation.animationName === 'terminalBgSlideshow').forEach(animation => { animation.pause(); animation.currentTime = 0; }));
  assert.match(await page.evaluate(() => getComputedStyle(document.body, '::before').backgroundImage), /fixture-second\.svg/);
  assert.deepEqual(errors, []);
});

test('profile, management menu and drawer animate both directions without moving the page', { timeout:20000 }, async t => {
  for (const mode of ['full', 'lite', 'reduced']) {
    const { page, context, errors } = await open(t, mode);
    await page.goto(origin + '/admin/dashboard');
    const menu = page.locator('#userProfileMenu');
    await page.locator('#userProfileBtn').click();
    await page.waitForFunction(() => getComputedStyle(document.querySelector('#userProfileMenu')).opacity === '1');
    assert.equal(await menu.evaluate(el => getComputedStyle(el).animationName), 'none', 'use reversible transitions');
    const duration = await menu.evaluate(el => getComputedStyle(el).transitionDuration);
    assert.equal(duration, mode === 'reduced' ? '0s' : mode === 'lite' ? '0.1s, 0.1s, 0s' : '0.16s, 0.16s, 0s');
    await page.locator('#userProfileBtn').click();
    await page.waitForFunction(() => getComputedStyle(document.querySelector('#userProfileMenu')).visibility === 'hidden');
    assert.equal(await menu.locator('a').first().isVisible(), false);
    const trigger = page.locator('[aria-controls="managementDropdownMenu"]');
    await trigger.click();
    assert.equal(await trigger.getAttribute('aria-expanded'), 'true');
    assert.equal(await page.locator('#managementDropdownMenu').evaluate(el => getComputedStyle(el).transitionDuration), duration);
    await page.mouse.move(5, 300);
    await page.keyboard.press('Escape');
    await page.waitForFunction(() => getComputedStyle(document.querySelector('#managementDropdownMenu')).visibility === 'hidden');
    await page.setViewportSize({ width:1024, height:768 });
    const before = await page.locator('#site-header').boundingBox();
    await page.locator('#siteNavHamburgerBtn').click();
    await page.waitForFunction(() => getComputedStyle(document.querySelector('#siteSidebarDrawer')).transform === 'matrix(1, 0, 0, 1, 0, 0)');
    assert.deepEqual(await page.locator('#site-header').boundingBox(), before, 'scroll lock does not move the header');
    await page.locator('#drawerCloseBtn').click();
    await page.waitForFunction(() => getComputedStyle(document.querySelector('#siteSidebarDrawer')).visibility === 'hidden');
    assert.equal(await page.locator('#siteSidebarDrawer').getAttribute('inert'), '');
    assert.deepEqual(errors, []);
    await context.close();
  }
});

test('phone, tablet, landscape and laptop page changes stay stable with usable menus', { timeout: 120000 }, async t => {
  for (const [width, height, mode, throttle] of [[320,568,'lite',4], [390,844,'full',0], [414,896,'reduced',0], [768,1024,'full',0], [844,390,'lite',0], [1365,768,'full',6]]) {
    t.diagnostic(`${engine}: ${width}×${height}, ${mode}${nativeOnly && throttle ? ', CPU/connection limited' : ''}`);
    const { page, context, errors } = await open(t, mode, false, { width, height });
    if (nativeOnly && throttle) {
      const cdp = await context.newCDPSession(page);
      await cdp.send('Emulation.setCPUThrottlingRate', { rate: throttle });
      await cdp.send('Network.enable');
      await cdp.send('Network.emulateNetworkConditions', { offline:false, latency:100, downloadThroughput:1024*1024, uploadThroughput:512*1024 });
    }
    for (const [from, to] of [['/guest','/fares'], ['/fares','/schedules'], ['/staff/dashboard','/staff/queue']]) {
      await page.goto(origin + from);
      await navigate(page, to);
      assert.equal(await page.locator('html').getAttribute('data-tq-navigation-reveal'), '');
      assert.equal(await page.locator('.main-content, .guest-theme > .container').first().evaluate(el => getComputedStyle(el).animationName), 'none');
      assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), `${width} ${to}: horizontal jump`);
      const shifts = await page.evaluate(() => window.layoutShifts.reduce((sum, value) => sum + value, 0));
      assert.ok(shifts < .01, `${width} ${to}: layout shifted by ${shifts}`);
      if (width === 390 && to === '/staff/queue') {
        const directory = path.join(__dirname, 'artifacts'); fs.mkdirSync(directory, { recursive:true });
        await page.screenshot({ path:path.join(directory, `${engine}-navigation-phone.png`) });
      }
      assert.deepEqual(errors, []);
    }
    await context.close();
  }
});

test('public, authentication and management navigation uses one short native page fade', { timeout: 30000, skip: !nativeOnly }, async t => {
  const { page, errors } = await open(t);
  for (const [from, to] of [['/guest', '/fares'], ['/fares', '/schedules'], ['/guest', '/login'], ['/login', '/forgot-password'], ['/staff/dashboard', '/staff/queue'], ['/admin/dashboard', '/admin/routes']]) {
    t.diagnostic(`${from} → ${to}`);
    await page.goto(origin + from);
    if (to === '/admin/routes') await page.locator('[aria-controls="managementDropdownMenu"]').hover();
    await page.locator(`a[href="${to}"]:visible`).first().click();
    await page.waitForURL(origin + to);
    await page.waitForFunction(() => window.transitionRecords.length > 0, null, { timeout: 3000 });
    const record = await page.evaluate(() => window.transitionRecords.find(record => record.duration));
    assert.ok(record, JSON.stringify(await page.evaluate(() => window.transitionRecords)));
    assert.equal(record.duration, '0.15s', `${from} → ${to}`);
    assert.equal(record.oldOpacity, '1', `${from} → ${to}: never expose a blank canvas`);
    assert.equal(record.oldAnimation, 'none', `${from} → ${to}: keep the outgoing frame solid`);
    // pagereveal can precede body parsing on faster runners. Inspect the actual
    // destination content once it exists, independently of snapshot readiness.
    assert.equal(await page.locator('.main-content, .auth .card, .login-card .card-head, .guest-theme > .container').first().evaluate(el => getComputedStyle(el).animationName), 'none', `${from} → ${to}: avoid stacked entry animations`);
    assert.equal(await page.locator('html').getAttribute('data-tq-navigation-reveal'), '');
    // Content does not launch another entrance after the snapshot disappears.
    await page.waitForFunction(() => !document.documentElement.getAnimations({ subtree: true }).some(animation => animation.animationName === 'tq-page-in'));
    assert.equal(await page.locator('.main-content, .auth .card, .login-card .card-head, .guest-theme > .container').first().evaluate(el => getComputedStyle(el).animationName), 'none');
  }
  assert.deepEqual(errors, []);
});

test('reduced motion and constrained devices keep the old frame until the destination is ready', { timeout: 15000, skip: !nativeOnly }, async t => {
  for (const mode of ['reduced', 'lite']) {
    const { page, errors } = await open(t, mode);
    await page.goto(origin + '/guest');
    await page.locator('a[href="/login"]:visible').first().click();
    await page.waitForURL(origin + '/login');
    assert.equal(await page.locator('html').getAttribute('data-tq-motion'), mode);
    assert.equal(await page.locator('html').getAttribute('data-tq-navigation-reveal'), '');
    await page.waitForFunction(() => window.transitionRecords.length > 0);
    const record = await page.evaluate(() => window.transitionRecords[0]);
    assert.equal(record.duration, mode === 'lite' ? '0.1s' : '0s');
    assert.equal(record.oldOpacity, '1');
    assert.equal(await page.locator('.login-card .card-head').evaluate(el => getComputedStyle(el).animationName), 'none');
    await page.locator('#username').fill('dispatcher@example.com');
    assert.equal(await page.locator('#username').inputValue(), 'dispatcher@example.com');
    assert.ok(await page.evaluate(() => window.transitionRecords.every(record => !record.skipped)));
    assert.deepEqual(errors, []);
  }
});

test('the dispatcher header never brightens or disappears between rendered transition frames', { timeout: 15000, skip: !nativeOnly }, async t => {
  const { page, errors } = await open(t, 'full', true);
  await page.goto(origin + '/staff/dashboard');
  await page.locator('a[href="/staff/queue"]:visible').first().click();
  await page.waitForURL(origin + '/staff/queue');
  await page.waitForFunction(() => window.heldTransitions?.length > 0);
  let previousOpacity = 0;
  for (const time of [0, 37.5, 75, 112.5, 150]) {
    const opacity = await page.evaluate(time => {
      window.heldTransitions.forEach(animation => animation.currentTime = time);
      return {
        old: getComputedStyle(document.documentElement, '::view-transition-old(root)').opacity,
        incoming: Number(getComputedStyle(document.documentElement, '::view-transition-new(root)').opacity),
      };
    }, time);
    assert.equal(opacity.old, '1', `old frame at ${time}ms`);
    assert.ok(opacity.incoming >= previousOpacity && opacity.incoming <= 1);
    previousOpacity = opacity.incoming;
    const png = await page.screenshot({ animations: 'allow' });
    // Sample an unchanged part of the actual production header. A transparent
    // outgoing frame would blend this blue area toward the white page canvas.
    const pixel = await page.evaluate(async png => {
      const image = new Image(); image.src = 'data:image/png;base64,' + png; await image.decode();
      const canvas = document.createElement('canvas'); canvas.width = image.width; canvas.height = image.height;
      const context = canvas.getContext('2d'); context.drawImage(image, 0, 0);
      return Array.from(context.getImageData(700, 20, 1, 1).data);
    }, png.toString('base64'));
    // Linux and Windows compositors can round a blended channel by one level.
    // A fade into a white canvas differs by dozens of levels and still fails.
    assert.equal(pixel[3], 255, `opaque header at ${time}ms`);
    assert.ok(pixel.slice(0, 3).every((channel, index) => Math.abs(channel - [22, 78, 99][index]) <= 1), `header pixels at ${time}ms: ${pixel}`);
  }
  await page.evaluate(() => window.heldTransitions.forEach(animation => animation.finish()));
  assert.deepEqual(errors, []);
});

test('browser history preserves form values and clears pending feedback after a page fade', { timeout: 15000, skip: !nativeOnly }, async t => {
  const { page, errors } = await open(t);
  await page.goto(origin + '/login');
  await page.locator('#username').fill('saved@example.com');
  await page.locator('a[href="/forgot-password"]').click();
  await page.waitForURL(origin + '/forgot-password');
  await page.goBack();
  assert.equal(await page.locator('#username').inputValue(), 'saved@example.com');
  assert.equal(await page.evaluate(() => GlobalLoader.isRunning()), false);
  assert.equal(await page.locator('#loginForm button[type="submit"]').getAttribute('aria-busy'), null);
  await page.locator('#username').fill('editable@example.com');
  assert.equal(await page.locator('#username').inputValue(), 'editable@example.com');
  assert.deepEqual(errors, []);
});

test('a slow destination leaves the current page readable and starts its request immediately', { timeout: 15000, skip: !nativeOnly }, async t => {
  const { page, errors } = await open(t);
  t.after(() => { for (const response of pending) if (!response.writableEnded) response.end(fixtures.get('/schedules')); });
  await page.goto(origin + '/guest');
  await page.evaluate(() => {
    const link = document.querySelector('a[href="/schedules"]'); link.setAttribute('href', '/slow?route=ormoc');
    link.addEventListener('click', () => setTimeout(() => navigator.sendBeacon('/navigation-report', JSON.stringify({
      path: location.pathname, progress: GlobalLoader.isVisible(), opacity: getComputedStyle(document.body).opacity,
      headerVisible: document.querySelector('#site-header, .guest-header').getBoundingClientRect().height > 0,
    })), 80), { once: true });
  });
  const count = pending.length, reportCount = navigationReports.length;
  const request = page.waitForRequest(origin + '/slow?route=ormoc');
  const box = await page.locator('a[href="/slow?route=ormoc"]:visible').boundingBox(); assert.ok(box);
  await page.mouse.click(box.x + box.width / 2, box.y + box.height / 2);
  await request;
  for (let i = 0; i < 300 && navigationReports.length === reportCount; i++) await new Promise(resolve => setTimeout(resolve, 10));
  assert.deepEqual(errors, []);
  assert.equal(pending.length, count + 1);
  assert.equal(navigationReports.length, reportCount + 1);
  assert.deepEqual(navigationReports[reportCount], { path: '/guest', progress: true, opacity: '1', headerVisible: true });
  pending[count].setHeader('Content-Type', 'text/html'); pending[count].end(fixtures.get('/schedules'));
  await page.waitForURL(origin + '/slow?route=ormoc');
  assert.deepEqual(errors, []);
});

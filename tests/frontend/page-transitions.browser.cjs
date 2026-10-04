'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const { chromium } = require('playwright');
const { root, assetContentType, phpBinary } = require('./harness.cjs');
const nativeOnly = (process.env.TQ_BROWSER || 'chromium') === 'chromium';
const routes = new Map([
  ['/guest', 'guest'], ['/fares', 'fares'], ['/schedules', 'schedules'],
  ['/login', 'login'], ['/forgot-password', 'forgot'],
  ['/staff/dashboard', 'staff-dashboard'], ['/staff/queue', 'staff-queue'],
  ['/admin/dashboard', 'admin-users'], ['/admin/routes', 'admin-routes'],
]);
const fixtures = new Map();
const pending = [];
const navigationReports = [];
let browser, server, origin;

test.before(async () => {
  if (!nativeOnly) return;
  for (const [url, name] of routes) {
    const html = execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme'], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
    // Render the real page heads/navigation; isolate animation from live queue polling.
    // Keep the production policy in its original parser-blocking head position.
    const isolated = html.replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, script => script.includes('window.TerminalMotion') ? script : '');
    const loader = '<script src="/assets/js/global-loader.js"></script>';
    fixtures.set(url, isolated.includes('</body>') ? isolated.replace('</body>', loader + '</body>') : isolated + loader + '</body></html>');
  }
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname === '/slow') { pending.push(res); return; }
    if (url.pathname === '/navigation-report') {
      let body = ''; req.on('data', chunk => body += chunk); req.on('end', () => {
        navigationReports.push(JSON.parse(body)); res.statusCode = 204; res.end();
      }); return;
    }
    if (routes.has(url.pathname)) { res.setHeader('Content-Type', 'text/html'); return res.end(fixtures.get(url.pathname)); }
    if (url.pathname === '/fixture.svg') {
      res.setHeader('Content-Type', 'image/svg+xml');
      return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#b71c1c"/></svg>');
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.statusCode = 404; return res.end(); }
    res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
  browser = await chromium.launch({ headless: true, ...(process.env.TQ_BROWSER_CHANNEL ? { channel: process.env.TQ_BROWSER_CHANNEL } : {}) });
});
test.after(async () => {
  for (const response of pending) if (!response.writableEnded) response.end(fixtures.get('/schedules'));
  await browser?.close(); server?.closeAllConnections();
  if (server) await new Promise(resolve => server.close(resolve));
});

async function open(t, mode = 'full') {
  const context = await browser.newContext({ viewport: { width: 1365, height: 900 }, reducedMotion: mode === 'reduced' ? 'reduce' : 'no-preference' });
  t.after(() => context.close());
  await context.route('**/*', route => route.request().url().startsWith(origin) || route.request().url().startsWith('data:') ? route.continue() : route.abort());
  await context.addInitScript(mode => {
    Object.defineProperty(Navigator.prototype, 'hardwareConcurrency', { get: () => mode === 'lite' ? 2 : 8 });
    Object.defineProperty(Navigator.prototype, 'deviceMemory', { get: () => mode === 'lite' ? 2 : 8 });
    window.transitionRecords = [];
    window.addEventListener('pagereveal', event => {
      if (!event.viewTransition) return;
      event.viewTransition.ready.then(() => {
        const record = {
          duration: getComputedStyle(document.documentElement, '::view-transition-new(root)').animationDuration,
          entry: document.querySelector('.main-content, .auth .card, .login-card .card-head, .guest-theme > .container') && getComputedStyle(document.querySelector('.main-content, .auth .card, .login-card .card-head, .guest-theme > .container')).animationName,
        };
        window.transitionRecords.push(record);
      }, error => window.transitionRecords.push({ skipped: true, reason: error.message }));
    });
  }, mode);
  const page = await context.newPage();
  const errors = []; page.on('pageerror', e => errors.push(e.message));
  return { page, errors };
}

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
    assert.equal(record.entry, 'none', `${from} → ${to}: avoid stacked entry animations`);
    assert.equal(await page.locator('html').getAttribute('data-tq-navigation-reveal'), '');
    // Content does not launch another entrance after the snapshot disappears.
    await page.waitForFunction(() => !document.documentElement.getAnimations({ subtree: true }).some(animation => animation.animationName === 'tq-page-in'));
    assert.equal(await page.locator('.main-content, .auth .card, .login-card .card-head, .guest-theme > .container').first().evaluate(el => getComputedStyle(el).animationName), 'none');
  }
  assert.deepEqual(errors, []);
});

test('reduced motion and constrained devices keep usable native navigation without snapshots', { timeout: 15000, skip: !nativeOnly }, async t => {
  for (const mode of ['reduced', 'lite']) {
    const { page, errors } = await open(t, mode);
    await page.goto(origin + '/guest');
    await page.locator('a[href="/login"]:visible').first().click();
    await page.waitForURL(origin + '/login');
    assert.equal(await page.locator('html').getAttribute('data-tq-motion'), mode);
    assert.equal(await page.locator('html').getAttribute('data-tq-navigation-reveal'), null);
    await page.locator('#username').fill('dispatcher@example.com');
    assert.equal(await page.locator('#username').inputValue(), 'dispatcher@example.com');
    assert.ok(await page.evaluate(() => window.transitionRecords.every(record => record.skipped)));
    assert.deepEqual(errors, []);
  }
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

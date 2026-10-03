'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const playwright = require('playwright');
const { root, assetContentType } = require('./harness.cjs');
const engine = process.env.TQ_BROWSER || 'chromium';
const pages = ['login', 'guest', 'fares', 'schedules', 'search', 'staff-queue', 'staff-schedules', 'admin-schedules', 'admin-users', 'admin-vehicles', 'admin-routes', 'admin-terminals', 'admin-announcements', 'admin-rules', 'admin-history', 'admin-logs', 'admin-settings'];
const fixtures = new Map(), report = { engine, cases: [] };
let browser, server, origin;
function fixture(name) {
  if (!fixtures.has(name)) {
    const html = execFileSync('php', [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme'], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
    fixtures.set(name, html.replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, ''));
  }
  return fixtures.get(name);
}
test.before(async () => {
  for (const name of pages) fixture(name);
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture'), name = url.pathname.slice(1);
    if (pages.includes(name)) { res.setHeader('Content-Type', 'text/html'); res.end(fixture(name)); return; }
    if (url.pathname === '/fixture.svg') { res.setHeader('Content-Type', 'image/svg+xml'); res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#b71c1c"/></svg>'); return; }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file)) { res.writeHead(404); res.end(); return; }
    // Delay vendor styles to exercise the slow connection that exposed plain buttons.
    setTimeout(() => { res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file)); }, url.pathname.startsWith('/assets/vendor/') ? 100 : 0);
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve)); origin = 'http://127.0.0.1:' + server.address().port;
  browser = await playwright[engine].launch({ headless: true }); report.browserVersion = browser.version();
});
test.after(async () => {
  if (browser) await browser.close();
  if (server) { server.closeAllConnections(); await new Promise(resolve => server.close(resolve)); }
  const directory = path.join(__dirname, 'artifacts'); fs.mkdirSync(directory, { recursive: true });
  fs.writeFileSync(path.join(directory, 'stylesheets-' + engine + '.json'), JSON.stringify(report, null, 2));
});
test('admin, dispatcher and guest styles work with third-party servers blocked', { timeout: 120000 }, async t => {
  for (const name of pages) {
    const context = await browser.newContext({ viewport: { width: 390, height: 844 } }); t.after(() => context.close());
    const page = await context.newPage(), failures = [], requestedStyles = [];
    await context.route('**/*', route => route.request().url().startsWith(origin) || route.request().url().startsWith('data:') ? route.continue() : route.abort());
    page.on('request', request => { if (request.resourceType() === 'stylesheet') requestedStyles.push(request.url()); });
    page.on('requestfailed', request => { if (['stylesheet', 'font'].includes(request.resourceType())) failures.push(request.url()); });
    page.on('response', response => { if (['stylesheet', 'font'].includes(response.request().resourceType()) && response.status() !== 200) failures.push(response.url() + ': ' + response.status()); });
    await page.goto(origin + '/' + name); await page.evaluate(() => document.fonts.ready);
    const state = await page.evaluate(async () => {
      const links = [...document.querySelectorAll('link[rel="stylesheet"]')];
      const local = links.every(link => new URL(link.href).origin === location.origin);
      const loaded = links.every(link => link.sheet && link.sheet.cssRules.length > 0);
      const paths = links.map(link => new URL(link.href).pathname);
      const fonts = [];
      if (paths.some(file => file.includes('/fontawesome/'))) fonts.push(...await document.fonts.load('900 16px "Font Awesome 6 Free"', '\uf0c9'));
      if (paths.some(file => file.includes('/bootstrap-icons/'))) fonts.push(...await document.fonts.load('16px "bootstrap-icons"', '\uf138'));
      const button = document.querySelector('.btn-modern-primary, .btn-primary, button[type="submit"], .login-btn');
      const style = button && getComputedStyle(button);
      return { local, loaded, unique: new Set(paths).size === paths.length,
        versioned: links.every(link => /^[0-9a-f]{12}$/.test(new URL(link.href).searchParams.get('v') || '')),
        fonts: fonts.length, fontsLoaded: fonts.every(font => font.status === 'loaded'),
        buttonStyled: !!style && parseFloat(style.borderRadius) > 0 && parseFloat(style.paddingRight) > 0 };
    });
    assert.deepEqual(failures, [], name); assert.ok(requestedStyles.every(url => url.startsWith(origin)), name);
    assert.ok(state.local && state.loaded && state.unique && state.versioned && state.fontsLoaded && state.buttonStyled, JSON.stringify({ name, state }));
    if (name !== 'login') assert.ok(state.fonts > 0, name + ' did not load icon fonts');
    if (['admin-users', 'staff-queue', 'guest'].includes(name)) {
      const directory = path.join(__dirname, 'artifacts'); fs.mkdirSync(directory, { recursive: true });
      await page.screenshot({ path: path.join(directory, engine + '-styles-' + name + '.png'), fullPage: true });
    }
    report.cases.push({ page: name, ...state }); await context.close();
  }
});

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
let browser, server, origin, slowFontGate, slowIconGate;
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
    const send = () => setTimeout(() => { res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file)); }, url.pathname.startsWith('/assets/vendor/') ? 100 : 0);
    if (slowFontGate && url.pathname.startsWith('/assets/vendor/fonts/') && url.pathname.endsWith('.woff2')) slowFontGate.then(send);
    else if (slowIconGate && assetContentType(file).startsWith('font/') && !url.pathname.startsWith('/assets/vendor/fonts/')) slowIconGate.then(send);
    else send();
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve)); origin = 'http://127.0.0.1:' + server.address().port;
  browser = await playwright[engine].launch({ headless: true, ...(process.env.TQ_BROWSER_CHANNEL ? {channel:process.env.TQ_BROWSER_CHANNEL} : {}) }); report.browserVersion = browser.version();
});

test('late icon fonts keep headings and button geometry stable', { timeout: 60000 }, async t => {
  for (const name of ['guest', 'search', 'admin-users', 'admin-settings', 'staff-queue']) {
    const context = await browser.newContext({ viewport: { width: 320, height: 568 } }); t.after(() => context.close());
    const page = await context.newPage();
    let releaseIcons;
    slowIconGate = new Promise(resolve => { releaseIcons = resolve; });
    await context.route('**/*', route => route.request().url().startsWith(origin) || route.request().url().startsWith('data:') ? route.continue() : route.abort());
    const measure = () => page.evaluate(() => [...document.querySelectorAll('h1, h2, button, .btn-modern-primary')].filter(el => el.getBoundingClientRect().width).map(el => {
      const rect = el.getBoundingClientRect(), style = getComputedStyle(el), round = n => Math.round(n * 100) / 100;
      return { label: el.textContent.trim(), width: round(rect.width), height: round(rect.height), color: style.color, background: style.backgroundColor };
    }));
    try {
      await page.goto(origin + '/' + name, { waitUntil: 'domcontentloaded' });
      await page.evaluate(() => Promise.all(['400 16px "Outfit"', '600 16px "Bricolage Grotesque"', '400 16px "Inter"'].map(font => document.fonts.load(font))));
      if (engine === 'chromium') await page.waitForFunction(() => performance.getEntriesByType('paint').some(entry => entry.name === 'first-contentful-paint'));
      await page.waitForTimeout(250);
      const before = await measure(); assert.ok(before.length);
      assert.equal(await page.evaluate(() => document.fonts.check('900 16px "Font Awesome 6 Free"', '\uf0c9')), false);
      releaseIcons();
      await page.evaluate(() => document.fonts.ready);
      await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
      assert.deepEqual(await measure(), before, name + ' moved when its icons arrived');
      report.cases.push({ page: name, slowIcons: true, stableControls: true });
    } finally { releaseIcons(); slowIconGate = null; await context.close(); }
  }
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
      return { local, loaded, headStyles: links.every(link => document.head.contains(link)) && !document.body.querySelector('style'), unique: new Set(paths).size === paths.length,
        versioned: links.every(link => /^[0-9a-f]{12}$/.test(new URL(link.href).searchParams.get('v') || '')),
        fonts: fonts.length, fontsLoaded: fonts.every(font => font.status === 'loaded'),
        buttonStyled: !!style && parseFloat(style.borderRadius) > 0 && parseFloat(style.paddingRight) > 0 };
    });
    assert.deepEqual(failures, [], name); assert.ok(requestedStyles.every(url => url.startsWith(origin)), name);
    assert.ok(state.local && state.loaded && state.headStyles && state.unique && state.versioned && state.fontsLoaded && state.buttonStyled, JSON.stringify({ name, state }));
    if (name !== 'login') assert.ok(state.fonts > 0, name + ' did not load icon fonts');
    if (['admin-users', 'staff-queue', 'guest'].includes(name)) {
      const directory = path.join(__dirname, 'artifacts'); fs.mkdirSync(directory, { recursive: true });
      await page.screenshot({ path: path.join(directory, engine + '-styles-' + name + '.png'), fullPage: true });
    }
    report.cases.push({ page: name, ...state }); await context.close();
  }
});

test('slow text fonts do not restyle or resize already visible controls', { timeout: 60000 }, async t => {
  for (const name of ['login', 'guest', 'search', 'admin-users', 'admin-settings', 'staff-queue']) {
    const context = await browser.newContext({ viewport: { width: 390, height: 844 } }); t.after(() => context.close());
    const page = await context.newPage(), requests = new Map();
    let releaseFonts;
    slowFontGate = new Promise(resolve => { releaseFonts = resolve; });
    await context.route('**/*', async route => {
      const request = route.request(), url = request.url();
      if (!url.startsWith(origin) && !url.startsWith('data:')) { await route.abort(); return; }
      if (request.resourceType() === 'font') requests.set(url, (requests.get(url) || 0) + 1);
      await route.continue();
    });
    const measure = () => page.evaluate(() => [...document.querySelectorAll('h1, h2, button[type="submit"], .btn-modern-primary, .profile-trigger-btn')].filter(el => el.getBoundingClientRect().width).map(el => {
      const range = document.createRange(); range.selectNodeContents(el);
      const rect = el.getBoundingClientRect(), text = range.getBoundingClientRect(), style = getComputedStyle(el);
      const round = n => Math.round(n * 100) / 100;
      return { label: el.textContent.trim(), width: round(rect.width), height: round(rect.height), textWidth: round(text.width), textHeight: round(text.height), color: style.color, background: style.backgroundColor, radius: style.borderRadius };
    }));
    try {
      await page.goto(origin + '/' + name, { waitUntil: 'domcontentloaded' });
      // Isolate text-font swapping from the separate icon-font downloads.
      await page.evaluate(async () => {
        if (document.querySelector('link[href*="/fontawesome/"]')) await document.fonts.load('900 16px "Font Awesome 6 Free"', '\uf0c9');
        if (document.querySelector('link[href*="/bootstrap-icons/"]')) await document.fonts.load('16px "bootstrap-icons"', '\uf138');
      });
      // Capture controls after an actual paint, while HTTP font responses are held.
      if (engine === 'chromium') await page.waitForFunction(() => performance.getEntriesByType('paint').some(entry => entry.name === 'first-contentful-paint'));
      await page.waitForTimeout(250);
      const before = await measure(); assert.ok(before.length, name);
      const family = name === 'login' ? 'Inter' : 'Outfit';
      assert.equal(await page.evaluate(family => document.fonts.check('400 16px "' + family + '"'), family), false, name);
      releaseFonts();
      await page.waitForFunction(() => performance.getEntriesByType('resource').some(entry => entry.name.includes('/assets/vendor/fonts/') && entry.name.includes('.woff2')));
      await page.evaluate(() => document.fonts.ready);
      await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
      const after = await measure(); assert.deepEqual(after, before, name + ' changed after late fonts arrived');
      assert.ok([...requests.values()].every(count => count === 1), name + ' downloaded a preload twice');
      report.cases.push({ page: name, slowFonts: true, stableControls: true, preloadsReused: true });
    } finally { releaseFonts(); slowFontGate = null; await context.close(); }
  }
});

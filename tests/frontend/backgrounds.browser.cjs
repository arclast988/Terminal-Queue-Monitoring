'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const os = require('node:os');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const playwright = require('playwright');
const root = path.resolve(__dirname, '../..');
const engine = process.env.TQ_BROWSER || 'chromium';
const report = { engine, cases: [], beforeAfter: {} };
let browser, server, origin, temp, oldClient, oldMotion;
const css = new Map();
test.before(async () => {
  const base = process.env.TQ_BG_BASE_REF || '64adc8c9c456bc7888f69db4bd8465bc96b76af0';
  temp = fs.mkdtempSync(path.join(os.tmpdir(), 'terminal-background-'));
  const beforeFile = path.join(temp, 'Common.php');
  fs.writeFileSync(beforeFile, execFileSync('git', ['show', base + ':app/Common.php'], { cwd: root }));
  oldClient = execFileSync('git', ['show', base + ':public/js/ws-client.js'], { cwd: root });
  oldMotion = execFileSync('git', ['show', base + ':public/assets/css/interaction-motion.css'], { cwd: root });
  for (const baseline of [false, true]) for (const count of [0, 1, 3]) {
    const args = [path.join(__dirname, 'render-background-fixture.php'), String(count)];
    if (baseline) args.push(beforeFile);
    css.set(baseline + ':' + count, execFileSync('php', args, { encoding: 'utf8' }));
  }
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname.startsWith('/fixture/bg-')) {
      const color = url.pathname.includes('second') ? '#1e40af' : url.pathname.includes('third') ? '#7e22ce' : '#15803d';
      res.setHeader('Content-Type', 'image/svg+xml');
      res.end('<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160"><rect width="160" height="160" fill="' + color + '"/><path d="M0 0L160 160M160 0L0 160" stroke="white" stroke-width="10"/></svg>');
      return;
    }
    if (url.pathname === '/js/ws-client.js') {
      res.setHeader('Content-Type', 'text/javascript');
      res.end(url.searchParams.has('baseline') ? oldClient : fs.readFileSync(path.join(root, 'public/js/ws-client.js')));
      return;
    }
    if (url.pathname.startsWith('/assets/')) {
      const file = path.resolve(root, 'public', '.' + url.pathname);
      if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file)) { res.writeHead(404); res.end(); return; }
      res.setHeader('Content-Type', file.endsWith('.js') ? 'text/javascript' : 'text/css');
      res.end(url.pathname === '/assets/css/interaction-motion.css' && url.searchParams.has('baseline') ? oldMotion : fs.readFileSync(file)); return;
    }
    if (url.pathname !== '/') { res.writeHead(404); res.end(); return; }
    const baseline = url.searchParams.has('baseline'), count = Number(url.searchParams.get('count') ?? 3);
    res.setHeader('Content-Type', 'text/html');
    res.end([
      '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1">',
      '<link rel="stylesheet" href="/assets/css/design-system.css">',
      '<style>body{min-height:100vh;background:#f8fafc}.fixture-controls{position:relative;z-index:1;margin:24px;padding:18px;background:#ffffffcc}</style>',
      '<style>' + css.get(baseline + ':' + count) + '</style>',
      '<script src="/assets/js/interaction-motion.js"></script>',
      '<link rel="stylesheet" href="/assets/css/interaction-motion.css' + (baseline ? '?baseline=1' : '') + '">',
      '</head><body class="guest-theme"><main class="fixture-controls"><h1>Background check</h1><label>Existing field <input required name="existing" value="unchanged"></label><button type="button">Existing action</button></main>',
      '<script src="/js/ws-client.js' + (baseline ? '?baseline=1' : '') + '"></script></body></html>'
    ].join('\n'));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
  browser = await playwright[engine].launch({ headless: true });
  report.browserVersion = browser.version();
});
test.after(async () => {
  if (browser) await browser.close();
  if (server) { server.closeAllConnections(); await new Promise(resolve => server.close(resolve)); }
  if (temp) fs.rmSync(temp, { recursive: true, force: true });
  const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
  fs.writeFileSync(path.join(dir, 'backgrounds-' + engine + '.json'), JSON.stringify(report, null, 2));
});
async function open(t, mode, width = 320, count = 3, baseline = false) {
  const context = await browser.newContext({ viewport: { width, height: 568 }, reducedMotion: mode === 'reduced' ? 'reduce' : 'no-preference' });
  t.after(() => context.close());
  await context.addInitScript(({ lite }) => {
    Object.defineProperty(Navigator.prototype, 'hardwareConcurrency', { configurable: true, get: () => lite ? 2 : 8 });
    Object.defineProperty(Navigator.prototype, 'deviceMemory', { configurable: true, get: () => lite ? 2 : 8 });
  }, { lite: mode === 'lite' });
  const page = await context.newPage(), errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.goto(origin + '/?count=' + count + (baseline ? '&baseline=1' : ''));
  return { page, context, errors };
}
async function state(page, pseudo = '::before') {
  return page.evaluate(pseudo => {
    const style = getComputedStyle(document.body, pseudo);
    return { image: style.backgroundImage, animation: style.animationName, opacity: Number(style.opacity), display: style.display, visibility: style.visibility, width: parseFloat(style.width), height: parseFloat(style.height) };
  }, pseudo);
}
async function photo(page, expected, pseudo = '::before') {
  const value = await state(page, pseudo);
  assert.ok(value.image.includes('/fixture/' + expected), JSON.stringify(value));
  assert.ok(value.opacity > 0 && value.display !== 'none' && value.visibility !== 'hidden' && value.width > 0 && value.height > 0);
  assert.equal(await page.evaluate(src => new Promise(resolve => {
    const image = new Image(); image.onload = () => resolve(image.naturalWidth > 0);
    image.onerror = () => resolve(false); image.src = src;
  }), origin + '/fixture/' + expected), true);
  return value;
}
async function branding(page, first = 'bg-third.svg') {
  await page.evaluate(first => window.applyLiveBranding({ category: 'background', app_bg_mode: 'slideshow', app_bg_slideshow: ['/fixture/' + first, '/fixture/bg-second.svg'] }), first);
}
async function frame(page) { await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))); }
async function screenshot(page, label) {
  const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
  await page.screenshot({ path: path.join(dir, engine + '-background-' + label + '.png'), animations: 'disabled' });
}

test('static background remains visible on phones and desktops in lite and reduced modes', { timeout: 25000 }, async t => {
  for (const mode of ['lite', 'reduced']) for (const width of [320, 1280]) {
    const { page, context, errors } = await open(t, mode, width);
    const value = await photo(page, 'bg-first.svg'); assert.equal(value.animation, 'none');
    assert.equal(await page.locator('input[name="existing"]').inputValue(), 'unchanged');
    assert.equal(await page.locator('button').isEnabled(), true); assert.deepEqual(errors, []);
    if (width === 320) await screenshot(page, mode);
    report.cases.push({ kind: 'static', mode, width, passed: true }); await context.close();
  }
});

test('full slideshow still changes photos and single/empty modes retain their fallback', { timeout: 20000 }, async t => {
  const { page, context, errors } = await open(t, 'full');
  await photo(page, 'bg-first.svg');
  await page.evaluate(() => {
    const animation = document.getAnimations().find(a => a.animationName === 'terminalBgSlideshow');
    if (!animation) throw new Error('Expected full slideshow animation');
    animation.pause(); animation.currentTime = 6000;
  });
  await frame(page); await photo(page, 'bg-second.svg'); assert.deepEqual(errors, []);
  report.cases.push({ kind: 'rotating', mode: 'full', width: 320, passed: true }); await context.close();
  for (const [mode, count, expected] of [['lite', 1, 'bg-first.svg'], ['reduced', 0, 'bg-fallback.svg']]) {
    const fixture = await open(t, mode, 320, count);
    assert.equal((await photo(fixture.page, expected)).animation, 'none'); assert.deepEqual(fixture.errors, []);
    report.cases.push({ kind: 'single/empty', mode, count, passed: true }); await fixture.context.close();
  }
});

test('live branding and changing motion preferences retain a visible background', { timeout: 25000 }, async t => {
  for (const mode of ['lite', 'reduced', 'full']) {
    const { page, context, errors } = await open(t, mode);
    await branding(page); await photo(page, 'bg-third.svg'); await photo(page, 'bg-third.svg', '::after');
    if (mode === 'full') {
      await page.emulateMedia({ reducedMotion: 'reduce' });
      await page.waitForFunction(() => document.documentElement.getAttribute('data-tq-motion') === 'reduced');
      assert.equal((await photo(page, 'bg-third.svg')).animation, 'none');
      await branding(page, 'bg-first.svg'); await photo(page, 'bg-first.svg');
      await page.emulateMedia({ reducedMotion: 'no-preference' });
      await page.waitForFunction(() => document.documentElement.getAttribute('data-tq-motion') === 'full');
      await photo(page, 'bg-first.svg'); assert.notEqual((await state(page)).animation, 'none');
    } else {
      assert.equal((await state(page)).animation, 'none');
      if (mode === 'reduced') {
        await page.evaluate(() => document.documentElement.removeAttribute('data-tq-motion'));
        assert.equal((await photo(page, 'bg-third.svg')).animation, 'none'); // CSS preference fallback without the JS attribute.
      }
    }
    assert.deepEqual(errors, []);
    report.cases.push({ kind: 'live/preferences', mode, width: 320, passed: true }); await context.close();
  }
});

test('before/after fixture reproduces the original blank background in both static modes', { timeout: 25000 }, async t => {
  for (const mode of ['lite', 'reduced']) {
    const before = await open(t, mode, 320, 3, true);
    assert.equal((await state(before.page)).image, 'none');
    if (mode === 'lite') await screenshot(before.page, 'before-lite');
    await branding(before.page);
    assert.notEqual((await state(before.page)).animation, 'none'); // Old live CSS overrode the motion policy.
    await before.context.close();
    const after = await open(t, mode);
    await photo(after.page, 'bg-first.svg'); await branding(after.page);
    await photo(after.page, 'bg-third.svg'); await photo(after.page, 'bg-third.svg', '::after'); assert.deepEqual(after.errors, []);
    report.beforeAfter[mode] = { before: 'none', oldLiveAnimation: 'overrode static mode', after: 'visible first photo', liveUpdate: 'visible new photo, animation paused' };
    await after.context.close();
  }
});

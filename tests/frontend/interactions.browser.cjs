'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const engine = process.env.TQ_BROWSER || 'chromium';
const browserType = require('playwright')[engine];
const { root, assetContentType } = require('./harness.cjs');
const posts = [];
const nativeRepeatReports = [];
const navigations = [], navigationReports = [];
let browser, server, origin;
const metrics = {};
const fixture = `<!doctype html><html><head>
<meta name="viewport" content="width=device-width,initial-scale=1">
<script>${fs.readFileSync(path.join(root, 'public/assets/js/interaction-motion.js'), 'utf8')}</script>
<link rel="stylesheet" href="/assets/css/interaction-motion.css">
<style>body{font:16px Arial;margin:40px}.login-card{width:340px}.field{margin:12px 0}.btn{position:relative;padding:12px 20px;color:white;background:#b91c1c;border:0;border-radius:8px;min-height:42px}#table{position:relative;min-height:180px}</style>
</head><body class="auth-page"><div class="login-card"><div class="card-head"><h2>Welcome back</h2></div>
<form id="loginForm" action="/submit" method="post">
<input type="hidden" name="csrf_fixture" value="unchanged">
<div class="field"><label>Username <input id="username" name="username" required autocomplete="username"></label></div>
<div class="field"><label>Password <input id="password" name="password" type="password" required></label></div>
<div class="options"><input type="checkbox" name="remember" value="on" checked> Remember me</div>
<button id="submit" class="btn" type="submit" name="action" value="save"><span id="label">Sign in</span></button>
</form></div><div id="table"><button id="tableButton" type="button">Table action</button></div>
<script src="/assets/js/global-loader.js"></script></body></html>`;

test.before(async () => {
  server = http.createServer((req, res) => {
    if (req.url === '/slow-navigation') { navigations.push(res); return; }
    if (req.url === '/navigation-state' && req.method === 'POST') {
      let body = ''; req.on('data', chunk => body += chunk);
      req.on('end', () => { navigationReports.push(JSON.parse(body)); res.writeHead(204); res.end(); }); return;
    }
    if (req.url === '/fixture-native-repeat' && req.method === 'POST') {
      let body = ''; req.on('data', chunk => body += chunk);
      req.on('end', () => { nativeRepeatReports.push(JSON.parse(body)); res.writeHead(204); res.end(); }); return;
    }
    if (req.url === '/submit' && req.method === 'POST') {
      res.setHeader('Content-Type', 'text/html; charset=utf-8');
      let body = ''; req.on('data', chunk => body += chunk); req.on('end', () => posts.push({ body, res })); return;
    }
    if (req.url?.startsWith('/assets/')) {
      const file = path.join(root, 'public', req.url.split('?')[0]);
      if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file)) { res.writeHead(404); res.end(); return; }
      res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file)); return;
    }
    res.setHeader('Content-Type', 'text/html'); res.end(fixture);
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = `http://127.0.0.1:${server.address().port}`;
  browser = await browserType.launch({ headless: true, ...(process.env.TQ_BROWSER_CHANNEL ? { channel: process.env.TQ_BROWSER_CHANNEL } : {}) });
});
test.after(async () => {
  for (const post of posts) if (!post.res.writableEnded) post.res.end('Done');
  for (const response of navigations) if (!response.writableEnded) response.end('Next page');
  if (browser) await browser.close();
  if (server) {
    server.closeAllConnections();
    await new Promise(resolve => server.close(resolve));
  }
  const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
  fs.writeFileSync(path.join(dir, 'performance-' + engine + '.json'), JSON.stringify(metrics, null, 2));
});
async function pageFor(mode = 'full', mobile = false) {
  const context = await browser.newContext({ viewport: mobile ? { width: 390, height: 844 } : { width: 1280, height: 900 }, reducedMotion: mode === 'reduced' ? 'reduce' : 'no-preference' });
  await context.addInitScript(({ mode }) => {
    Object.defineProperty(Navigator.prototype, 'hardwareConcurrency', { configurable: true, get: () => mode === 'lite' ? 2 : 8 });
    Object.defineProperty(Navigator.prototype, 'deviceMemory', { configurable: true, get: () => mode === 'lite' ? 2 : 8 });
  }, { mode });
  const page = await context.newPage();
  const errors = []; page.on('pageerror', error => errors.push(error.message));
  await page.goto(origin);
  return { page, context, errors };
}
async function waitForPosts(count) {
  for (let i = 0; i < 100 && posts.length < count; i++) await new Promise(resolve => setTimeout(resolve, 10));
  assert.equal(posts.length, count);
}
async function waitForNativeRepeat(count) {
  for (let i = 0; i < 300 && nativeRepeatReports.length < count; i++) await new Promise(resolve => setTimeout(resolve, 10));
  assert.equal(nativeRepeatReports.length, count);
}

test('native validation, submitter values and duplicate submission protection survive feedback', { timeout: 15000 }, async t => {
  t.diagnostic('Opening the native form fixture');
  const { page, context, errors } = await pageFor();
  t.after(async () => {
    for (const post of posts) if (!post.res.writableEnded) post.res.end('Done');
    await context.close();
  });
  async function clickSubmit() {
    const box = await page.locator('#submit').boundingBox(); assert.ok(box);
    // A real pointer event does not attach an automation navigation waiter to
    // the deliberately held native response.
    await page.mouse.click(box.x + box.width / 2, box.y + box.height / 2);
  }
  await clickSubmit(); assert.equal(posts.length, 0);
  t.diagnostic('Invalid submission kept the form usable');
  assert.equal(await page.locator('#submit').getAttribute('aria-busy'), null);
  await page.locator('#username').fill('fixture-user'); await page.locator('#password').fill('fixture-password');
  await page.evaluate(() => {
    // Report through the fixture server: WebKit can retire an automation binding
    // during provisional navigation, even while the native POST response is held.
    document.addEventListener('submit', () => {
      queueMicrotask(() => {
        const form = document.querySelector('#loginForm'), button = document.querySelector('#submit');
        form.requestSubmit(button);
        navigator.sendBeacon('/fixture-native-repeat', JSON.stringify({ busy: button.getAttribute('aria-busy'), disabled: button.disabled }));
      });
    }, { once: true });
  });
  await clickSubmit(); await waitForPosts(1);
  t.diagnostic('Native POST captured without waiting for its response');
  await waitForNativeRepeat(1);
  assert.deepEqual(nativeRepeatReports[0], { busy: 'true', disabled: false });
  t.diagnostic('Repeat submission feedback captured');
  assert.equal(posts.length, 1);
  const values = new URLSearchParams(posts[0].body);
  assert.equal(values.get('username'), 'fixture-user'); assert.equal(values.get('password'), 'fixture-password');
  assert.equal(values.get('csrf_fixture'), 'unchanged'); assert.equal(values.get('action'), 'save'); assert.equal(values.get('remember'), 'on');
  posts[0].res.end('Saved'); await page.waitForURL(origin + '/submit');
  t.diagnostic('Native HTML response committed');
  assert.deepEqual(errors, []);
});

test('buttons keep their size, DOM nodes, bound listeners and keyboard focus', async () => {
  const { page, context, errors } = await pageFor();
  const result = await page.evaluate(() => {
    const btn = document.querySelector('#submit'), label = document.querySelector('#label');
    label.addEventListener('fixture', () => { window.fixtureEvent = true; });
    const before = btn.getBoundingClientRect(); btn.focus();
    GlobalLoader.showButtonSpinner(btn, 'Signing in…', true);
    const during = btn.getBoundingClientRect(); label.dispatchEvent(new Event('fixture'));
    const same = document.querySelector('#label') === label;
    GlobalLoader.hideButtonSpinner(btn);
    return { width: before.width === during.width, height: before.height === during.height, same, bound: window.fixtureEvent, focus: document.activeElement === btn, children: btn.children.length };
  });
  assert.deepEqual(result, { width: true, height: true, same: true, bound: true, focus: true, children: 1 });
  assert.deepEqual(errors, []); await context.close();
});

test('page entry stays focusable immediately and the login stagger ends below 300 ms', async () => {
  const { page, context } = await pageFor();
  const result = await page.evaluate(() => {
    const input = document.querySelector('#username'); input.focus();
    const stages = [...document.querySelectorAll('.card-head, #loginForm .field, #loginForm .options, #submit')];
    return { focused: document.activeElement === input, budgets: stages.map(el => { const s = getComputedStyle(el); return 1000 * (parseFloat(s.animationDuration) + parseFloat(s.animationDelay)); }) };
  });
  assert.equal(result.focused, true); assert.ok(Math.max(...result.budgets) < 300);
  metrics.loginStaggerMs = Math.max(...result.budgets); await context.close();
});

test('reduced and lite modes preserve features with the intended motion limits', async () => {
  for (const mode of ['lite', 'reduced']) {
    const { page, context } = await pageFor(mode, true);
    const result = await page.evaluate(() => {
      const btn = document.querySelector('#submit'); btn.focus(); GlobalLoader.showButtonSpinner(btn, 'Saving', true);
      const style = getComputedStyle(btn), spin = getComputedStyle(btn.querySelector('.gl-btn-spinner'));
      return { mode: TerminalMotion.getMode(), animation: style.animationDuration, spinner: spin.animationName, focus: document.activeElement === btn, value: btn.value };
    });
    assert.equal(result.mode, mode); assert.equal(result.spinner, mode === 'lite' ? 'gl-spin' : 'none'); assert.equal(result.focus, true); assert.equal(result.value, 'save');
    assert.equal(result.animation, mode === 'lite' ? '0.1s' : '0s'); await context.close();
  }
});

test('table skeleton feedback does not block table actions or race on reuse', async () => {
  const { page, context } = await pageFor();
  await page.evaluate(() => {
    const box = document.querySelector('#table');
    document.querySelector('#tableButton').addEventListener('click', () => window.tableClicks = (window.tableClicks || 0) + 1);
    GlobalLoader.showTableLoader(box, 'Loading', true); GlobalLoader.hideTableLoader(box); GlobalLoader.showTableLoader(box, 'Refreshing', true);
  });
  await page.locator('#tableButton').click();
  assert.equal(await page.evaluate(() => window.tableClicks), 1);
  assert.equal(await page.locator('.table-loader-overlay').count(), 1); await context.close();
});

test('phone loading indicators keep moving in lite mode and stop for reduced motion', async () => {
  for (const mode of ['full', 'lite', 'reduced']) {
    const { page, context, errors } = await pageFor(mode, true);
    const styles = await page.evaluate(() => {
      const button = document.querySelector('#submit'); GlobalLoader.showButtonSpinner(button, 'Saving', true); GlobalLoader.start(true);
      const samples = ['spinner-border', 'table-loader-spinner', 'fa-spin'];
      const nodes = samples.map(className => { const el = document.createElement('span'); el.className = className; document.body.appendChild(el); return el; });
      const spinner = button.querySelector('.gl-btn-spinner');
      return { spinner: getComputedStyle(spinner).animationName, legacy: nodes.map(el => getComputedStyle(el).animationName),
        barDisplay: getComputedStyle(document.querySelector('#global-progress-bar')).display,
        sweep: getComputedStyle(document.querySelector('#global-progress-bar'), '::after').animationName };
    });
    assert.equal(styles.spinner, mode === 'reduced' ? 'none' : 'gl-spin');
    assert.notEqual(styles.barDisplay, 'none'); assert.equal(styles.sweep, mode === 'reduced' ? 'none' : 'gl-progress-sweep');
    if (mode === 'lite') assert.deepEqual(styles.legacy, ['gl-spin', 'gl-spin', 'gl-spin']);
    if (mode !== 'reduced') {
      const before = await page.locator('.gl-btn-spinner').evaluate(el => getComputedStyle(el).transform);
      await page.waitForTimeout(120);
      const after = await page.locator('.gl-btn-spinner').evaluate(el => getComputedStyle(el).transform);
      assert.notEqual(after, before);
    }
    await page.emulateMedia({ reducedMotion: 'reduce' });
    assert.equal(await page.locator('.gl-btn-spinner').evaluate(el => getComputedStyle(el).animationName), 'none');
    assert.deepEqual(errors, []); await context.close();
  }
});

test('a phone shows navigation feedback while a slow destination is still loading', { timeout: 15000 }, async t => {
  const { page, context, errors } = await pageFor('lite', true);
  t.after(async () => { for (const response of navigations) if (!response.writableEnded) response.end('Next page'); await context.close(); });
  const count = navigations.length, reportCount = navigationReports.length;
  await page.evaluate(() => {
    const link = document.createElement('a'); link.href = '/slow-navigation'; link.textContent = 'Schedules'; link.id = 'navigation'; document.body.appendChild(link);
    link.addEventListener('click', () => setTimeout(() => {
      const bar = document.querySelector('#global-progress-bar');
      navigator.sendBeacon('/navigation-state', JSON.stringify({ visible: GlobalLoader.isVisible(), mode: TerminalMotion.getMode(),
        display: getComputedStyle(bar).display, animation: getComputedStyle(bar, '::after').animationName }));
    }, 80), { once: true });
  });
  const box = await page.locator('#navigation').boundingBox(); assert.ok(box);
  await page.mouse.click(box.x + box.width / 2, box.y + box.height / 2);
  for (let i = 0; i < 300 && (navigations.length === count || navigationReports.length === reportCount); i++) await new Promise(resolve => setTimeout(resolve, 10));
  assert.equal(navigations.length, count + 1); assert.equal(navigationReports.length, reportCount + 1);
  assert.deepEqual(navigationReports[reportCount], { visible: true, mode: 'lite', display: 'block', animation: 'gl-progress-sweep' });
  navigations[count].setHeader('Content-Type', 'text/html'); navigations[count].end('<p>Schedules ready</p>');
  await page.waitForURL(origin + '/slow-navigation'); assert.deepEqual(errors, []);
});

test('a new request during completion keeps the progress bar active without unlocking a button', async () => {
  const { page, context, errors } = await pageFor();
  await page.evaluate(() => {
    window.fetch = undefined; // Install a deterministic transport before loading a fresh wrapper.
    delete window.GlobalLoader;
    document.querySelector('#global-progress-bar').remove();
    window.fixtureResolvers = [];
    window.fetch = () => new Promise(resolve => window.fixtureResolvers.push(resolve));
  });
  await page.addScriptTag({ path: path.join(root, 'public/assets/js/global-loader.js') });
  await page.evaluate(async () => {
    GlobalLoader.setDelay(0); GlobalLoader.showButtonSpinner(document.querySelector('#submit'), 'Saving', true);
    const first = fetch('/first'); fixtureResolvers[0]({ ok: true }); await first;
  });
  await page.evaluate(() => { window.second = fetch('/second'); });
  await page.waitForFunction(() => document.querySelector('.global-progress-bar-inner').style.transform.startsWith('scaleX('));
  assert.equal(await page.evaluate(() => GlobalLoader.isRunning()), true);
  assert.equal(await page.locator('#submit').getAttribute('aria-busy'), 'true');
  await page.evaluate(async () => { fixtureResolvers[1]({ ok: true }); await second; GlobalLoader.hideButtonSpinner(document.querySelector('#submit')); });
  assert.deepEqual(errors, []); await context.close();
});

test('compositor progress updates avoid the old per-frame width layouts', { skip: engine !== 'chromium' }, async () => {
  const baseRef = process.env.TQ_BASE_REF || 'c4048e1923aac4452526f7d9b45f7eca5192df8c';
  const beforeSource = execFileSync('git', ['show', baseRef + ':public/assets/js/global-loader.js'], { cwd: root, encoding: 'utf8' });
  const afterSource = fs.readFileSync(path.join(root, 'public/assets/js/global-loader.js'), 'utf8');
  for (const [label, source] of [['before', beforeSource], ['after', afterSource]]) {
    const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
    await page.setContent('<!doctype html><html><head></head><body><button>Ready</button></body></html>');
    await page.addScriptTag({ content: source });
    await page.evaluate(() => { GlobalLoader.setDelay(0); GlobalLoader.start(true); GlobalLoader.set(10); });
    const cdp = await page.context().newCDPSession(page); await cdp.send('Performance.enable');
    const initial = (await cdp.send('Performance.getMetrics')).metrics;
    await page.evaluate(async () => { for (let i = 0; i < 36; i++) { GlobalLoader.set(10 + i * 2); await new Promise(requestAnimationFrame); } });
    const final = (await cdp.send('Performance.getMetrics')).metrics;
    const delta = name => final.find(m => m.name === name).value - initial.find(m => m.name === name).value;
    metrics[label] = { layoutCount: delta('LayoutCount'), layoutDurationMs: 1000 * delta('LayoutDuration'), taskDurationMs: 1000 * delta('TaskDuration'), scriptDurationMs: 1000 * delta('ScriptDuration') };
    await page.close();
  }
  assert.ok(metrics.after.layoutCount < metrics.before.layoutCount, JSON.stringify(metrics));
  console.log('Fixture rendering metrics:', JSON.stringify(metrics));
});

'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const playwright = require('playwright');
const { assetContentType } = require('./harness.cjs');
const root = path.resolve(__dirname, '../..');
const engine = process.env.TQ_BROWSER || 'chromium';
const selectors = { guest: '.search-bar button[type="submit"]', search: '.search-bar button[type="submit"]', schedules: '.filter-btn[type="submit"]', login: '#loginForm button[type="submit"]', 'admin-settings': '#formIdentity button[type="submit"]' };
const report = { engine, cases: [], beforeAfter: {} };
const fixtures = new Map(), requests = [];
let browser, server, origin, oldLoader;
function fixture(name, baseline) {
  if (!fixtures.has(name)) {
    const rendered = execFileSync('php', [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme'], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
    // Keep production markup and real generated theme CSS. Network/navigation
    // contracts are covered separately; these fixtures isolate button rendering.
    let html = rendered.replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, '');
    html = html.replace(/https:\/\/cdn\.jsdelivr\.net\/npm\/bootstrap@5\.3\.0\/dist\/css\/bootstrap\.min\.css/g, '/fixture/bootstrap.css');
    html = html.replace('</head>', '<script src="/assets/js/interaction-motion.js"></script></head>');
    fixtures.set(name, html);
  }
  return fixtures.get(name).replace('</body>', '<script src="/assets/js/global-loader.js' + (baseline ? '?baseline=1' : '') + '"></script></body>');
}
test.before(async () => {
  oldLoader = execFileSync('git', ['show', (process.env.TQ_BUTTON_BASE_REF || '64adc8c9c456bc7888f69db4bd8465bc96b76af0') + ':public/assets/js/global-loader.js'], { cwd: root });
  for (const name of Object.keys(selectors)) fixture(name, false);
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname === '/fixture-action' && req.method === 'POST') {
      let body = ''; req.on('data', chunk => body += chunk);
      req.on('end', () => requests.push({ body, header: req.headers['x-fixture'], res })); return;
    }
    if (url.pathname === '/fixture.svg') { res.setHeader('Content-Type', 'image/svg+xml'); res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#b71c1c"/></svg>'); return; }
    if (url.pathname === '/fixture/bootstrap.css') { res.setHeader('Content-Type', 'text/css'); res.end(fs.readFileSync(path.join(__dirname, 'node_modules/bootstrap/dist/css/bootstrap.min.css'))); return; }
    if (url.pathname.startsWith('/assets/')) {
      const file = path.resolve(root, 'public', '.' + url.pathname);
      if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file)) { res.writeHead(404); res.end(); return; }
      res.setHeader('Content-Type', assetContentType(file));
      res.end(url.pathname === '/assets/js/global-loader.js' && url.searchParams.has('baseline') ? oldLoader : fs.readFileSync(file)); return;
    }
    const name = url.pathname.slice(1);
    if (!selectors[name]) { res.writeHead(404); res.end(); return; }
    res.setHeader('Content-Type', 'text/html'); res.end(fixture(name, url.searchParams.has('baseline')));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
  browser = await playwright[engine].launch({ headless: true, ...(process.env.TQ_BROWSER_CHANNEL ? {channel:process.env.TQ_BROWSER_CHANNEL} : {}), ...(process.env.TQ_BROWSER_EXECUTABLE ? {executablePath:process.env.TQ_BROWSER_EXECUTABLE} : {}) }); report.browserVersion = browser.version();
});
test.after(async () => {
  for (const request of requests) if (!request.res.writableEnded) request.res.end('Done');
  if (browser) await browser.close();
  if (server) { server.closeAllConnections(); await new Promise(resolve => server.close(resolve)); }
  const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
  fs.writeFileSync(path.join(dir, 'pending-buttons-' + engine + '.json'), JSON.stringify(report, null, 2));
});
async function open(t, name, mode = 'full', width = 320, baseline = false) {
  const context = await browser.newContext({ viewport: { width, height: 844 }, reducedMotion: mode === 'reduced' ? 'reduce' : 'no-preference' });
  t.after(() => context.close());
  await context.addInitScript(({ lite }) => {
    Object.defineProperty(Navigator.prototype, 'hardwareConcurrency', { configurable: true, get: () => lite ? 2 : 8 });
    Object.defineProperty(Navigator.prototype, 'deviceMemory', { configurable: true, get: () => lite ? 2 : 8 });
  }, { lite: mode === 'lite' });
  await context.route('**/*', route => route.request().url().startsWith(origin) || route.request().url().startsWith('data:') ? route.continue() : route.abort());
  const page = await context.newPage(), errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.goto(origin + '/' + name + (baseline ? '?baseline=1' : ''));
  await page.evaluate(() => Promise.all(document.getAnimations().filter(a => a.effect?.getTiming().iterations !== Infinity).map(a => a.finished.catch(() => {}))));
  return { page, context, errors };
}
function transparent(value) { return value === 'transparent' || /^rgba\(.+,\s*0\)$/.test(value) || /\/\s*0\s*\)$/.test(value); }
async function pending(page, selector) {
  return page.evaluate(selector => {
    const button = document.querySelector(selector), nodes = [...button.childNodes], before = button.getBoundingClientRect();
    window.fixtureButton = button; window.fixtureNodes = nodes; window.fixtureInlineStyle = button.getAttribute('style');
    window.fixturePaint = { color: getComputedStyle(button).color, fill: getComputedStyle(button).getPropertyValue('-webkit-text-fill-color') };
    button.addEventListener('fixture-node', () => window.fixtureBound = true, { once: true });
    button.focus();
    GlobalLoader.showButtonSpinner(button, undefined, true);
    GlobalLoader.showButtonSpinner(button, 'Duplicate', true);
    button.dispatchEvent(new Event('fixture-node'));
    const after = button.getBoundingClientRect(), style = getComputedStyle(button), feedback = button.querySelector('.gl-btn-feedback'), feedbackStyle = getComputedStyle(feedback);
    return {
      color: style.color, fill: style.getPropertyValue('-webkit-text-fill-color'),
      feedbackColor: feedbackStyle.color, feedbackFill: feedbackStyle.getPropertyValue('-webkit-text-fill-color'), feedbackOpacity: feedbackStyle.opacity, label: feedback.textContent.trim(),
      width: Math.abs(before.width - after.width), height: Math.abs(before.height - after.height),
      nodes: nodes.every((node, i) => button.childNodes[i] === node), bound: window.fixtureBound, focused: document.activeElement === button,
      childOpacities: nodes.filter(node => node.nodeType === 1).map(node => getComputedStyle(node).opacity),
      disabled: button.disabled, feedbackCount: button.querySelectorAll('.gl-btn-feedback').length
    };
  }, selector);
}
async function restored(page) {
  return page.evaluate(() => {
    const button = window.fixtureButton; GlobalLoader.hideButtonSpinner(button);
    const style = getComputedStyle(button);
    return { nodes: button.childNodes.length === fixtureNodes.length && fixtureNodes.every((node, i) => button.childNodes[i] === node),
      color: style.color === fixturePaint.color, fill: style.getPropertyValue('-webkit-text-fill-color') === fixturePaint.fill,
      inlineStyle: (button.getAttribute('style') || '') === (fixtureInlineStyle || ''), feedback: button.querySelectorAll('.gl-btn-feedback').length };
  });
}
async function shot(page, selector, label) {
  const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
  await page.locator(selector).screenshot({ path: path.join(dir, engine + '-button-' + label + '.png'), animations: 'disabled' });
}

test('themed production buttons display one pending label without layout or focus changes', { timeout: 70000 }, async t => {
  for (const name of Object.keys(selectors)) for (const mode of ['full', 'lite', 'reduced']) for (const width of [320, 1280]) {
    const { page, context, errors } = await open(t, name, mode, width);
    const value = await pending(page, selectors[name]);
    assert.ok(transparent(value.color) && (!value.fill || transparent(value.fill)), JSON.stringify(value));
    assert.ok(!transparent(value.feedbackColor) && (!value.feedbackFill || !transparent(value.feedbackFill)));
    assert.equal(value.feedbackOpacity, '1'); assert.equal(value.feedbackCount, 1);
    assert.ok(value.width < .25 && value.height < .25); assert.equal(value.nodes, true); assert.equal(value.bound, true); assert.equal(value.focused, true); assert.equal(value.disabled, false);
    assert.ok(value.childOpacities.every(opacity => opacity === '0'));
    if (name === 'guest') assert.equal(value.label, 'Searching\u2026');
    if (name === 'guest' && mode === 'lite' && width === 320) await shot(page, selectors[name], 'guest-after-lite');
    assert.deepEqual(await restored(page), { nodes: true, color: true, fill: true, inlineStyle: true, feedback: 0 });
    assert.deepEqual(errors, []); report.cases.push({ page: name, mode, width, passed: true }); await context.close();
  }
});

test('search and filter feedback stay centered inside the button on phones and desktop', { timeout: 70000 }, async t => {
  for (const name of ['guest', 'search', 'schedules']) for (const mode of ['full', 'lite', 'reduced']) for (const width of [320, 390, 1280]) {
    const { page, context, errors } = await open(t, name, mode, width);
    await pending(page, selectors[name]);
    const geometry = await page.evaluate(selector => {
      const button = document.querySelector(selector), spinner = button.querySelector('.gl-btn-spinner'), label = button.querySelector('.gl-btn-label');
      // Freeze at a quarter turn so rotation does not expand the measured bounds.
      const animation = spinner.getAnimations()[0]; if (animation) { animation.pause(); animation.currentTime = 200; }
      const b = button.getBoundingClientRect(), s = spinner.getBoundingClientRect(), l = label.getBoundingClientRect();
      return { inside: s.left >= b.left + 4 && l.right <= b.right - 4,
        vertical: Math.abs((s.top + s.bottom - b.top - b.bottom) / 2),
        horizontal: Math.abs((s.left + l.right - b.left - b.right) / 2),
        labelFits: label.scrollWidth <= label.clientWidth + 1, labelWidth:label.clientWidth, textWidth:label.scrollWidth, font:getComputedStyle(label).font, fontFamily:getComputedStyle(label).fontFamily,
        spinnerMargin: getComputedStyle(spinner).marginRight, square: Math.abs(s.width - s.height),
        labelColor: getComputedStyle(label).color, feedbackColor: getComputedStyle(button.querySelector('.gl-btn-feedback')).color };
    }, selectors[name]);
    assert.ok(geometry.inside && geometry.labelFits, JSON.stringify({ name, mode, width, geometry }));
    if (name !== 'schedules') assert.match(geometry.fontFamily, /Outfit/, 'search feedback uses the original page font');
    assert.ok(geometry.vertical < 1 && geometry.horizontal < 1 && geometry.square < 1, JSON.stringify(geometry));
    assert.equal(geometry.spinnerMargin, '0px'); assert.equal(geometry.labelColor, geometry.feedbackColor);
    if (name === 'search' && mode === 'lite' && width === 390) await shot(page, selectors[name], 'search-centered-phone');
    assert.deepEqual(errors, []); await context.close();
  }
});

test('before/after reproduces both guest labels being painted by the original theme rules', { timeout: 20000 }, async t => {
  for (const mode of ['full', 'lite']) {
    const before = await open(t, 'guest', mode, 320, true), after = await open(t, 'guest', mode);
    const old = await pending(before.page, selectors.guest);
    assert.ok(!transparent(old.color) && !transparent(old.fill), JSON.stringify(old)); assert.equal(old.feedbackCount, 1); assert.equal(old.label, 'Searching\u2026');
    if (mode === 'lite') await shot(before.page, selectors.guest, 'guest-before-lite');
    const fixed = await pending(after.page, selectors.guest);
    assert.ok(transparent(fixed.color) && (!fixed.fill || transparent(fixed.fill)));
    assert.deepEqual(await restored(after.page), { nodes: true, color: true, fill: true, inlineStyle: true, feedback: 0 });
    assert.deepEqual(before.errors, []); assert.deepEqual(after.errors, []);
    report.beforeAfter[mode] = { before: 'original and loading labels both painted', after: 'only loading label painted', cleanup: 'original nodes and text styles restored' };
    await before.context.close(); await after.context.close();
  }
});

test('inline styles and native input values survive cleanup; the first keyboard action sends immediately', { timeout: 20000 }, async t => {
  const { page, context, errors } = await open(t, 'guest');
  const styles = await page.evaluate(() => {
    const button = document.querySelector('.search-bar button[type="submit"]');
    button.style.setProperty('color', '#123456', 'important'); button.style.setProperty('-webkit-text-fill-color', '#abcdef');
    const before = button.style.cssText; GlobalLoader.showButtonSpinner(button, 'Searching', true); GlobalLoader.hideButtonSpinner(button);
    const input = document.createElement('input'); input.type = 'submit'; input.name = 'action'; input.value = 'original-value'; document.body.appendChild(input);
    const snapshot = () => JSON.stringify({ name: input.name, value: input.value, type: input.type, disabled: input.disabled, style: input.style.cssText });
    const original = snapshot(); GlobalLoader.showButtonSpinner(input, undefined, true); const during = snapshot(); GlobalLoader.hideButtonSpinner(input);
    GlobalLoader.showButtonSpinner(button); GlobalLoader.hideButtonSpinner(button);
    return { styles: button.style.cssText === before, input: snapshot() === original && during === original, value: input.value, feedback: button.querySelectorAll('.gl-btn-feedback').length };
  });
  assert.deepEqual(styles, { styles: true, input: true, value: 'original-value', feedback: 0 });
  const count = requests.length;
  await page.evaluate(() => {
    const button = document.createElement('button'); button.id = 'fixture-action'; button.type = 'button'; button.textContent = 'Save';
    document.body.appendChild(button); window.fixtureClicks = 0; window.fixtureDone = 0;
    button.addEventListener('click', () => {
      window.fixtureClicks++; GlobalLoader.showButtonSpinner(button, 'Saving');
      window.fixtureStartedBeforeFeedback = !button.querySelector('.gl-btn-feedback');
      fetch('/fixture-action', { method: 'POST', headers: { 'X-Fixture': 'unchanged' }, body: 'original-payload' }).finally(() => { GlobalLoader.hideButtonSpinner(button); window.fixtureDone++; });
    }); button.focus();
  });
  await page.keyboard.press('Space');
  for (let i = 0; i < 100 && requests.length === count; i++) await new Promise(resolve => setTimeout(resolve, 10));
  assert.equal(requests.length, count + 1); assert.equal(requests[count].body, 'original-payload'); assert.equal(requests[count].header, 'unchanged');
  assert.equal(await page.evaluate(() => window.fixtureStartedBeforeFeedback), true);
  await page.locator('#fixture-action .gl-btn-feedback').waitFor({ state: 'visible' });
  await page.keyboard.press('Space'); await page.locator('#fixture-action').scrollIntoViewIfNeeded();
  const box = await page.locator('#fixture-action').boundingBox(); assert.ok(box);
  await page.mouse.click(box.x + box.width / 2, box.y + box.height / 2);
  assert.equal(await page.evaluate(() => window.fixtureClicks), 1); assert.equal(requests.length, count + 1);
  requests[count].res.writeHead(500); requests[count].res.end('Fixture failure');
  await page.waitForFunction(() => window.fixtureDone === 1);
  assert.equal(await page.locator('#fixture-action .gl-btn-feedback').count(), 0);
  assert.equal(await page.locator('#fixture-action').getAttribute('aria-busy'), null);
  assert.deepEqual(errors, []); await context.close();
});

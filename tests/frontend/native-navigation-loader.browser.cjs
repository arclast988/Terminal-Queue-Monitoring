'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const playwright = require('playwright');
const root = path.resolve(__dirname, '../..');
const engine = process.env.TQ_BROWSER || 'chromium';
const desktopUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/143.0.0.0 Safari/537.36';
const androidUA = 'Mozilla/5.0 (Linux; Android 15; Mobile) AppleWebKit/537.36 Chrome/143.0.0.0 Mobile Safari/537.36';
const iphoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 Version/18.0 Mobile/15E148 Safari/604.1';
const profiles = [
  { name: 'android-phone', ua: androidUA, width: 390, height: 844, touch: true, native: true },
  { name: 'android-firefox', ua: 'Mozilla/5.0 (Android 15; Mobile; rv:145.0) Gecko/145.0 Firefox/145.0', width: 360, height: 800, touch: true, native: true },
  { name: 'iphone-safari', ua: iphoneUA, width: 375, height: 812, touch: true, native: true },
  { name: 'ipad-desktop-profile', ua: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 Version/18.0 Safari/605.1.15', width: 1024, height: 768, touch: true, platform: 'MacIntel', touchPoints: 5, native: true },
  { name: 'android-tablet', ua: androidUA, width: 768, height: 1024, touch: true, native: true },
  { name: 'desktop', ua: desktopUA, width: 1366, height: 768, native: true },
  { name: 'wide-desktop', ua: desktopUA, width: 1920, height: 1080, native: true },
  { name: 'narrow-desktop', ua: desktopUA, width: 320, height: 640, native: true },
  { name: 'installed-android-app', ua: androidUA, width: 390, height: 844, touch: true, standalone: true, native: false },
  { name: 'android-webview', ua: androidUA.replace('Android 15;', 'Android 15; wv;'), width: 390, height: 844, touch: true, native: false },
  { name: 'ios-webview', ua: iphoneUA.replace(' Safari/604.1', ''), width: 375, height: 812, touch: true, native: false }
];
const reports = new Map(), pending = new Map();
const results = { engine, profiles: [], forms: [], requests: [] };
let server, browser, origin, nextId = 0;
function fixture(id) {
  return `<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1">
<style>body{margin:0;font:16px Arial}main{padding:12px}button,a{display:inline-block;padding:12px;margin:4px}#table{position:relative;min-height:100px}</style></head><body><main>
<a id="navigate" href="/slow-link?id=${id}">Next page</a>
<form id="native-form" action="/slow-form?id=${id}" method="post">
<input type="hidden" name="csrf_fixture" value="unchanged">
<input type="hidden" name="vehicle" value="fixture-vehicle">
<button id="submit" type="submit" name="action" value="save">Save</button></form>
<div id="table">Table content</div></main>
<script src="/loader.js"></script><script>
function report(kind,event){
  var bar=document.getElementById('global-progress-bar');
  var button=document.getElementById('submit');
  navigator.sendBeacon('/report',JSON.stringify({id:'${id}',kind:kind,
    visible:GlobalLoader.isVisible(),running:GlobalLoader.isRunning(),
    active:bar.classList.contains('is-active'),count:document.querySelectorAll('#global-progress-bar').length,
    pointerEvents:getComputedStyle(bar).pointerEvents,width:bar.getBoundingClientRect().width,
    viewport:innerWidth,overflow:document.documentElement.scrollWidth>innerWidth,
    prevented:event.defaultPrevented,busy:button.getAttribute('aria-busy'),disabled:button.disabled}));
}
document.addEventListener('DOMContentLoaded',function(){
  document.addEventListener('click',function(event){
    if(event.target.closest('#navigate'))queueMicrotask(function(){report('link',event);});
  });
  document.addEventListener('submit',function(event){
    queueMicrotask(function(){report('form',event);});
  });
});</script></body></html>`;
}
test.before(async () => {
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname === '/loader.js') {
      res.setHeader('Content-Type', 'text/javascript');
      res.end(fs.readFileSync(path.join(root, 'public/assets/js/global-loader.js'))); return;
    }
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      if (url.pathname === '/report') {
        const report = JSON.parse(body); reports.set(report.id + ':' + report.kind, report);
        res.writeHead(204); res.end(); return;
      }
      if (url.pathname.startsWith('/slow-')) {
        pending.set(url.searchParams.get('id') + ':' + url.pathname, { res, body, method: req.method }); return;
      }
      res.setHeader('Content-Type', 'text/html'); res.end(fixture(url.searchParams.get('id') || '0'));
    });
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
  browser = await playwright[engine].launch({ headless: true,
    ...(process.env.TQ_BROWSER_CHANNEL ? { channel: process.env.TQ_BROWSER_CHANNEL } : {}),
    ...(process.env.TQ_BROWSER_EXECUTABLE ? { executablePath: process.env.TQ_BROWSER_EXECUTABLE } : {}) });
  results.browserVersion = browser.version();
});
test.after(async () => {
  for (const entry of pending.values()) if (!entry.res.writableEnded) entry.res.end('Done');
  if (browser) await browser.close();
  if (server) { server.closeAllConnections(); await new Promise(resolve => server.close(resolve)); }
  const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
  fs.writeFileSync(path.join(dir, 'native-navigation-loader-' + engine + '.json'), JSON.stringify(results, null, 2));
});
async function waitEntry(map, key) {
  for (let n = 0; n < 250 && !map.has(key); n++) await new Promise(resolve => setTimeout(resolve, 20));
  assert.ok(map.has(key), 'Missing fixture event: ' + key); return map.get(key);
}
async function open(t, profile) {
  const id = String(++nextId);
  const context = await browser.newContext({ userAgent: profile.ua,
    viewport: { width: profile.width, height: profile.height }, hasTouch: !!profile.touch,
    ...(engine !== 'firefox' && profile.touch ? { isMobile: true } : {}) });
  t.after(() => context.close());
  await context.addInitScript(({ standalone, platform, touchPoints }) => {
    // Simulate an installed display mode and iPad's desktop-style identity.
    // These tests exercise the site's heuristic, not native browser chrome.
    const original = window.matchMedia.bind(window);
    window.matchMedia = query => standalone && query === '(display-mode: standalone)'
      ? { matches: true, media: query, addListener() {}, removeListener() {}, addEventListener() {}, removeEventListener() {} }
      : original(query);
    if (platform) Object.defineProperty(Navigator.prototype, 'platform', { configurable: true, get: () => platform });
    if (touchPoints) Object.defineProperty(Navigator.prototype, 'maxTouchPoints', { configurable: true, get: () => touchPoints });
  }, { standalone: !!profile.standalone, platform: profile.platform, touchPoints: profile.touchPoints });
  const page = await context.newPage(), errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.goto(origin + '/?id=' + id);
  await page.waitForFunction(() => window.GlobalLoader && !GlobalLoader.isVisible() && !GlobalLoader.isRunning());
  return { id, page, context, errors };
}
async function click(page, selector) {
  const box = await page.locator(selector).boundingBox(); assert.ok(box);
  await page.mouse.click(box.x + box.width / 2, box.y + box.height / 2);
}
function checkSnapshot(report, profile) {
  assert.equal(report.visible, !profile.native, profile.name);
  assert.equal(report.running, !profile.native, profile.name);
  assert.equal(report.active, !profile.native, profile.name);
  assert.equal(report.count, 1); assert.equal(report.pointerEvents, 'none');
  assert.ok(Math.abs(report.width - report.viewport) < 1);
  assert.equal(report.overflow, false); assert.equal(report.prevented, false);
}
test('phone, tablet, desktop and installed-app link navigation use the intended feedback', { timeout: 60000 }, async t => {
  for (const profile of profiles) {
    const { id, page, context, errors } = await open(t, profile);
    await click(page, '#navigate');
    const report = await waitEntry(reports, id + ':link'); checkSnapshot(report, profile);
    const navigation = await waitEntry(pending, id + ':/slow-link');
    assert.equal(navigation.method, 'GET'); navigation.res.end('Next page');
    await page.waitForURL(origin + '/slow-link?id=' + id);
    assert.deepEqual(errors, []);
    results.profiles.push({ name: profile.name, width: profile.width, height: profile.height, siteBar: report.visible, passed: true });
    await context.close();
  }
});
test('native form values and button feedback survive mobile-browser and app navigation', { timeout: 30000 }, async t => {
  for (const profile of [profiles[0], profiles[5], profiles[8]]) {
    const { id, page, context, errors } = await open(t, profile);
    await click(page, '#submit');
    const report = await waitEntry(reports, id + ':form'); checkSnapshot(report, profile);
    assert.equal(report.busy, 'true'); assert.equal(report.disabled, false);
    const submission = await waitEntry(pending, id + ':/slow-form');
    assert.equal(submission.method, 'POST');
    const fields = new URLSearchParams(submission.body);
    assert.equal(fields.get('csrf_fixture'), 'unchanged');
    assert.equal(fields.get('vehicle'), 'fixture-vehicle'); assert.equal(fields.get('action'), 'save');
    submission.res.end('Saved'); await page.waitForURL(origin + '/slow-form?id=' + id);
    assert.deepEqual(errors, []); results.forms.push({ profile: profile.name, passed: true }); await context.close();
  }
});
test('mobile in-page fetch and XHR operations retain progress and local loading feedback', { timeout: 30000 }, async t => {
  for (const profile of [profiles[0], profiles[2], profiles[3]]) {
    const { id, page, context, errors } = await open(t, profile);
    await page.evaluate(id => {
      GlobalLoader.setDelay(0); window.fetchDone = false;
      GlobalLoader.showButtonSpinner(document.querySelector('#submit'), 'Saving', true);
      GlobalLoader.showTableLoader('#table', 'Loading', true);
      fetch('/slow-fetch?id=' + id).then(() => {
        window.fetchDone = true; GlobalLoader.hideButtonSpinner(document.querySelector('#submit'));
        GlobalLoader.hideTableLoader('#table', true);
      });
    }, id);
    const fetchRequest = await waitEntry(pending, id + ':/slow-fetch');
    await page.waitForFunction(() => GlobalLoader.isVisible());
    assert.equal(await page.locator('.gl-btn-feedback').count(), 1);
    assert.equal(await page.locator('.table-loader-overlay').count(), 1);
    fetchRequest.res.end('Fetched');
    await page.waitForFunction(() => window.fetchDone && !GlobalLoader.isVisible());
    assert.equal(await page.locator('.gl-btn-feedback').count(), 0);
    assert.equal(await page.locator('.table-loader-overlay').count(), 0);
    await page.evaluate(id => {
      window.xhrDone = false;
      const xhr = new XMLHttpRequest(); xhr.open('POST', '/slow-xhr?id=' + id);
      xhr.onload = () => window.xhrDone = true; xhr.send('original-payload');
    }, id);
    const xhrRequest = await waitEntry(pending, id + ':/slow-xhr');
    await page.waitForFunction(() => GlobalLoader.isVisible()); assert.equal(xhrRequest.body, 'original-payload');
    xhrRequest.res.end('Updated'); await page.waitForFunction(() => window.xhrDone && !GlobalLoader.isVisible());
    assert.deepEqual(errors, []); results.requests.push({ profile: profile.name, passed: true }); await context.close();
  }
});

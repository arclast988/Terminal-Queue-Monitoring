'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const { chromium } = require('playwright');
const { root, assetContentType } = require('./harness.cjs');
let browser, server, origin, variant = '', roundRequests = [];
function fixture(name = 'staff-queue', state = '') {
  return execFileSync(process.env.PHP_BINARY || 'php', [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme', state], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
}
function queuePage(state) {
  let html = fixture('staff-queue', state);
  return html.replace('</head>', `<script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>window.QueueWS={_isLoaded:true,init:function(c){this.config=c},updateConfig:function(c){this.config=c},isConnected:function(){return true}};</script></head>`);
}
test.before(async () => {
  browser = await chromium.launch({ headless: true, ...(process.env.TQ_BROWSER_CHANNEL ? { channel: process.env.TQ_BROWSER_CHANNEL } : {}) });
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname === '/staff/queue') { res.setHeader('Content-Type', 'text/html'); return res.end(queuePage(variant)); }
    if (url.pathname === '/api/queue-status') { res.setHeader('Content-Type', 'application/json'); return res.end(JSON.stringify({ success: true, queue: [], sync_token: 'fixture' })); }
    if (url.pathname === '/staff/queue/round' || url.pathname === '/staff/queue/tick') {
      let body = ''; req.on('data', chunk => body += chunk); req.on('end', () => {
        roundRequests.push({ path: url.pathname, body: new URLSearchParams(body) });
        res.setHeader('Content-Type', 'application/json'); res.end(JSON.stringify({ success: true, csrf: 'unchanged' }));
      }); return;
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.statusCode = 404; return res.end(); }
    res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve)); origin = 'http://127.0.0.1:' + server.address().port;
});
test.after(async () => { await browser?.close(); await new Promise(resolve => server?.close(resolve)); });

test('new registration appears in an open empty Add to Queue dialog and works immediately', async () => {
  variant = 'empty';
  const page = await browser.newPage(); const errors = []; page.on('pageerror', e => errors.push(e.message));
  await page.goto(origin + '/staff/queue');
  await page.locator('[data-bs-target="#addToQueueModal"]').click();
  await page.waitForSelector('#addToQueueModal.show');
  assert.equal(await page.locator('#vehicleListContainer').count(), 0);
  variant = 'fresh';
  await page.evaluate(() => document.dispatchEvent(new CustomEvent('pttm:ws-queue_update', { detail: { data: { action: 'vehicle_created' } } })));
  await page.waitForSelector('#veh_check_202');
  assert.equal(await page.locator('#addToQueueModal.show').count(), 1);
  assert.equal(await page.locator('#addToQueueForm.add-queue-modal-empty').count(), 0);
  await page.locator('#veh_check_202').check();
  assert.equal(await page.locator('#submitToQueueBtn').isEnabled(), true);
  assert.match(await page.locator('#submitToQueueBtn').innerText(), /Add 1 Vehicle/);
  await page.locator('#vehicleModalSearch').fill('NEW-789');
  await page.locator('#addQueueRouteFilter').selectOption('1|BATO');
  assert.equal(await page.locator('.vehicle-select-item:not(.d-none)').count(), 1);
  await page.evaluate(() => QueueSync.refresh(true));
  await page.waitForTimeout(650);
  assert.equal(await page.locator('#veh_check_202').isChecked(), true);
  assert.equal(await page.locator('#vehicleModalSearch').inputValue(), 'NEW-789');
  assert.equal(await page.locator('#addQueueRouteFilter').inputValue(), '1|BATO');
  assert.deepEqual(errors, []);
  await page.close();
});

test('route filters survive refreshes and switching rounds submits the route and round', async () => {
  variant = 'fresh'; roundRequests = [];
  const page = await browser.newPage(); await page.goto(origin + '/staff/queue');
  await page.locator('[data-queue-route="1|BATO"]').click();
  assert.equal(await page.locator('#queue-list .q-card:not(.d-none)').count(), 0);
  assert.equal(await page.locator('#queueRouteEmpty').isVisible(), true);
  await page.locator('[data-dispatch-round][data-route-id="2"]').selectOption('3');
  await page.waitForFunction(() => document.getElementById('queueRoundFeedback').textContent.includes('Round updated'));
  const request = roundRequests.find(r => r.path.endsWith('/round'));
  assert.equal(request.body.get('route_id'), '2'); assert.equal(request.body.get('round_number'), '3');
  assert.equal(request.body.get('csrf_fixture'), 'unchanged');
  await page.evaluate(() => QueueSync.refresh(true)); await page.waitForTimeout(650);
  assert.equal(await page.locator('[data-queue-route="1|BATO"]').getAttribute('aria-pressed'), 'true');
  assert.equal(await page.locator('#queue-list .q-card:not(.d-none)').count(), 0);
  await page.locator('[data-bs-target="#addToQueueModal"]').click();
  assert.equal(await page.locator('#addQueueRouteFilter').inputValue(), '1|BATO');
  assert.equal(await page.locator('.vehicle-select-item:not(.d-none)').count(), 1);
  await page.close();
});

test('saved and live role themes recolor route tabs, queue headers and primary controls', async () => {
  for (const name of ['staff-queue', 'admin-routes', 'admin-users', 'admin-rules', 'guest']) {
    const page = await browser.newPage();
    const html = fixture(name, 'fresh').replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, '');
    await page.route('**/themed', route => route.fulfill({ contentType: 'text/html', body: html }));
    await page.goto(origin + '/themed');
    await page.addScriptTag({ url: origin + '/js/ws-client.js' });
    await page.evaluate(() => {
      applyLiveBranding({ category: 'theme', theme_staff_primary: '#7040b0', theme_staff_nav_bg: '#7040b0', theme_staff_nav_text: '#ffffff', theme_admin_primary: '#7040b0', theme_admin_nav_bg: '#7040b0', theme_admin_nav_text: '#ffffff', theme_guest_primary: '#7040b0', theme_guest_nav_bg: '#ffffff', theme_guest_nav_text: '#1c2430' });
    });
    await page.waitForFunction(() => [...document.querySelectorAll('.btn-modern-primary, .btn-primary, .btn-submit-queue, .rule-filter-btn.active, #tab-active-routes.active, .queue-order-header, .login-btn')].every(el => getComputedStyle(el).backgroundColor === 'rgb(112, 64, 176)'), null, { timeout: 5000 });
    const colors = await page.evaluate(() => {
      const selectors = ['.btn-modern-primary', '.btn-primary', '.btn-submit-queue', '.rule-filter-btn.active', '#tab-active-routes.active', '.queue-order-header', '.login-btn'];
      return selectors.flatMap(s => [...document.querySelectorAll(s)].map(el => ({ selector: s, background: getComputedStyle(el).backgroundColor })));
    });
    assert.ok(colors.length > 0, name);
    for (const item of colors) assert.equal(item.background, 'rgb(112, 64, 176)', name + ': ' + item.selector);
    const directory = path.join(__dirname, 'artifacts'); fs.mkdirSync(directory, { recursive: true });
    await page.screenshot({ path: path.join(directory, 'dispatch-theme-' + name + '.png'), fullPage: true });
    await page.close();
  }
});

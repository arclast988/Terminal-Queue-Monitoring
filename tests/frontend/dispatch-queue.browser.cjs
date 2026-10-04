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
    if (/^\/fixtures\/staff-rule-(create|edit)$/.test(url.pathname) || url.pathname === '/fixtures/staff-rules') {
      res.setHeader('Content-Type', 'text/html'); return res.end(fixture(url.pathname.split('/').pop()));
    }
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

test('checked weekdays keep one explicit round and display consecutive or separate day labels', async () => {
  const page = await browser.newPage(); const errors = []; page.on('pageerror', e => errors.push(e.message));
  await page.goto(origin + '/fixtures/staff-rule-create');
  await page.locator('#ruleEveryDay').uncheck();
  for (const day of ['1', '2', '3']) await page.locator(`input[name="days_of_week[]"][value="${day}"]`).check();
  assert.equal(await page.locator('#ruleDaysSummary').innerText(), 'Monday–Wednesday');
  await page.locator('input[name="days_of_week[]"][value="2"]').uncheck();
  await page.locator('input[name="days_of_week[]"][value="3"]').uncheck();
  for (const day of ['4', '7']) await page.locator(`input[name="days_of_week[]"][value="${day}"]`).check();
  assert.equal(await page.locator('#ruleDaysSummary').innerText(), 'Monday, Thursday, Sunday');
  await page.locator('#round_number').selectOption('2');
  const submitted = await page.locator('#departureRuleForm').evaluate(form => {
    const data = new FormData(form); return { days: data.getAll('days_of_week[]'), round: data.get('round_number') };
  });
  assert.deepEqual(submitted, {days:['1','4','7'], round:'2'});
  assert.equal(await page.locator('#round_number').isVisible(), true);
  assert.equal(await page.locator('#ruleDays .autocomplete-wrapper').count(), 0);
  assert.deepEqual(errors, []);
  await page.close();
});

test('editing restores checked days and the assigned round with usable desktop and mobile layouts', async () => {
  for (const width of [320, 360, 375, 414, 768, 1280]) {
    const page = await browser.newPage({viewport:{width,height:900}});
    await page.goto(origin + '/fixtures/staff-rule-edit');
    assert.deepEqual(await page.locator('input[name="days_of_week[]"]:checked').evaluateAll(boxes => boxes.map(box => box.value)), ['1','2','3']);
    assert.equal(await page.locator('#round_number').inputValue(), '1');
    assert.equal(await page.locator('#round_number').isVisible(), true);
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1));
    await page.locator('#ruleDays').scrollIntoViewIfNeeded();
    const directory = path.join(__dirname, 'artifacts'); fs.mkdirSync(directory, {recursive:true});
    await page.screenshot({path:path.join(directory, `departure-rule-days-${width}.png`)});
    await page.close();
  }
});

test('dispatcher rule list displays grouped days and edit/delete for terminal defaults', async () => {
  const page = await browser.newPage();
  await page.goto(origin + '/fixtures/staff-rules');
  assert.match(await page.locator('#departure-rules-table').innerText(), /Monday–Wednesday/i);
  assert.match(await page.locator('#departure-rules-table').innerText(), /Monday, Thursday, Sunday/i);
  assert.doesNotMatch(await page.locator('#departure-rules-table').innerText(), /View Only|All rounds/);
  assert.equal(await page.locator('.rule-edit-link').count(), 2);
  assert.equal(await page.locator('form[action*="/departure-rules/delete/"]').count(), 2);
  await page.close();
});

test('queue route controls show rule intervals and fit desktop and mobile screens', async () => {
  variant = 'fresh';
  for (const width of [375, 1280]) {
    const page = await browser.newPage({viewport:{width,height:900}});
    await page.goto(origin + '/staff/queue');
    assert.equal(await page.locator('[data-dispatch-round]:visible').count(), 2);
    assert.match(await page.locator('[data-route-id="2"] option:checked').innerText(), /Round 2 · 25 min/);
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1));
    const directory = path.join(__dirname, 'artifacts'); fs.mkdirSync(directory, {recursive:true});
    await page.screenshot({path:path.join(directory, `queue-route-controls-${width}.png`)});
    await page.locator('[data-queue-route="1|BATO"]').click();
    assert.equal(await page.locator('[data-dispatch-round]:visible').count(), 1);
    await page.close();
  }
});

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

test('mobile queue dialogs keep their headers clear and selection cards include all trip details', async () => {
  variant = 'many';
  for (const {width,height} of [{width:320,height:568},{width:360,height:640},{width:375,height:667},{width:414,height:736},{width:667,height:375},{width:768,height:667},{width:1280,height:800}]) {
    const page = await browser.newPage({viewport:{width,height}});
    await page.goto(origin + '/staff/queue');
    await page.locator('#manageQueueBtn').click();
    await page.waitForSelector('#manageQueueModal.show');
    const bounds = await page.evaluate(() => {
      const modal = document.getElementById('manageQueueModal');
      const header = modal.querySelector('.modal-header').getBoundingClientRect();
      const body = modal.querySelector('.modal-body').getBoundingClientRect();
      const cards = [...modal.querySelectorAll('.queue-order-item')].slice(0,2).map(card=>card.getBoundingClientRect());
      const footer = modal.querySelector('.modal-footer').getBoundingClientRect();
      return {headerBottom:header.bottom, bodyTop:body.top, cardTop:cards[0].top, gap:cards[1].top-cards[0].bottom, footerBottom:footer.bottom};
    });
    assert.ok(bounds.bodyTop >= bounds.headerBottom - 1, JSON.stringify({width,...bounds}));
    assert.ok(bounds.cardTop > bounds.headerBottom + 10);
    assert.ok(bounds.gap >= 9);
    assert.ok(bounds.footerBottom <= height);
    const directory = path.join(__dirname,'artifacts');fs.mkdirSync(directory,{recursive:true});
    if(width===375)await page.screenshot({path:path.join(directory,'manage-queue-mobile-spacing.png')});
    await page.locator('#manageQueueModal .btn-close').click();
    await page.locator('#cancelSelectionBtn').click();
    await page.waitForSelector('#cancelSelectionModal.show');
    assert.equal(await page.locator('#cancelSelectionModal .modal-header').evaluate(el=>getComputedStyle(el).backgroundColor),'rgb(21, 128, 61)');
    const first = page.locator('.queue-cancel-item').first();
    assert.match(await first.innerText(), /TRIP-1/);
    assert.match(await first.innerText(), /Jeepney/i);
    for(const label of ['Operator','Driver','Destination','Palompon','Ormoc City'])assert.match(await first.innerText(),new RegExp(label));
    await page.locator('#cancelRouteFilter').selectOption('1|BATO');
    await page.locator('#cancelSelectVisible').check();
    assert.equal(await page.locator('[name="cancel_queue_ids[]"]:checked').count(),5);
    assert.match(await page.locator('#cancelSelectionCount').innerText(),/5 trips selected/);
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
    if(width===375)await page.screenshot({path:path.join(directory,'select-cancel-trips-mobile.png')});
    await page.close();
  }
});

test('selected cancellation submits only reviewed trips and preserves selection on errors', async () => {
  variant='many'; const page=await browser.newPage();let bodies=[],succeed=false;
  await page.route('**/staff/queue/cancel-selected',async route=>{
    bodies.push(new URLSearchParams(route.request().postData()));
    await route.fulfill({status:succeed?200:409,contentType:'application/json',body:JSON.stringify(succeed?{success:true,queue_ids:[101,102],message:'2 trips canceled.'}:{success:false,message:'A selected trip has already departed. Refresh the selection and try again.'})});
  });
  await page.goto(origin+'/staff/queue');await page.locator('#cancelSelectionBtn').click();
  for(const id of ['101','102'])await page.locator(`[name="cancel_queue_ids[]"][value="${id}"]`).check();
  await page.locator('#cancelSelectedSubmit').click();
  await page.waitForSelector('#cancelSelectionFeedback:not([hidden])');
  assert.match(await page.locator('#cancelSelectionFeedback').innerText(),/already departed/);
  assert.equal(await page.locator('#cancelSelectionModal.show').count(),1);
  assert.equal(await page.locator('[name="cancel_queue_ids[]"]:checked').count(),2);
  assert.equal(await page.locator('#cancelSelectedSubmit').isEnabled(),true);
  assert.deepEqual(bodies[0].getAll('queue_ids[]'),['101','102']);
  succeed=true;await page.locator('#cancelSelectedSubmit').click();
  await page.waitForSelector('#cancelSelectionModal.show',{state:'hidden'});
  assert.match(await page.locator('#queueActionFeedback').innerText(),/2 trips canceled/);
  await page.close();
});

test('boarding warnings are readable in-page and canceled trips restore their action controls after errors', async () => {
  variant='fresh';const page=await browser.newPage({viewport:{width:375,height:667}});let cancelRequests=0;
  await page.route('**/staff/queue/update/101/boarding',route=>route.fulfill({status:409,contentType:'application/json',body:JSON.stringify({success:false,variant:'warning',message:'Another vehicle is ahead. Automatic boarding is scheduled for 1:35 PM. <b>Text only</b>'})}));
  await page.route('**/staff/queue/update/101/canceled',async route=>{
    cancelRequests++;await route.fulfill({status:409,contentType:'application/json',body:JSON.stringify({success:false,message:'This trip has already departed. Refresh the queue.'})});
  });
  await page.goto(origin+'/staff/queue');
  const button=page.locator('[data-action="update-status"][data-id="101"]');await button.click();
  await page.waitForSelector('#queueActionFeedback:not([hidden])');
  assert.match(await page.locator('#queueActionFeedback').innerText(),/1:35 PM/);
  assert.equal(await page.locator('#queueActionFeedback b').count(),0);
  assert.equal(await page.locator('#queueActionFeedback').evaluate(el=>getComputedStyle(el).position),'static');
  assert.equal(await button.isEnabled(),true);assert.match(await button.innerText(),/Start boarding now/);
  await page.locator('[data-action="open-cancel-modal"][data-id="101"]').click();await page.locator('#confirmCancelTripBtn').click();
  await page.waitForSelector('#cancelTripFeedback:not([hidden])');
  assert.match(await page.locator('#cancelTripFeedback').innerText(),/already departed/);
  assert.equal(await page.locator('#confirmCancelTripBtn').isEnabled(),true);
  assert.equal(cancelRequests,1);
  const directory=path.join(__dirname,'artifacts');await page.screenshot({path:path.join(directory,'queue-cancellation-warning-mobile.png')});
  await page.close();
});

test('dispatcher clocks, forms and schedule labels use AM/PM while administrators retain 24-hour time', async () => {
  const page=await browser.newPage();
  for(const name of ['staff-queue','staff-schedules','staff-rules']) {
    await page.route('**/time-fixture',route=>route.fulfill({contentType:'text/html',body:queuePage('fresh').replace(/<script\b[^>]*>[\s\S]*?<\/script>/g,'')}));
    if(name==='staff-queue')await page.goto(origin+'/staff/queue');
    else {await page.unroute('**/time-fixture');await page.route('**/time-fixture',route=>route.fulfill({contentType:'text/html',body:fixture(name)}));await page.goto(origin+'/time-fixture');}
    assert.doesNotMatch(await page.locator('body').innerText(),/HH:MM/);
    assert.match(await page.locator('.operations-header-clock').first().innerText(),/AM|PM/);
  }
  await page.goto(origin+'/fixtures/staff-rule-edit');
  assert.equal(await page.locator('#time_from').inputValue(),'5:00 AM');
  assert.equal(await page.locator('#time_to').inputValue(),'5:00 PM');
  await page.locator('#time_from').fill('12:00 PM');await page.locator('#time_to').fill('11:59 PM');
  await page.locator('#wait_minutes').fill('25');
  assert.equal(await page.locator('#departureRuleForm').evaluate(form=>form.checkValidity()),true);
  await page.locator('#time_from').fill('13:00 PM');
  assert.equal(await page.locator('#time_from').evaluate(input=>input.checkValidity()),false);
  for(const name of ['admin-schedules','admin-rules']) {
    await page.unroute('**/time-fixture');await page.route('**/time-fixture',route=>route.fulfill({contentType:'text/html',body:fixture(name)}));await page.goto(origin+'/time-fixture');
    assert.match(await page.locator('body').innerText(),/HH:MM/);
    assert.doesNotMatch(await page.locator('.operations-header-clock').first().innerText(),/AM|PM/);
  }
  await page.close();
});

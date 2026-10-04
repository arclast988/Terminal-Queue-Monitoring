'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const { chromium } = require('playwright');
const { root, assetContentType, phpBinary } = require('./harness.cjs');
let browser, server, origin, variant = '', roundRequests = [];
function fixture(name = 'staff-queue', state = '') {
  return execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme', state], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
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
    await page.locator('[data-clock-field="time_from"] [data-clock-toggle]').click();
    assert.equal(await page.locator('#time_fromPicker').isVisible(),true);
    const bounds=await page.locator('#time_fromPicker').evaluate(el=>({left:el.getBoundingClientRect().left,right:el.getBoundingClientRect().right}));
    assert.ok(bounds.left>=0 && bounds.right<=width);
    await page.locator('#time_fromHour').selectOption('6');await page.locator('#time_fromMinute').selectOption('15');await page.locator('#time_fromPeriod').selectOption('AM');
    await page.locator('#time_fromPicker [data-clock-set]').click();assert.equal(await page.locator('#time_from').inputValue(),'6:15 AM');
    assert.equal(await page.locator('#time_from').isEditable(),false);
    if(width===375) {await page.locator('[data-clock-field="time_to"] [data-clock-toggle]').click();await page.screenshot({path:path.join(directory,'dispatcher-time-picker-mobile.png')});}
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

test('compact route filters and Round dialog fit phones, landscape and desktop screens', async () => {
  variant = 'fresh';
  for (const {width,height} of [{width:320,height:568},{width:360,height:640},{width:375,height:667},{width:414,height:736},{width:667,height:375},{width:768,height:667},{width:1280,height:800}]) {
    const page = await browser.newPage({viewport:{width,height}});
    await page.goto(origin + '/staff/queue');
    assert.equal(await page.locator('[data-dispatch-round]:visible').count(), 0);
    assert.equal(await page.locator('#queueRouteControls [data-dispatch-round]').count(),0);
    assert.ok(await page.locator('#queueRouteControls').evaluate(el=>el.getBoundingClientRect().height<160));
    assert.equal(await page.locator('#queueRouteControls #cancelSelectionBtn').count(),1);
    assert.equal(await page.locator('#queueRouteControls #queueRoundBtn').count(),1);
    assert.doesNotMatch(await page.locator('#cancelSelectionBtn').getAttribute('class'),/btn-modern-danger/);
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1));
    const buttons=await page.locator('.queue-selection-actions').evaluate(el=>{
      const select=el.querySelector('#cancelSelectionBtn').getBoundingClientRect(),round=el.querySelector('#queueRoundBtn').getBoundingClientRect();
      return {selectBottom:select.bottom,roundTop:round.top,roundBottom:round.bottom,roundHeight:round.height};
    });
    assert.ok(buttons.roundTop<buttons.selectBottom && buttons.roundBottom>buttons.selectBottom-1);
    assert.ok(buttons.roundHeight>=33 && buttons.roundHeight<42);
    const directory = path.join(__dirname, 'artifacts'); fs.mkdirSync(directory, {recursive:true});
    if(width===375 || width===1280)await page.screenshot({path:path.join(directory, `queue-route-controls-${width}.png`)});
    await page.locator('[data-queue-route="1|BATO"]').click();
    await page.locator('#queueRoundBtn').click();
    await page.waitForSelector('#queueRoundModal.show');
    assert.equal(await page.locator('#queueRoundRouteFilter').inputValue(),'1|BATO');
    assert.equal(await page.locator('[data-dispatch-round]:visible').count(), 1);
    assert.match(await page.locator('[data-route-id="2"] option:checked').innerText(), /Round 2 · 25 min/);
    await page.locator('#queueRoundRouteFilter').selectOption('all');
    assert.equal(await page.locator('[data-dispatch-round]:visible').count(), 2);
    assert.equal(await page.locator('#queueRoundModal .modal-header').evaluate(el=>getComputedStyle(el).backgroundColor),'rgb(21, 128, 61)');
    const bounds=await page.locator('#queueRoundModal').evaluate(el=>{
      const header=el.querySelector('.modal-header').getBoundingClientRect(),body=el.querySelector('.modal-body').getBoundingClientRect(),footer=el.querySelector('.modal-footer').getBoundingClientRect();
      return {headerTop:header.top,headerBottom:header.bottom,bodyTop:body.top,bodyBottom:body.bottom,footerTop:footer.top,footerBottom:footer.bottom};
    });
    assert.ok(bounds.headerTop>=0 && bounds.bodyTop>=bounds.headerBottom-1);
    assert.ok(bounds.bodyBottom<=bounds.footerTop+1 && bounds.footerBottom<=height);
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
    if(width===375 || width===1280)await page.screenshot({path:path.join(directory, `queue-round-modal-${width}.png`)});
    await page.locator('#queueRoundModal button').filter({hasText:'Done'}).click();
    await page.waitForSelector('#queueRoundModal.show',{state:'hidden'});
    assert.equal(await page.locator('[data-queue-route="1|BATO"]').getAttribute('aria-pressed'),'true');
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
  await page.locator('#queueRoundBtn').click();
  await page.locator('[data-dispatch-round][data-route-id="2"]').selectOption('3');
  await page.waitForFunction(() => document.getElementById('queueRoundFeedback').textContent.includes('Round updated'));
  const request = roundRequests.find(r => r.path.endsWith('/round'));
  assert.equal(request.body.get('route_id'), '2'); assert.equal(request.body.get('round_number'), '3');
  assert.equal(request.body.get('csrf_fixture'), 'unchanged');
  await page.evaluate(() => QueueSync.refresh(true)); await page.waitForTimeout(650);
  assert.equal(await page.locator('[data-queue-route="1|BATO"]').getAttribute('aria-pressed'), 'true');
  assert.equal(await page.locator('#queue-list .q-card:not(.d-none)').count(), 0);
  assert.equal(await page.locator('#queueRoundModal.show').count(),1);
  assert.equal(await page.locator('#queueRoundRouteFilter').inputValue(),'1|BATO');
  await page.locator('#queueRoundModal .btn-close').click();
  await page.locator('[data-bs-target="#addToQueueModal"]').click();
  assert.equal(await page.locator('#addQueueRouteFilter').inputValue(), '1|BATO');
  assert.equal(await page.locator('.vehicle-select-item:not(.d-none)').count(), 1);
  await page.close();
});

test('Round dialog restores the active choice and releases controls after a failed update', async()=>{
  variant='fresh';const page=await browser.newPage({viewport:{width:320,height:568}});
  await page.route('**/staff/queue/round',route=>route.fulfill({status:409,contentType:'application/json',body:JSON.stringify({success:false,message:'This round is no longer available. Review the departure rules.'})}));
  await page.goto(origin+'/staff/queue');await page.locator('#queueRoundBtn').click();
  const select=page.locator('[data-dispatch-round][data-route-id="2"]');
  await select.selectOption('3');await page.waitForSelector('#queueRoundFeedback:not([hidden])');
  assert.match(await page.locator('#queueRoundFeedback').innerText(),/no longer available/);
  assert.equal(await select.inputValue(),'2');
  assert.equal(await page.locator('#queueRoundModal.show').count(),1);
  assert.equal(await page.locator('[data-dispatch-round]:disabled').count(),0);
  await page.close();
});

test('switching to a forty-minute round refreshes the overdue boarding countdown immediately', async()=>{
  variant='round-overdue';const page=await browser.newPage({viewport:{width:375,height:667}});
  await page.route('**/staff/queue/round',async route=>{
    variant='round-extended';await route.fulfill({status:200,contentType:'application/json',body:JSON.stringify({success:true,csrf:'unchanged'})});
  });
  await page.goto(origin+'/staff/queue');
  await page.waitForFunction(()=>document.querySelector('.countdown-timer').textContent.includes('overdue'));
  await page.locator('#queueRoundBtn').click();
  await page.locator('[data-dispatch-round][data-route-id="1"]').selectOption('2');
  await page.waitForFunction(()=>/^[^]*\b(9|10)m/.test(document.querySelector('.countdown-timer').textContent) && !document.querySelector('.countdown-timer').textContent.includes('overdue'));
  assert.match(await page.locator('#queue-list .q-card').first().innerText(),/Round 2/i);
  await page.close();
});

test('queue route dropdowns support typing and clearing on desktop and small screens',async()=>{
  variant='many';
  for(const {width,height} of [{width:320,height:568},{width:375,height:667},{width:667,height:375},{width:1280,height:800}]) {
    const page=await browser.newPage({viewport:{width,height}});const errors=[];page.on('pageerror',error=>errors.push(error.message));
    await page.goto(origin+'/staff/queue');await page.addScriptTag({url:origin+'/assets/js/autocomplete-search.js'});
    for(const [button,modal,select] of [['#manageQueueBtn','#manageQueueModal','#queueOrderGroupSelect'],['#cancelSelectionBtn','#cancelSelectionModal','#cancelRouteFilter'],['#queueRoundBtn','#queueRoundModal','#queueRoundRouteFilter'],['[data-bs-target="#addToQueueModal"]','#addToQueueModal','#addQueueRouteFilter']]) {
      await page.evaluate(selector=>{const el=document.querySelector(selector);delete el.dataset.testShown;el.addEventListener('shown.bs.modal',()=>{el.dataset.testShown='yes';},{once:true});},modal);
      await page.locator(button).click();await page.waitForFunction(selector=>document.querySelector(selector).dataset.testShown==='yes',modal);
      const wrapper=page.locator('.autocomplete-wrapper').filter({has:page.locator(select)});
      const input=wrapper.locator('input[type="text"]');await input.fill('bato');
      await page.waitForFunction(id=>document.querySelector(id).closest('.autocomplete-wrapper').querySelectorAll('.autocomplete-item').length===1,select);
      await wrapper.locator('.autocomplete-item').filter({hasText:'BATO'}).click();
      assert.match(await input.inputValue(),/BATO/);
      if(select==='#queueOrderGroupSelect')assert.match(await page.locator('#manageQueueModal .queue-order-panel:not(.d-none)').innerText(),/BATO/);
      if(select==='#cancelRouteFilter')assert.equal(await page.locator('.queue-cancel-item:not([hidden])').count(),5);
      if(select==='#queueRoundRouteFilter')assert.equal(await page.locator('[data-dispatch-round]:visible').count(),1);
      await wrapper.locator('.autocomplete-clear-btn').click();
      if(select==='#queueOrderGroupSelect')assert.equal(await page.locator('#manageQueueModal .queue-order-panel:not(.d-none)').count(),1);
      if(select==='#cancelRouteFilter')assert.equal(await page.locator('.queue-cancel-item:not([hidden])').count(),10);
      if(select==='#queueRoundRouteFilter')assert.equal(await page.locator('[data-dispatch-round]:visible').count(),2);
      await input.fill('bato');await page.waitForFunction(id=>document.querySelector(id).closest('.autocomplete-wrapper').querySelectorAll('.autocomplete-item').length===1,select);
      await wrapper.locator('.autocomplete-item').filter({hasText:'BATO'}).waitFor();
      const clearBounds=await wrapper.evaluate(el=>{const input=el.querySelector('input[type="text"]').getBoundingClientRect(),clear=el.querySelector('.autocomplete-clear-btn').getBoundingClientRect();return {inputTop:input.top,inputBottom:input.bottom,clearTop:clear.top,clearBottom:clear.bottom};});
      assert.ok(clearBounds.clearTop>=clearBounds.inputTop && clearBounds.clearBottom<=clearBounds.inputBottom);
      if(select==='#addQueueRouteFilter') {
        await page.evaluate(()=>QueueSync.refresh(true));
        await page.waitForResponse(response=>response.url().endsWith('/staff/queue') && response.request().method()==='GET');
        assert.equal(await input.inputValue(),'bato');
      }
      assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
      const bounds=await wrapper.evaluate(el=>({left:el.getBoundingClientRect().left,right:el.getBoundingClientRect().right}));
      assert.ok(bounds.left>=0 && bounds.right<=width);
      if(width===375 && modal==='#manageQueueModal')await page.screenshot({path:path.join(__dirname,'artifacts','manage-queue-search-mobile.png')});
      await page.locator(modal+' .btn-close').click();await page.waitForSelector(modal+'.show',{state:'hidden'});
    }
    assert.deepEqual(errors,[]);await page.close();
  }
});

test('cancel selection refresh loads directly, keeps choices and recovers from failures',async()=>{
  variant='many';const page=await browser.newPage({viewport:{width:375,height:667}});let calls=0,fail=false;
  await page.route('**/staff/queue/cancel-selection',async route=>{
    calls++;await new Promise(resolve=>setTimeout(resolve,200));
    const html='<label class="queue-cancel-item" data-cancel-route="1|Ormoc City"><input name="cancel_queue_ids[]" type="checkbox" value="101"><span>TRIP-1 Updated driver</span></label>';
    await route.fulfill({status:fail?503:200,contentType:'application/json',body:JSON.stringify(fail?{success:false,message:'Please try refreshing again.'}:{success:true,html})});
  });
  await page.goto(origin+'/staff/queue');await page.locator('#cancelSelectionBtn').click();
  for(const id of ['101','102'])await page.locator(`[name="cancel_queue_ids[]"][value="${id}"]`).check();
  await page.locator('#cancelVehicleSearch').fill('trip-1');
  await page.locator('#refreshCancelSelection').click();
  assert.equal(await page.locator('#refreshCancelSelection').innerText(),'Refreshing…');
  assert.equal(await page.locator('#refreshCancelSelection').isDisabled(),true);
  assert.equal(await page.locator('#cancelSelectedSubmit').isDisabled(),true);
  await page.waitForFunction(()=>document.getElementById('cancelSelectionFeedback').textContent.includes('Selection refreshed'));
  assert.equal(await page.locator('[name="cancel_queue_ids[]"]:checked').count(),1);
  assert.match(await page.locator('#cancelSelectionFeedback').innerText(),/no longer active/);
  assert.match(await page.locator('#cancelSelectionList').innerText(),/Updated driver/);
  assert.equal(await page.locator('#cancelVehicleSearch').inputValue(),'trip-1');
  assert.equal(await page.locator('.queue-cancel-item:not([hidden])').count(),1);
  assert.equal(await page.locator('#cancelSelectedSubmit').isEnabled(),true);
  fail=true;await page.locator('#refreshCancelSelection').click();
  await page.waitForFunction(()=>document.getElementById('cancelSelectionFeedback').textContent.includes('Could not refresh'));
  assert.equal(await page.locator('[name="cancel_queue_ids[]"]:checked').count(),1);
  assert.equal(await page.locator('#refreshCancelSelection').isEnabled(),true);
  assert.equal(await page.locator('#cancelSelectedSubmit').isEnabled(),true);
  assert.equal(calls,2);assert.equal(await page.locator('#cancelSelectionList').getAttribute('aria-busy'),null);
  await page.close();
});

test('cancel vehicle search combines with routes and selects only matching trips on phones and desktop',async()=>{
  variant='search-many';
  for(const [width,height] of [[320,568],[375,667],[667,375],[1280,800]]) {
    const page=await browser.newPage({viewport:{width,height}}),errors=[];
    page.on('pageerror',error=>errors.push(error.message));
    await page.goto(origin+'/staff/queue');await page.locator('#cancelSelectionBtn').click();
    await page.waitForSelector('#cancelSelectionModal.show');
    const search=page.locator('#cancelVehicleSearch'),shown=page.locator('.queue-cancel-item:not([hidden])');
    assert.equal(await shown.count(),10);
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
    if(width===375 || width===1280)await page.screenshot({path:path.join(__dirname,'artifacts','cancel-vehicle-search-'+width+'.png')});
    for(const [query,count] of [['VAN',5],['operator 7',1],['Driver 2',1]]) {
      await search.fill(query);assert.equal(await shown.count(),count);
    }
    await search.fill('trip-4');assert.equal(await shown.count(),1);
    await page.locator('#cancelSelectVisible').check();
    assert.deepEqual(await page.locator('[name="cancel_queue_ids[]"]:checked').evaluateAll(boxes=>boxes.map(box=>box.value)),['104']);
    await page.locator('#cancelRouteFilter').selectOption('1|BATO');
    assert.equal(await shown.count(),0);
    assert.equal(await page.locator('#cancelSelectVisible').isDisabled(),true);
    assert.match(await page.locator('#cancelSelectionEmpty').innerText(),/No trips match your search for this route/);
    assert.equal(await page.locator('[name="cancel_queue_ids[]"][value="104"]').isChecked(),true);
    await page.locator('#clearCancelVehicleSearch').click();
    assert.equal(await search.inputValue(),'');assert.equal(await shown.count(),5);
    await page.locator('#cancelSelectVisible').check();
    assert.deepEqual(await page.locator('[name="cancel_queue_ids[]"]:checked').evaluateAll(boxes=>boxes.map(box=>box.value)),['104','106','107','108','109','110']);
    await page.locator('#cancelRouteFilter').selectOption('all');
    assert.equal(await shown.count(),10);
    assert.match(await page.locator('#cancelSelectionCount').innerText(),/6 trips selected/);
    assert.equal(await page.locator('#cancelSelectVisible').evaluate(box=>box.indeterminate),true);
    assert.deepEqual(errors,[]);await page.close();
  }
});

test('Round dialog never presents the fallback interval as a configured departure rule',async()=>{
  variant='unconfigured-round';const page=await browser.newPage({viewport:{width:375,height:667}});
  await page.goto(origin+'/staff/queue');await page.locator('#queueRoundBtn').click();
  const select=page.locator('[data-dispatch-round][data-route-id="2"]');
  assert.equal(await select.locator('option[value="2"]').count(),0);
  assert.equal(await select.inputValue(),'');
  assert.match(await select.locator('option:checked').innerText(),/Choose an available round/);
  assert.doesNotMatch(await page.locator('[data-round-route="1|BATO"]').innerText(),/30 min/);
  assert.match(await page.locator('[data-round-route="1|BATO"]').innerText(),/Choose a round that is active now/);
  await page.close();
});

test('expired rounds are absent and routes with no active hours stay disabled after another route changes',async()=>{
  variant='expired-round';roundRequests=[];
  const page=await browser.newPage({viewport:{width:375,height:667}});
  await page.goto(origin+'/staff/queue');await page.locator('#queueRoundBtn').click();
  const active=page.locator('[data-dispatch-round][data-route-id="1"]');
  const unavailable=page.locator('[data-dispatch-round][data-route-id="2"]');
  assert.deepEqual(await active.locator('option').evaluateAll(options=>options.map(option=>option.value)),['','2','3']);
  assert.match(await active.locator('option:checked').innerText(),/Choose an available round/);
  assert.equal(await unavailable.isDisabled(),true);
  assert.match(await unavailable.locator('option:checked').innerText(),/No rounds available now/);
  await active.selectOption('2');
  await page.waitForFunction(()=>document.getElementById('queueRoundFeedback').textContent.includes('Round updated'));
  assert.equal(roundRequests.find(request=>request.path.endsWith('/round')).body.get('round_number'),'2');
  assert.equal(await unavailable.isDisabled(),true);
  assert.equal(await active.isEnabled(),true);
  await page.close();
});

test('guest Active now badge moves to the dispatcher-selected round in the open mobile dialog',async()=>{
  const page=await browser.newPage({viewport:{width:375,height:667}}),errors=[];page.on('pageerror',error=>errors.push(error.message));
  let selected=1;
  await page.route('**/guest-rounds',route=>route.fulfill({contentType:'text/html',body:fixture('guest','guest-rounds')}));
  await page.route('**/status?*',route=>route.fulfill({contentType:'application/json',body:JSON.stringify({active_queue:[],routes:[],sync_token:'round-'+selected,departure_rules:[1,2].map(round=>({id:round,label:'Round schedule',round_number:round,days_label:'Every day',time_range:'12:00 AM – 11:59 PM',wait_minutes:round===1?20:25,interval_label:'Every '+(round===1?20:25)+' min',route_scope:'All Routes',route_destination:null,is_active_now:round===selected,active_destinations:round===selected?['ORMOC']:[]}))})}));
  await page.goto(origin+'/guest-rounds');
  await page.locator('#routeAverageCard').click();
  await page.waitForSelector('#routeAverageModal.is-open');
  assert.equal(await page.locator('#routeAverageList .badge-active-now').count(),1);
  assert.match(await page.locator('[data-rule-id="1"]').innerText(),/Active Now/i);
  assert.doesNotMatch(await page.locator('[data-rule-id="2"]').innerText(),/Active Now/i);
  selected=2;await page.evaluate(()=>QueueSync.refresh(true));
  await page.waitForSelector('[data-rule-id="2"].is-active-rule');
  assert.equal(await page.locator('#routeAverageList .badge-active-now').count(),1);
  assert.doesNotMatch(await page.locator('[data-rule-id="1"]').innerText(),/Active Now/i);
  assert.match(await page.locator('[data-rule-id="2"]').innerText(),/Round 2/i);
  assert.match(await page.locator('[data-rule-id="2"]').innerText(),/Active for ORMOC/i);
  assert.equal(await page.locator('#routeAverageModal.is-open').count(),1);
  assert.ok(await page.locator('.route-average-dialog').evaluate(el=>el.getBoundingClientRect().width<=innerWidth));
  const directory=path.join(__dirname,'artifacts');fs.mkdirSync(directory,{recursive:true});await page.screenshot({path:path.join(directory,'guest-selected-round-mobile.png')});
  assert.deepEqual(errors,[]);await page.close();
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
  async function setClock(field,hour,minute,period) {
    await page.locator(`[data-clock-field="${field}"] [data-clock-toggle]`).click();
    await page.locator('#'+field+'Hour').selectOption(hour);await page.locator('#'+field+'Minute').selectOption(minute);await page.locator('#'+field+'Period').selectOption(period);
    await page.locator('#'+field+'Picker [data-clock-set]').click();
  }
  await setClock('time_from','12','00','PM');await setClock('time_to','11','59','PM');
  await page.locator('#wait_minutes').fill('25');
  assert.equal(await page.locator('#departureRuleForm').evaluate(form=>form.checkValidity()),true);
  await page.locator('#time_from').pressSequentially('sdsadsas111111');
  assert.equal(await page.locator('#time_from').inputValue(),'12:00 PM');
  await setClock('time_from','11','59','PM');
  assert.equal(await page.locator('#departureRuleForm').evaluate(form=>form.checkValidity()),false);
  for(const name of ['admin-schedules','admin-rules']) {
    await page.unroute('**/time-fixture');await page.route('**/time-fixture',route=>route.fulfill({contentType:'text/html',body:fixture(name)}));await page.goto(origin+'/time-fixture');
    assert.match(await page.locator('body').innerText(),/HH:MM/);
    assert.doesNotMatch(await page.locator('.operations-header-clock').first().innerText(),/AM|PM/);
  }
  await page.close();
});

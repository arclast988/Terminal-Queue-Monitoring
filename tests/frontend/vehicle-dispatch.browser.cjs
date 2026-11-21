'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const playwright = require('playwright');
const { root, assetContentType, phpBinary } = require('./harness.cjs');
const engine = process.env.TQ_BROWSER || 'chromium';
let browser, server, origin, queueVariant = 'dispatch-departed', serviceDate = '2026-10-07', refreshes = 0;
const cache = new Map();
function fixture(name, state = 'dispatch-register', role = 'admin') {
  const key = [name,state,role].join(':');
  if (!cache.has(key)) cache.set(key, execFileSync(phpBinary(), [path.join(__dirname,'render-responsive-fixture.php'), name, 'normal', 'theme', state, 'single', role], {encoding:'utf8',maxBuffer:2*1024*1024}));
  let html = cache.get(key);
  if (name === 'staff-queue') html = html.replace('</head>', '<script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script></head>');
  if (name === 'staff-queue') html = html.replace(/serviceDate: '\d{4}-\d{2}-\d{2}'/, `serviceDate: '${serviceDate}'`).replace(/data-service-date="[^"]+"/, `data-service-date="${serviceDate}"`);
  return html.replace('</head>', '<script>window.QueueWS={_isLoaded:true,init:function(){},updateConfig:function(){},isConnected:function(){return false}};</script></head>');
}
test.before(async () => {
  browser = await playwright[engine].launch({headless:true,...(engine === 'chromium' && process.env.TQ_BROWSER_CHANNEL ? {channel:process.env.TQ_BROWSER_CHANNEL} : {})});
  server = http.createServer((req,res) => {
    const url = new URL(req.url,'http://fixture');
    if (url.pathname === '/fixture.svg') { res.setHeader('Content-Type','image/svg+xml'); return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#475569"/></svg>'); }
    if (url.pathname === '/vehicles' || url.pathname === '/vehicle-edit') {
      const state = ['dispatch-order-difference','route-order-change','register-order'].includes(url.searchParams.get('state')) ? url.searchParams.get('state') : 'dispatch-register';
      res.setHeader('Content-Type','text/html'); return res.end(fixture(url.pathname === '/vehicles' ? 'admin-vehicles' : 'admin-vehicle-edit',state,url.searchParams.get('role') || 'admin'));
    }
    if (url.pathname === '/staff/queue') { refreshes++; res.setHeader('Content-Type','text/html'); return res.end(fixture('staff-queue',queueVariant,'staff')); }
    if (url.pathname === '/api/queue-status') { res.setHeader('Content-Type','application/json'); return res.end(JSON.stringify({success:true,queue:[],sync_token:'unchanged',service_date:serviceDate})); }
    if (url.pathname === '/staff/queue/tick') { res.setHeader('Content-Type','application/json'); return res.end(JSON.stringify({success:true,csrf:'unchanged',service_date:serviceDate})); }
    const file = path.resolve(root,'public','.'+url.pathname);
    if (!file.startsWith(path.join(root,'public')+path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.statusCode=404; return res.end(); }
    res.setHeader('Content-Type',assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve)); origin='http://127.0.0.1:'+server.address().port;
});
test.after(async()=>{await browser?.close(); server?.closeAllConnections(); await new Promise(resolve=>server?.close(resolve));});
async function fits(page, selector, width) {
  const result = await page.locator(selector).evaluate(el=>{const b=el.getBoundingClientRect();return {left:b.left,right:b.right,scroll:el.scrollWidth,client:el.clientWidth};});
  assert.ok(result.left>=-1 && result.right<=width+1, JSON.stringify({selector,width,result}));
  assert.ok(result.scroll<=result.client+1, JSON.stringify({selector,width,result}));
}
async function screenshot(page, name, fullPage = true) {
  if (engine !== 'chromium') return;
  const dir=path.join(__dirname,'artifacts'); fs.mkdirSync(dir,{recursive:true});
  await page.screenshot({path:path.join(dir,`vehicle-dispatch-${name}.png`),fullPage});
}

test('vehicle register route filtering combines with type, status, and search at phone, tablet, and desktop sizes', async()=>{
  for (const width of [320,375,768,1280]) {
    const page=await browser.newPage({viewport:{width,height:900}}); const errors=[]; page.on('pageerror',e=>errors.push(e.message));
    await page.goto(origin+'/vehicles');
    await page.locator('#vehicle-route-filter').selectOption('1|ORMOC',{force:true});
    assert.equal(await page.locator('#vehicles-table tbody tr[data-type]:not(.vehicle-row-hidden)').count(),2);
    await page.locator('#filter-btn-van').click();
    assert.equal(await page.locator('#vehicles-table tbody tr[data-type]:not(.vehicle-row-hidden)').count(),1);
    assert.match(await page.locator('#filter-label').innerText(),/ORMOC/);
    await page.locator('#vehicle-search').fill('does-not-exist');
    assert.equal(await page.locator('#vehicles-table .no-filter-results').count(),1);
    await page.locator('#vehicle-search').fill('ZZZ');
    assert.equal(await page.locator('#vehicles-table tbody tr[data-type]:not(.vehicle-row-hidden)').count(),1);
    assert.equal(new URL(page.url()).searchParams.get('route'),'1|ORMOC');
    await page.reload();
    assert.equal(await page.locator('#vehicle-route-filter').inputValue(),'1|ORMOC');
    await fits(page,'.vehicle-route-filter',width);
    assert.equal(await page.locator('.vehicle-start-order').count(),0);
    assert.equal((await page.locator('tr[data-vehicle-id="201"] .row-number').innerText()).trim(),'1');
    assert.equal((await page.locator('tr[data-vehicle-id="202"] .row-number').innerText()).trim(),'2');
    assert.deepEqual((await page.locator('[data-route-heading]:visible').allTextContents()).map(text=>text.trim()),['PALOMPON → ORMOC']);
    if (width===375) await screenshot(page,'route-filter-phone');
    assert.deepEqual(errors,[]); await page.close();
  }
});

test('register and edit use one route order control with usable layouts for both admin roles', async()=>{
  for (const role of ['admin','super_admin']) for (const width of [320,375,768,1280]) {
    const page=await browser.newPage({viewport:{width,height:900}}); const errors=[]; page.on('pageerror',e=>errors.push(e.message));
    await page.goto(origin+'/vehicles?role='+role);
    await page.locator('[data-bs-target="#registerVehicleModal"]').click();
    await page.locator('#registerVehicleModal').waitFor({state:'visible'});
    assert.equal(await page.locator('label[for="dispatch_order"]').textContent(),'Route order (#)');
    await page.locator('#type').selectOption('jeepney',{force:true});
    await page.locator('#route_id').selectOption('1',{force:true});
    await page.locator('#dispatch_order').fill('2');
    assert.equal(await page.locator('#registerVehicleModal form').evaluate(form=>new FormData(form).get('dispatch_order')),'2');
    assert.equal(await page.locator('#dispatch_order').evaluate(input=>input.checkValidity()),true);
    await page.locator('#dispatch_order').fill('0');
    assert.equal(await page.locator('#dispatch_order').evaluate(input=>input.checkValidity()),false);
    await page.locator('#dispatch_order').fill('2');
    await fits(page,'#registerVehicleModal .modal-content',width);
    await fits(page,'#dispatch_order',width);
    if (role==='admin' && width===375) await screenshot(page,'register-phone');
    await page.goto(origin+'/vehicle-edit?role='+role);
    assert.equal(await page.locator('#dispatch_order').inputValue(),'2');
    assert.equal(await page.locator('#current_dispatch_position').count(),0);
    assert.equal(await page.locator('.vehicle-dispatch-order-field input[type="number"]').count(),1);
    assert.equal(await page.locator('#vehicle-current-order-note').isVisible(),false);
    assert.equal(await page.locator('#sync_current_order').inputValue(),'0');
    await page.locator('#dispatch_order').fill('1');
    assert.equal(await page.locator('#dispatch_order').evaluate(input=>new FormData(input.form).get('dispatch_order')),'1');
    await fits(page,'#dispatch_order',width);
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth+1));
    if (role==='admin' && width===375) await screenshot(page,'edit-phone');
    if (role==='admin' && width===1280) await screenshot(page,'edit-desktop');
    assert.deepEqual(errors,[]); await page.close();
  }
});

test('route order stays within the selected destination count for registration and editing', async()=>{
  const page=await browser.newPage({viewport:{width:375,height:900}});
  try {
    await page.goto(origin+'/vehicles');
    await page.locator('[data-bs-target="#registerVehicleModal"]').click();
    await page.locator('#registerVehicleModal').waitFor({state:'visible'});
    await page.locator('#type').selectOption('jeepney',{force:true});
    await page.locator('#route_id').selectOption('1',{force:true});
    const order=page.locator('#dispatch_order');
    assert.equal(await order.getAttribute('max'),'3','two existing vehicles allow registration in slot three');
    await order.fill('5'); assert.equal(await order.inputValue(),'3');
    await order.fill(''); assert.equal(await order.inputValue(),'');
    await page.goto(origin+'/vehicle-edit');
    assert.equal(await order.getAttribute('max'),'2','the edited vehicle is already included');
    await order.fill('3'); assert.equal(await order.inputValue(),'2');
    await order.press('ArrowUp'); assert.equal(await order.inputValue(),'2');
    await page.goto(origin+'/vehicle-edit?state=route-order-change');
    assert.equal(await order.getAttribute('max'),'9');
    await page.locator('#route_id').selectOption('3',{force:true});
    assert.equal(await order.getAttribute('max'),'1');
    assert.equal(await order.inputValue(),'1');
    await page.locator('#route_id').selectOption('5',{force:true});
    assert.equal(await order.getAttribute('max'),'3','unavailable vehicles do not extend the active line');
    await order.fill('4'); assert.equal(await order.inputValue(),'3');
    await order.fill(''); assert.equal(await order.inputValue(),'');
    await page.locator('#type').selectOption('van',{force:true});
    assert.equal(await order.getAttribute('max'),'3','sibling vehicle types share one destination line');
    assert.equal(await order.inputValue(),'');
  } finally { await page.close(); }
});

test('one route order input preserves the saved start and shows a differing current position as text', async()=>{
  for (const role of ['admin','super_admin']) for (const width of [375,1280]) {
    const page=await browser.newPage({viewport:{width,height:900}}); const errors=[]; page.on('pageerror',e=>errors.push(e.message));
    const params='?role='+role+'&state=dispatch-order-difference';
    await page.goto(origin+'/vehicles'+params);
    assert.equal((await page.locator('tr[data-vehicle-id="209"] .row-number').innerText()).trim(),'3');
    await page.goto(origin+'/vehicle-edit'+params);
    assert.equal(await page.locator('#current_dispatch_position').count(),0);
    assert.equal(await page.locator('#vehicle-current-order-note').isVisible(),true);
    assert.match(await page.locator('#vehicle-current-order-note').innerText(),/Today's current position: 3/);
    assert.equal(await page.locator('#dispatch_order').inputValue(),'9');
    assert.equal(await page.locator('#sync_current_order').inputValue(),'0');
    assert.match(await page.locator('#dispatch-order-help').innerText(),/today's lineup and the daily starting position/);
    assert.match(await page.locator('#dispatch-order-help').innerText(),/Leave blank to put this vehicle last/);
    await page.locator('#dispatch_order').fill('4');
    assert.deepEqual(await page.locator('#editVehicleForm').evaluate(form=>({order:new FormData(form).get('dispatch_order'),hasCurrent:new FormData(form).has('current_dispatch_position'),sync:new FormData(form).get('sync_current_order')})),{order:'4',hasCurrent:false,sync:'1'});
    assert.equal(await page.locator('#vehicle-current-order-note').isVisible(),false);
    await fits(page,'#dispatch_order',width);
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth+1));
    if (role==='admin') await screenshot(page,'order-difference-'+width);
    assert.deepEqual(errors,[]); await page.close();
  }
});

test('the single route order input suggests one or last for a destination and stays editable', async()=>{
  for (const [role,width] of [['admin',375],['super_admin',1280]]) {
    const page=await browser.newPage({viewport:{width,height:900}}); const errors=[]; page.on('pageerror',e=>errors.push(e.message));
    await page.goto(origin+'/vehicle-edit?state=route-order-change&role='+role);
    const type=page.locator('#type'), route=page.locator('#route_id');
    assert.equal(await page.locator('#status, select[name="status"]').count(),0);
    assert.equal(await page.locator('#editVehicleForm').evaluate(form=>new FormData(form).has('status')),false);
    const order=page.locator('#dispatch_order');
    async function orders(position,sync) {
      assert.equal(await order.inputValue(),position);
      assert.equal(await page.locator('#current_dispatch_position').count(),0);
      if (sync !== undefined) assert.equal(await page.locator('#sync_current_order').inputValue(),sync);
    }
    await orders('9','0');
    // Changing vehicle type within the saved line preserves today's rotation and the daily starting order.
    await type.selectOption('van',{force:true});
    assert.equal(await route.inputValue(),'2');
    await orders('9','0');

    await route.selectOption('4',{force:true}); // Empty destination.
    await orders('1','1');
    await type.selectOption('jeepney',{force:true});
    assert.equal(await route.inputValue(),'3');
    await orders('1','1');

    await route.selectOption('5',{force:true}); // Two active vehicles across both types; unavailable vehicles are not in today's line.
    await orders('3','1');
    await order.fill('2');
    await orders('2','1');
    await type.selectOption('van',{force:true});
    assert.equal(await route.inputValue(),'6');
    await orders('2','1');
    await order.fill('1');
    await orders('1','1');

    await route.selectOption('8',{force:true}); // Same destination in another terminal has its own line.
    await orders('2','1');
    await route.selectOption('6',{force:true});
    await orders('3','1');
    await route.selectOption('',{force:true});
    assert.equal(await route.inputValue(),'');
    await route.selectOption('6',{force:true});
    await orders('3','1');
    await route.selectOption('2',{force:true});
    await orders('9','0');
    assert.equal(await page.locator('#vehicle-current-order-note').isVisible(),true);
    await order.fill('');
    await orders('','1');
    await order.fill('4');
    await orders('4','1');
    await page.locator('#editVehicleForm').evaluate(form=>{
      window.fixtureEditPayloads=[];
      form.addEventListener('submit',event=>{
        event.preventDefault();
        const data=new FormData(form);
        window.fixtureEditPayloads.push(Object.fromEntries(['route_id','dispatch_order','sync_current_order'].map(name=>[name,data.get(name)])));
      });
    });
    await page.locator('#editVehicleForm button[type="submit"]').click();
    assert.deepEqual(await page.evaluate(()=>window.fixtureEditPayloads),[{route_id:'2',dispatch_order:'4',sync_current_order:'1'}]);
    await fits(page,'#dispatch_order',width);
    assert.deepEqual(errors,[]); await page.close();
  }
});

test('vehicle availability changes use row actions and explicit confirmation for both admin roles', {timeout:60000}, async()=>{
  for (const role of ['admin','super_admin']) for (const width of [320,375,1366]) {
    const page=await browser.newPage({viewport:{width,height:900},reducedMotion:'reduce'});
    const posts=[];
    await page.route('**/admin/vehicles/status/*',async route=>{
      posts.push({url:new URL(route.request().url()).pathname,method:route.request().method(),body:new URLSearchParams(route.request().postData())});
      await route.fulfill({contentType:'text/html',body:'<!doctype html><title>Saved</title><p>Availability saved</p>'});
    });
    const url=origin+'/vehicles?state=register-order&role='+role;
    await page.goto(url);
    const maintenance=page.locator('tr[data-vehicle-id="201"] [data-action="vehicle-status"]');
    assert.equal(await maintenance.getAttribute('data-next-status'),'maintenance');
    assert.match(await maintenance.innerText(),/Maintenance/);
    await maintenance.click();
    await page.locator('#vehicleStatusConfirmModal.show').waitFor({state:'visible'});
    assert.match(await page.locator('#vehicleStatusConfirmLabel').innerText(),/Maintenance/);
    assert.equal(await page.locator('#vehicleStatusPlate').innerText(),'ZZZ-001');
    assert.match(await page.locator('#vehicleStatusDescription').innerText(),/waiting and boarding trips will be canceled/);
    assert.equal(await page.locator('#vehicleStatusTypeTag').innerText(),'VAN');
    assert.match(await page.locator('#vehicleStatusSubInfo').innerText(),/Driver: .+ · .+→ ORMOC/);
    const maintenanceColor = await page.locator('#vehicleStatusSubmit').evaluate(button=>getComputedStyle(button).backgroundImage.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/)?.slice(1).map(Number));
    assert.ok(maintenanceColor && maintenanceColor[0] > maintenanceColor[1], 'Maintenance confirmation must be red');
    await fits(page,'#vehicleStatusSubmit',width);
    const confirmBox = await page.locator('#vehicleStatusSubmit').boundingBox();
    const cancelBox = await page.locator('#vehicleStatusConfirmModal [data-bs-dismiss="modal"]').boundingBox();
    assert.ok(cancelBox.x + cancelBox.width < confirmBox.x && Math.abs(cancelBox.y - confirmBox.y) <= 1, 'Cancel and Confirm must sit beside each other');
    if (role==='admin') await screenshot(page,'maintenance-confirm-'+width,false);
    assert.equal(posts.length,0,'opening confirmation does not change availability');
    await page.locator('#vehicleStatusConfirmModal [data-bs-dismiss="modal"]').click();
    assert.equal(posts.length,0,'canceling confirmation sends no request');
    await maintenance.click();
    await fits(page,'#vehicleStatusConfirmModal .modal-content',width);
    await page.locator('#vehicleStatusSubmit').click();
    await page.waitForURL('**/admin/vehicles/status/201');
    assert.equal(posts.length,1);
    assert.equal(posts[0].method,'POST');
    assert.equal(posts[0].body.get('status'),'maintenance');
    assert.equal(posts[0].body.get('current_status'),'active');
    assert.equal(posts[0].body.get('csrf_fixture'),'unchanged');

    await page.goto(url);
    await page.locator('#filter-btn-maintenance').click();
    const active=page.locator('tr[data-vehicle-id="206"] [data-action="vehicle-status"]');
    assert.equal(await active.getAttribute('data-next-status'),'active');
    assert.match(await active.innerText(),/Active/);
    await active.focus();
    await page.mouse.move(0,0);
    const activeStyle=()=>active.evaluate(button=>{
      const style=getComputedStyle(button);
      return {background:style.backgroundColor,text:getComputedStyle(button.querySelector('.action-label')).webkitTextFillColor,icon:getComputedStyle(button.querySelector('i')).webkitTextFillColor};
    });
    assert.deepEqual(await activeStyle(),{background:'rgb(220, 252, 231)',text:'rgb(21, 128, 61)',icon:'rgb(21, 128, 61)'},'Focused Set Active keeps the readable pale-green style');
    await active.hover();
    assert.deepEqual(await activeStyle(),{background:'rgb(22, 163, 74)',text:'rgb(255, 255, 255)',icon:'rgb(255, 255, 255)'},'Hovered Set Active uses white text and icon');
    await active.click();
    assert.match(await page.locator('#vehicleStatusConfirmLabel').innerText(),/Active/);
    assert.match(await page.locator('#vehicleStatusDescription').innerText(),/Canceled trips will stay canceled/);
    assert.equal(await page.locator('#vehicleStatusTypeTag').innerText(),'JEEPNEY');
    const activeColor = await page.locator('#vehicleStatusSubmit').evaluate(button=>getComputedStyle(button).backgroundImage.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/)?.slice(1).map(Number));
    assert.ok(activeColor && activeColor[1] > activeColor[0], 'Set Active confirmation must be green');
    await fits(page,'#vehicleStatusConfirmModal .modal-content',width);
    if (role==='admin') await screenshot(page,'active-confirm-'+width,false);
    await page.locator('#vehicleStatusSubmit').click();
    await page.waitForURL('**/admin/vehicles/status/206');
    assert.equal(posts.length,2);
    assert.equal(posts[1].body.get('status'),'active');
    assert.equal(posts[1].body.get('current_status'),'maintenance');
    await page.close();
  }
});

test('midnight refresh restores the route starting order and clears departed badges while the queue modal stays open', async()=>{
  for (const width of [320,375,768,1280]) {
    serviceDate='2026-10-07'; queueVariant='dispatch-departed';
    const page=await browser.newPage({viewport:{width,height:900}}); const errors=[]; page.on('pageerror',e=>errors.push(e.message));
    await page.goto(origin+'/staff/queue');
    await page.locator('[data-bs-target="#addToQueueModal"]').click();
    await page.locator('#addToQueueModal').waitFor({state:'visible'});
    assert.deepEqual(await page.locator('.vehicle-select-item').evaluateAll(items=>items.map(i=>i.dataset.vehicleId)),['202','203','201']);
    assert.match(await page.locator('.vehicle-select-item').last().innerText(),/DEPARTED/i);
    await page.evaluate(()=>QueueSync.init({apiUrl:'/api/queue-status',refreshUrl:'/staff/queue',tableSelector:'#queue-list',serviceDate:'2026-10-07',pollInterval:100}));
    const before=refreshes; serviceDate='2026-10-08'; queueVariant='dispatch-reset';
    await page.waitForFunction(()=>document.querySelector('.vehicle-select-item')?.dataset.vehicleId==='201');
    assert.ok(refreshes>before);
    assert.deepEqual(await page.locator('.vehicle-select-item').evaluateAll(items=>items.map(i=>i.dataset.vehicleId)),['201','202','203']);
    assert.doesNotMatch(await page.locator('#vehicleListContainer').innerText(),/DEPARTED/i);
    assert.deepEqual(await page.locator('.dispatch-position-badge').allTextContents(),['Order 1','Order 2','Order 3']);
    assert.equal(await page.locator('#addToQueueModal').isVisible(),true);
    await fits(page,'#addToQueueModal .modal-content',width);
    if (width===375) await screenshot(page,'queue-reset-phone');
    assert.deepEqual(errors,[]); await page.close();
  }
});

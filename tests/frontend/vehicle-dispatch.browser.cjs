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
      res.setHeader('Content-Type','text/html'); return res.end(fixture(url.pathname === '/vehicles' ? 'admin-vehicles' : 'admin-vehicle-edit','dispatch-register',url.searchParams.get('role') || 'admin'));
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
async function screenshot(page, name) {
  if (engine !== 'chromium') return;
  const dir=path.join(__dirname,'artifacts'); fs.mkdirSync(dir,{recursive:true});
  await page.screenshot({path:path.join(dir,`vehicle-dispatch-${name}.png`),fullPage:true});
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

test('register and edit preserve a valid daily starting order with usable layouts for both admin roles', async()=>{
  for (const role of ['admin','super_admin']) for (const width of [320,375,768,1280]) {
    const page=await browser.newPage({viewport:{width,height:900}}); const errors=[]; page.on('pageerror',e=>errors.push(e.message));
    await page.goto(origin+'/vehicles?role='+role);
    await page.locator('[data-bs-target="#registerVehicleModal"]').click();
    await page.locator('#registerVehicleModal').waitFor({state:'visible'});
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
    await page.locator('#dispatch_order').fill('1');
    assert.equal(await page.locator('#dispatch_order').evaluate(input=>new FormData(input.form).get('dispatch_order')),'1');
    await fits(page,'#dispatch_order',width);
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth+1));
    if (role==='admin' && width===375) await screenshot(page,'edit-phone');
    if (role==='admin' && width===1280) await screenshot(page,'edit-desktop');
    assert.deepEqual(errors,[]); await page.close();
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

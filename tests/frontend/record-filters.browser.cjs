'use strict';
const test=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),http=require('node:http');
const {execFileSync}=require('node:child_process');
const playwright=require('playwright');
const {root,assetContentType,phpBinary}=require('./harness.cjs');
const engine=process.env.TQ_BROWSER||'chromium',cache=new Map();
let browser,server,origin;
function fixture(role,query={},today=false) {
  const key=JSON.stringify([role,query,today]);
  if(!cache.has(key)) {
    const name=today?'staff-departures':role==='guest'?'schedules':role==='staff'?'staff-schedules':'admin-schedules';
    const rendered=execFileSync(phpBinary(),[path.join(__dirname,'render-record-filter-fixture.php'),name,'normal','theme','','',role==='super_admin'?role:'','shared-dialogs','',JSON.stringify(query)],{encoding:'utf8',maxBuffer:3*1024*1024});
    cache.set(key,rendered.replace(/<script\b([^>]*)>([\s\S]*?)<\/script>/g,(tag,attrs,code)=>{
      if(/\bsrc\s*=/.test(attrs))return /record-filters|global-loader|queue-sync/.test(attrs)?tag:'';
      return /currentType|currentSharedDestFilter|RecordFilters\.init/.test(code)?tag:'';
    }));
  }
  return cache.get(key);
}
const allRows=[['ORMOC-1','ORMOC','jeepney'],['BATO-2','BATO','van'],['ORMOC-3','ORMOC','van']].map(([plate,destination,type],index)=>({id:index+1,queue_id:index+1,plate_number:plate,destination,vehicle_type:type,origin:'VILLABA',position:index+1,status:'waiting',current_passengers:1,capacity:12,operator_name:'Operator',driver_name:'Driver',estimated_departure:'2026-10-27 12:00:00',estimated_departure_formatted:'12:00 PM',photo_url:'/fixture.svg'}));
test.before(async()=>{
  server=http.createServer((req,res)=>{
    const url=new URL(req.url,'http://fixture'),role=req.headers['x-fixture-role']||'guest';
    if(url.pathname==='/schedules/status') {
      const rows=allRows.filter(row=>!url.searchParams.get('type')||row.vehicle_type===url.searchParams.get('type'));
      res.setHeader('Content-Type','application/json');return res.end(JSON.stringify({success:true,schedules:rows,queue:rows,vehicle_types:[{slug:'jeepney',name:'Jeepney',color:'#1565c0'},{slug:'van',name:'Van',color:'#b71c1c'}],destinations:['ORMOC','BATO'],active_dest_counts:{ORMOC:2,BATO:1},total_active_count:3}));
    }
    if(url.pathname==='/schedules'||url.pathname==='/staff/departures') {res.setHeader('Content-Type','text/html');return res.end(fixture(role,Object.fromEntries(url.searchParams),url.pathname.includes('departures')));}
    if(url.pathname==='/fixture.svg'){res.setHeader('Content-Type','image/svg+xml');return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"/>');}
    const file=path.resolve(root,'public','.'+url.pathname);
    if(!file.startsWith(path.join(root,'public')+path.sep)||!fs.existsSync(file)||fs.statSync(file).isDirectory()){res.statusCode=404;return res.end();}
    res.setHeader('Content-Type',assetContentType(file));res.end(fs.readFileSync(file));
  });
  await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));origin='http://127.0.0.1:'+server.address().port;
  browser=await playwright[engine].launch({headless:true,...(engine==='chromium'&&process.env.TQ_BROWSER_CHANNEL?{channel:process.env.TQ_BROWSER_CHANNEL}:{})});
});
test.after(async()=>{await browser?.close();if(server){server.closeAllConnections();await new Promise(resolve=>server.close(resolve));}});
async function open(role,width,today=false) {
  const context=await browser.newContext({viewport:{width,height:850},extraHTTPHeaders:{'x-fixture-role':role}});
  const page=await context.newPage(),errors=[];let navigations=0;
  page.on('pageerror',error=>errors.push(error.message));
  page.on('request',request=>{if(request.isNavigationRequest()&&request.frame()===page.mainFrame())navigations++;});
  await page.goto(origin+(today?'/staff/departures':'/schedules'));
  await page.waitForFunction(()=>window.RecordFilters&&document.querySelector('form[id$="FilterForm"]').recordFilters);
  await page.evaluate(()=>{window.originalHeader=document.querySelector('#site-header,.guest-header');window.originalFilter=document.querySelector('form[id$="FilterForm"]');window.originalOrigin=performance.timeOrigin;});
  return {page,context,errors,navigations:()=>navigations};
}
async function ready(page,selector) {await page.waitForFunction(selector=>document.querySelector(selector).getAttribute('aria-busy')==='false',selector);}
async function shell(page,navigations) {
  assert.equal(navigations(),1,'Filtering must not navigate the main frame.');
  const state=await page.evaluate(()=>({header:originalHeader===document.querySelector('#site-header,.guest-header'),form:originalFilter===document.querySelector('form[id$="FilterForm"]'),origin:originalOrigin,currentOrigin:performance.timeOrigin}));
  assert.equal(state.header,true,'Filtering must preserve the header element.');
  assert.equal(state.form,true,'Filtering must preserve the filter form element.');
  assert.equal(state.origin,state.currentOrigin,'Filtering must preserve the document time origin.');
}
test('record filters update only schedule results for Guest, Dispatcher, Admin and Super Admin',{timeout:180000},async()=>{
  for(const role of ['guest','staff','admin','super_admin'])for(const width of [375,1280]) {
    const {page,context,errors,navigations}=await open(role,width);
    try {
      const guest=role==='guest',form=page.locator(guest?'#guestScheduleFilterForm':'#sharedFilterForm'),results=guest?'#guestScheduleResults':'#sharedScheduleResults';
      await form.locator('[name="type"]').selectOption('van');
      await form.locator('[name="destination"]').selectOption('BATO');
      await form.locator('button[type="submit"]').click();await ready(page,results);
      assert.equal(new URL(page.url()).searchParams.get('destination'),'BATO');
      assert.deepEqual(await page.locator(results+' tr[data-queue-id]:visible').getAttribute('data-plate').then(value=>[value]),['BATO-2']);
      await page.locator(guest?'#guestScheduleActiveFilters .clear-btn':'#sharedScheduleActiveFilters a').click();await ready(page,results);
      assert.equal(new URL(page.url()).search,'');
      assert.equal(await page.locator(results+' tr[data-queue-id]:visible').count(),3);
      await page.locator((guest?'#routeFilterBar':'#sharedRouteFilterBar')+' [data-dest="ormoc"]').click();await ready(page,results);
      assert.equal(await page.locator(results+' tr[data-queue-id]:visible').count(),2);
      await page.goBack();await ready(page,results);
      assert.equal(await page.locator(results+' tr[data-queue-id]:visible').count(),3);
      await shell(page,navigations);assert.deepEqual(errors,[],role+' '+width);
    } finally {await context.close();}
  }
});
test('record filters keep Today Departures local and its print report synchronized',{timeout:90000},async()=>{
  for(const width of [375,1280]) {
    const {page,context,errors,navigations}=await open('staff',width,true);
    try {
      const form=page.locator('#todayDepartureFilterForm'),results='#todayDepartureResults';
      await form.locator('[name="q"]').fill('BATO');await form.locator('[name="sort_order"]').selectOption('asc');
      await form.locator('button[type="submit"]').click();await ready(page,results);
      assert.match(await page.locator('#today-departures-table tbody').textContent(),/BATO-2/);
      const report=new URL(await page.locator('#todayDepartureReport').getAttribute('href'),origin);
      assert.equal(report.searchParams.get('q'),'BATO');assert.equal(report.searchParams.get('sort_order'),'asc');
      await form.locator('[data-record-filter-link]').click();await ready(page,results);
      await form.locator('[name="sort_order"]').selectOption('asc');await form.locator('button[type="submit"]').click();await ready(page,results);
      await page.locator(results+' .pagination a').click();await ready(page,results);
      assert.equal(new URL(page.url()).searchParams.get('page'),'2');assert.equal(new URL(page.url()).searchParams.get('sort_order'),'asc');
      assert.equal(await page.locator('#today-departures-table tbody tr').count(),1);
      assert.equal(await form.locator('[name="sort_order"]').inputValue(),'asc');
      await shell(page,navigations);assert.deepEqual(errors,[]);
    } finally {await context.close();}
  }
});
test('record filters ignore obsolete responses and recover from failures without reloading',{timeout:60000},async()=>{
  const {page,context,errors,navigations}=await open('admin',1280);
  let release,fail=false;
  const stalled=new Promise(resolve=>{release=resolve;});
  try {
    let first=true;
    await context.route(origin+'/schedules?**',async route=>{
      const query=Object.fromEntries(new URL(route.request().url()).searchParams);
      if(first){first=false;await stalled;}
      await route.fulfill({status:fail?503:200,contentType:'text/html',body:fail?'Unavailable':fixture('admin',query)}).catch(()=>{});
    });
    const form=page.locator('#sharedFilterForm');
    await form.locator('[name="destination"]').selectOption('ORMOC');await form.locator('button[type="submit"]').click();
    await page.locator('#sharedScheduleResults .table-loader-overlay').waitFor({state:'visible'});
    assert.equal(await page.evaluate(()=>GlobalLoader.isRunning()),false,'Filtering must use local feedback without the global progress bar.');
    await page.locator('#sharedRouteFilterBar [data-dest="bato"]').click();await ready(page,'#sharedScheduleResults');
    release();assert.match(await page.locator('#sharedScheduleTableBody').textContent(),/BATO-2/);
    fail=true;await form.locator('[name="destination"]').selectOption('ORMOC');await form.locator('button[type="submit"]').click();await ready(page,'#sharedScheduleResults');
    assert.equal(await page.locator('[role="alert"]').isVisible(),true);
    assert.match(await page.locator('#sharedScheduleTableBody').textContent(),/BATO-2/);
    fail=false;await form.locator('button[type="submit"]').click();await ready(page,'#sharedScheduleResults');
    assert.equal(await page.locator('#sharedScheduleTableBody tr[data-queue-id]:visible').count(),2);
    assert.equal(await page.locator('[role="alert"]').isVisible(),false);
    await shell(page,navigations);assert.deepEqual(errors,[]);
  } finally {release();await context.close();}
});

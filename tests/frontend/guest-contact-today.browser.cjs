'use strict';
const test=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),http=require('node:http');
const {execFileSync}=require('node:child_process');
const {chromium}=require('playwright');
const {root,assetContentType,phpBinary}=require('./harness.cjs');
let browser,server,origin;
function fixture(name,state='') {
  return execFileSync(phpBinary(),[path.join(__dirname,'render-responsive-fixture.php'),name,'normal','theme',state],{encoding:'utf8',maxBuffer:3*1024*1024});
}
test.before(async()=>{
  browser=await chromium.launch({headless:true,...(process.env.TQ_BROWSER_CHANNEL?{channel:process.env.TQ_BROWSER_CHANNEL}:{})});
  server=http.createServer((req,res)=>{
    const url=new URL(req.url,'http://fixture');
    if(url.pathname.startsWith('/fixtures/')) {
      res.setHeader('Content-Type','text/html');
      return res.end(fixture(url.pathname.split('/').pop(),url.searchParams.get('state')||''));
    }
    if(url.pathname==='/auth/session-status') {res.setHeader('Content-Type','application/json');return res.end('{"authenticated":true}');}
    if(url.pathname==='/fixture.svg') {res.setHeader('Content-Type','image/svg+xml');return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" fill="#15803d"/></svg>');}
    const file=path.resolve(root,'public','.'+url.pathname);
    if(!file.startsWith(path.join(root,'public')+path.sep)||!fs.existsSync(file)||fs.statSync(file).isDirectory()) {res.statusCode=404;return res.end();}
    res.setHeader('Content-Type',assetContentType(file));res.end(fs.readFileSync(file));
  });
  await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));origin='http://127.0.0.1:'+server.address().port;
});
test.after(async()=>{await browser?.close();server?.closeAllConnections();await new Promise(resolve=>server?.close(resolve));});

test('guest verification has usable mobile and desktop forms, safe previews and a resend countdown',async()=>{
  for(const width of [320,375,1365]) {
    const page=await browser.newPage({viewport:{width,height:850}}),errors=[];
    page.on('pageerror',error=>errors.push(error.message));
    await page.goto(origin+'/fixtures/guest-contact-verification');
    assert.equal(await page.locator('#guestContactCode').isVisible(),true);
    assert.equal(await page.locator('#guestContactResend').isDisabled(),true);
    assert.match(await page.locator('#guestContactResend').innerText(),/Resend code in/);
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
    await page.locator('details summary').click();
    assert.equal(await page.locator('.contact-verification-message').innerText(),'<script>Text must stay text</script>');
    assert.equal(await page.locator('.contact-verification-message script').count(),0);
    assert.equal(await page.locator('form input[name="csrf_fixture"]').count(),3);
    await page.locator('#guestContactCode').fill('123456');
    let data;
    await page.route('**/contact/verify',route=>{
      data=new URLSearchParams(route.request().postData());
      return route.fulfill({status:200,body:'Verification submitted'});
    });
    await page.screenshot({path:path.join(__dirname,'artifacts','guest-email-verification-'+width+'.png')});
    await Promise.all([page.waitForURL('**/contact/verify'),page.locator('#guestContactVerifyForm button').click()]);
    assert.equal(data.get('code'),'123456');
    assert.equal(data.get('draft_id'),'a'.repeat(64));
    assert.equal(data.has('email'),false);
    assert.deepEqual(errors,[]);
    await page.close();
  }
});

test('a verified draft shows only delivery retry and edit actions',async()=>{
  const page=await browser.newPage({viewport:{width:375,height:800}});
  await page.goto(origin+'/fixtures/guest-contact-verification?state=verified');
  assert.equal(await page.locator('#guestContactCode,#guestContactResend').count(),0);
  assert.match(await page.locator('#guestContactVerifyForm button').innerText(),/Send message/);
  let action;
  await page.route('**/contact/edit',route=>{action=route.request().method();return route.fulfill({body:'Edit saved draft'});});
  await Promise.all([page.waitForURL('**/contact/edit'),page.locator('form[action="/contact/edit"] button').click()]);
  assert.equal(action,'POST');
  await page.close();
});

test('Today Departures has taller cards and a working profile-menu link without overflowing',async()=>{
  for(const width of [320,375,1365]) {
    const page=await browser.newPage({viewport:{width,height:800}}),errors=[];
    page.on('pageerror',error=>errors.push(error.message));
    await page.goto(origin+'/fixtures/staff-departures');
    await page.addScriptTag({url:origin+'/assets/js/autocomplete-search.js'});
    assert.equal(await page.locator('.autocomplete-wrapper input[type="text"]').count(),2);
    const heights=await page.locator('.today-departure-stats .stat-card-modern').evaluateAll(cards=>cards.map(card=>card.getBoundingClientRect().height));
    assert.equal(heights.length,2);assert.ok(heights.every(height=>height>=(width<576?168:184)));
    assert.equal(await page.locator('#userProfileMenu a[href="/staff/departures"]').count(),1);
    assert.equal(await page.locator('.nav-menu > a[href="/staff/departures"]').count(),0);
    await page.locator('#userProfileBtn').click();
    assert.equal(await page.locator('#userProfileMenu a[href="/staff/departures"]').isVisible(),true);
    await page.waitForFunction(()=>getComputedStyle(document.getElementById('userProfileMenu')).opacity === '1');
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
    await page.screenshot({path:path.join(__dirname,'artifacts','today-departures-profile-'+width+'.png')});
    await page.locator('#userProfileBtn').click();
    await page.screenshot({path:path.join(__dirname,'artifacts','today-departures-taller-'+width+'.png')});
    assert.deepEqual(errors,[]);
    await page.close();
  }
});

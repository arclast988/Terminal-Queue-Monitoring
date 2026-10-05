'use strict';
const test=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),http=require('node:http');
const {execFileSync}=require('node:child_process');
const {chromium}=require('playwright');
const {root,assetContentType,phpBinary}=require('./harness.cjs');
let browser,server,origin,policy;
function fixture(name,state='') {
  return execFileSync(phpBinary(),[path.join(__dirname,'render-responsive-fixture.php'),name,'normal','theme',state],{encoding:'utf8',maxBuffer:3*1024*1024});
}
test.before(async()=>{
  browser=await chromium.launch({headless:true,...(process.env.TQ_BROWSER_CHANNEL?{channel:process.env.TQ_BROWSER_CHANNEL}:{})});
  server=http.createServer((req,res)=>{
    const url=new URL(req.url,'http://fixture');
    if(url.pathname.startsWith('/fixtures/')) {
      res.setHeader('Content-Type','text/html');
      res.setHeader('Content-Security-Policy',policy);
      return res.end(fixture(url.pathname.split('/').pop(),url.searchParams.get('state')||''));
    }
    if(url.pathname==='/auth/session-status') {res.setHeader('Content-Type','application/json');return res.end('{"authenticated":true}');}
    if(url.pathname==='/fixture.svg') {res.setHeader('Content-Type','image/svg+xml');return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" fill="#15803d"/></svg>');}
    const file=path.resolve(root,'public','.'+url.pathname);
    if(!file.startsWith(path.join(root,'public')+path.sep)||!fs.existsSync(file)||fs.statSync(file).isDirectory()) {res.statusCode=404;return res.end();}
    res.setHeader('Content-Type',assetContentType(file));res.end(fs.readFileSync(file));
  });
  await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));origin='http://127.0.0.1:'+server.address().port;
  policy=execFileSync(phpBinary(),[path.join(__dirname,'render-security-policy.php'),origin+'/'],{encoding:'utf8'});
});
test.after(async()=>{await browser?.close();server?.closeAllConnections();await new Promise(resolve=>server?.close(resolve));});

test('verification opens over the original guest page with safe mobile/desktop forms and no unused text preload',async()=>{
  for(const width of [320,375,1365]) {
    const page=await browser.newPage({viewport:{width,height:850}}),errors=[],warnings=[];
    page.on('pageerror',error=>errors.push(error.message));
    page.on('console',message=>{if(message.text().includes('preloaded')&&message.text().includes('/fonts/')) warnings.push(message.text());});
    const url=origin+'/fixtures/guest-contact-verification?contact_verify=1';
    await page.goto(url);
    await page.evaluate(()=>document.fonts.ready);
    assert.equal(await page.locator('#guestContactCode').isVisible(),true);
    assert.equal(await page.locator('#guestContactResend').isDisabled(),true);
    assert.match(await page.locator('#guestContactResend').innerText(),/Resend code in/);
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
    assert.ok((await page.locator('link[rel="preload"][href*="/fonts/"]').count()) <= 2);
    assert.equal(await page.locator('#guestContactVerificationModal.active').count(),1);
    await page.locator('#guestContactVerificationContent details summary').click();
    assert.equal(await page.locator('.contact-verification-message').innerText(),'<script>Text must stay text</script>');
    assert.equal(await page.locator('.contact-verification-message script').count(),0);
    assert.equal(await page.locator('#guestContactVerificationContent form input[name="csrf_fixture"]').count(),3);
    await page.locator('#guestContactCode').fill('123456');
    let data;
    await page.route('**/contact/verify',route=>{
      data=route.request().postData();
      assert.equal(route.request().headers().accept,'application/json');
      return route.fulfill({status:200,contentType:'application/json',body:JSON.stringify({success:true,stage:'complete',type:'report',message:'Your message has been sent.',csrf:{name:'csrf_fixture',hash:'new-token'}})});
    });
    await page.screenshot({path:path.join(__dirname,'artifacts','guest-email-verification-'+width+'.png')});
    await page.locator('#guestContactVerifyForm button').click();
    await page.waitForSelector('#reportModal.active [role="status"]');
    assert.equal(page.url(),url);
    assert.match(data,/name="code"\r\n\r\n123456/);
    assert.match(data,/name="draft_id"\r\n\r\na{64}/);
    assert.doesNotMatch(data,/name="email"/);
    assert.equal(await page.locator('input[name="csrf_fixture"]').evaluateAll(inputs=>inputs.every(input=>input.value==='new-token')),true);
    assert.equal(await page.locator('.support-modal-backdrop.active').count(),1);
    await page.waitForTimeout(4000);
    assert.deepEqual(warnings,[]);
    assert.deepEqual(errors,[]);
    await page.close();
  }
});

test('a verified draft shows only delivery retry and edit actions',async()=>{
  const page=await browser.newPage({viewport:{width:375,height:800}});
  const url=origin+'/fixtures/guest-contact-verification?state=verified&contact_verify=1';
  await page.goto(url);
  assert.equal(await page.locator('#guestContactCode,#guestContactResend').count(),0);
  assert.match(await page.locator('#guestContactVerifyForm button').innerText(),/Send message/);
  let action;
  const draft={name:'Saved Passenger',email:'passenger@example.com',subject:'Other operational issue',message:'Saved original report'};
  await page.route('**/contact/edit',route=>{action=route.request().method();return route.fulfill({contentType:'application/json',body:JSON.stringify({success:true,stage:'form',type:'report',draft})});});
  await page.locator('form[action="/contact/edit"] button').click();
  await page.waitForSelector('#reportModal.active');
  assert.equal(page.url(),url);
  assert.equal(await page.locator('#guestReportEmail').inputValue(),draft.email);
  assert.equal(await page.locator('#guestReportMessage').inputValue(),draft.message);
  assert.equal(action,'POST');
  await page.close();
});

test('sending, incorrect codes, resend, close/resume and edit retain the guest layout and draft',async()=>{
  const page=await browser.newPage({viewport:{width:375,height:820}}),errors=[];
  page.on('pageerror',error=>errors.push(error.message));
  const url=origin+'/fixtures/guest-contact-verification?state=empty';
  await page.goto(url);
  await page.locator('footer a[onclick*="reportModal"]').click();
  await page.locator('#guestReportName').fill('Guest Passenger');
  await page.locator('#guestReportEmail').fill('passenger@example.com');
  await page.locator('#guestReportMessage').fill('Saved original report');
  const savedScroll=await page.evaluate(()=>window.scrollY);
  const partial=fixture('guest-contact-verification-content');
  let sends=0;
  await page.route('**/contact/send',async route=>{
    sends++;
    await new Promise(resolve=>setTimeout(resolve,150));
    await route.fulfill({contentType:'application/json',body:JSON.stringify({success:true,stage:'verify',type:'report',html:partial,csrf:{name:'csrf_fixture',hash:'token-1'}})});
  });
  await page.locator('#reportModal button[type="submit"]').click();
  assert.equal(await page.locator('#reportModal button[type="submit"]').isDisabled(),true);
  await page.waitForSelector('#guestContactVerificationModal.active');
  assert.equal(page.url(),url);assert.equal(sends,1);
  assert.equal(await page.locator('.support-modal-backdrop.active').count(),1);
  assert.equal(await page.locator('#guestContactResend').isDisabled(),true);
  assert.equal(await page.locator('#guestContactVerificationContent input[name="csrf_fixture"]').evaluateAll(inputs=>inputs.every(input=>input.value==='token-1')),true);
  await page.keyboard.press('Escape');
  await page.waitForSelector('#guestContactVerificationModal.active',{state:'hidden'});
  assert.equal(await page.evaluate(()=>document.body.style.overflow),'');
  assert.equal(await page.evaluate(()=>window.scrollY),savedScroll);
  await page.locator('footer a[onclick*="reportModal"]').click();
  assert.equal(await page.locator('#guestReportMessage').inputValue(),'Saved original report');
  await page.locator('#reportModal [data-contact-resume]').click();
  await page.waitForSelector('#guestContactVerificationModal.active');
  await page.locator('#guestContactVerificationModal .close-modal').focus();
  await page.keyboard.press('Shift+Tab');
  assert.equal(await page.locator('#guestContactVerificationContent details summary').evaluate(summary=>summary===document.activeElement),true);
  await page.locator('#guestContactCode').fill('000000');
  await page.route('**/contact/verify',route=>route.fulfill({contentType:'application/json',body:JSON.stringify({success:false,stage:'verify',type:'report',html:fixture('guest-contact-verification-content','error')})}));
  await page.locator('#guestContactVerifyForm button').click();
  await page.waitForSelector('#guestContactVerificationModal [role="alert"]');
  assert.match(await page.locator('#guestContactVerificationModal [role="alert"]').innerText(),/4 attempts remaining/);
  await page.evaluate(()=>{document.getElementById('guestContactResend').dataset.availableAt=String(Date.now()-1);});
  await page.waitForFunction(()=>!document.getElementById('guestContactResend').disabled);
  await page.route('**/contact/resend',route=>route.fulfill({contentType:'application/json',body:JSON.stringify({success:true,stage:'verify',type:'report',html:partial})}));
  await page.locator('#guestContactResend').click();
  await page.waitForFunction(()=>document.getElementById('guestContactResend').textContent.includes('Resend code in'));
  await page.route('**/contact/edit',route=>route.fulfill({contentType:'application/json',body:JSON.stringify({success:true,stage:'form',type:'report',draft:{name:'Guest Passenger',email:'passenger@example.com',subject:'Other operational issue',message:'Saved original report'}})}));
  await page.locator('form[action="/contact/edit"] button').click();
  await page.waitForSelector('#reportModal.active');
  assert.equal(await page.locator('#guestReportEmail').inputValue(),'passenger@example.com');
  assert.equal(await page.locator('#reportModal [data-contact-resume]').isVisible(),false);
  assert.equal(page.url(),url);assert.deepEqual(errors,[]);
  await page.close();
});

test('a failed or expired request releases the form, keeps the draft and shows a persistent error',async()=>{
  const page=await browser.newPage({viewport:{width:375,height:820}});
  await page.goto(origin+'/fixtures/guest-contact-verification?state=empty&contact=1');
  await page.locator('#guestContactName').fill('Passenger');
  await page.locator('#guestContactEmail').fill('passenger@example.com');
  await page.locator('#guestContactMessage').fill('Do not discard my message');
  let status=500;
  await page.route('**/contact/send',route=>route.fulfill({status,body:'Failure'}));
  for(const code of [500,403]) {
    status=code;
    await page.locator('#contactModal button[type="submit"]').click();
    await page.waitForFunction(()=>!document.querySelector('#contactModal button[type="submit"]').disabled);
    assert.equal(await page.locator('#guestContactMessage').inputValue(),'Do not discard my message');
    assert.match(await page.locator('#contactModal [role="alert"]').innerText(),code===403?/Refresh the page/:/Please try again/);
  }
  await page.close();
});

test('the production CSP blocks an untrusted script without breaking local guest controls',async()=>{
  const page=await browser.newPage(),blocked=[];
  await page.goto(origin+'/fixtures/guest-contact-verification?state=empty');
  await page.exposeFunction('recordBlocked',uri=>blocked.push(uri));
  await page.evaluate(()=>{
    document.addEventListener('securitypolicyviolation',event=>window.recordBlocked(event.blockedURI));
    const script=document.createElement('script');script.src='https://untrusted.example/attack.js';document.head.appendChild(script);
  });
  await page.waitForFunction(()=>performance.getEntriesByType('resource').some(entry=>entry.name==='https://untrusted.example/attack.js'));
  await page.waitForTimeout(50);
  assert.ok(blocked.some(uri=>uri.includes('untrusted.example')));
  await page.locator('footer a[onclick*="contactModal"]').click();
  assert.equal(await page.locator('#contactModal').isVisible(),true);
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

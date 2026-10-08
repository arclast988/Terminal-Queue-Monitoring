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

test('both guest forms prepare encoded Gmail drafts from the guest account on mobile and desktop',async()=>{
  for(const width of [320,375,1365]) {
    const page=await browser.newPage({viewport:{width,height:850}}),errors=[],requests=[];
    page.on('pageerror',error=>errors.push(error.message));
    page.on('request',request=>{if(request.url().includes('/contact/')) requests.push(request.url());});
    await page.addInitScript(()=>{window.drafts=[];window.open=(url,target,features)=>{window.drafts.push({url,target,features});return null;};});
    const url=origin+'/fixtures/guest-contact-draft';
    await page.goto(url);
    for(const [type,prefix,modal] of [['report','guestReport','reportModal'],['contact','guestContact','contactModal']]) {
      await page.locator('footer a[onclick*="'+modal+'"]').click();
      const form=page.locator('#'+modal+' form');
      assert.equal(await form.locator('input[name="email"]').count(),0);
      assert.equal(await page.locator('#guestContactVerificationModal').count(),0);
      await page.locator('#'+prefix+'Name').fill('José & Passenger');
      if(type==='contact') await page.locator('#'+prefix+'Subject').selectOption('Travel feedback or suggestion');
      const subject=await page.locator('#'+prefix+'Subject').inputValue();
      const message='<script>Stay as text</script>\nPlate ABC-123: café 🚐 & +=?#';
      await page.locator('#'+prefix+'Message').fill(message);
      await form.locator('button[type="submit"]').click();
      const draft=await page.evaluate(()=>window.drafts.at(-1));
      assert.ok(draft,JSON.stringify({type,width,errors,requests,validity:await form.evaluate(form=>Array.from(form.elements).filter(el=>el.validity&&!el.validity.valid).map(el=>({name:el.name,value:el.value,message:el.validationMessage})))}));
      const gmail=new URL(draft.url);
      assert.equal(gmail.origin,'https://mail.google.com');assert.equal(gmail.pathname,'/mail/');
      assert.equal(gmail.searchParams.get('to'),'management@example.com');
      assert.equal(gmail.searchParams.get('view'),'cm');assert.equal(gmail.searchParams.get('tf'),'cm');
      assert.equal(gmail.searchParams.get('authuser'),null);assert.equal(gmail.searchParams.get('from'),null);
      assert.ok(gmail.searchParams.get('su').endsWith((type==='report'?'Report Issue':'Contact Us')+': '+subject));
      assert.equal(gmail.searchParams.get('body'),'Name: José & Passenger\n\n'+message);
      assert.equal(draft.target,'_blank');assert.equal(draft.features,'noopener,noreferrer');
      assert.equal(await form.locator('[data-contact-draft-link]').getAttribute('href'),draft.url);
      assert.match(await form.locator('[role="status"]').innerText(),/ready to review in Gmail/);
      assert.equal(await page.locator('#'+prefix+'Message').inputValue(),message);
      assert.equal(page.url(),url);
      assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
      await page.screenshot({path:path.join(__dirname,'artifacts','guest-gmail-'+type+'-'+width+'.png')});
      await page.keyboard.press('Escape');
      await page.locator('footer a[onclick*="'+modal+'"]').click();
      assert.equal(await page.locator('#'+prefix+'Message').inputValue(),message);
      await page.keyboard.press('Escape');
    }
    assert.deepEqual(requests,[]);assert.deepEqual(errors,[]);await page.close();
  }
});

test('blocked Gmail tabs retain a usable draft link, and editing rebuilds the draft',async()=>{
  const page=await browser.newPage({viewport:{width:375,height:820}});
  await page.addInitScript(()=>{window.open=()=>null;});
  await page.goto(origin+'/fixtures/guest-contact-draft?contact=1');
  await page.locator('#guestContactName').fill('Passenger');
  await page.locator('#guestContactMessage').fill('First message');
  const form=page.locator('#contactModal form');
  await form.locator('button[type="submit"]').click();
  assert.equal(await form.locator('[data-contact-draft-link]').isVisible(),true);
  assert.equal(await form.locator('[data-contact-draft-link]').getAttribute('rel'),'noopener noreferrer');
  assert.equal(await form.locator('button[type="submit"]').isEnabled(),true);
  await page.locator('#guestContactMessage').fill('Corrected message');
  assert.equal(await form.locator('[role="status"]').isVisible(),false);
  assert.equal(await form.locator('[data-contact-draft-link]').getAttribute('href'),null);
  await form.locator('button[type="submit"]').click();
  const link=new URL(await form.locator('[data-contact-draft-link]').getAttribute('href'));
  assert.ok(link.searchParams.get('body').endsWith('Corrected message'));
  await page.close();
});

test('Gmail buttons and blocked-popup links survive the notification timeout and reopening',{timeout:60000},async()=>{
  for(const width of [375,1365]) {
    const page=await browser.newPage({viewport:{width,height:850}}),errors=[];
    page.on('pageerror',error=>errors.push(error.message));
    await page.addInitScript(()=>{window.drafts=[];window.open=url=>{window.drafts.push(url);return null;};});
    await page.goto(origin+'/fixtures/guest-contact-draft');
    // The old notification timer removed both hidden form links after 4.95s.
    await page.waitForTimeout(5500);
    for(const [prefix,modal] of [['guestReport','reportModal'],['guestContact','contactModal']]) {
      await page.locator('footer a[onclick*="'+modal+'"]').click();
      const form=page.locator('#'+modal+' form');
      await form.getByRole('button',{name:'Open Gmail',exact:true}).click();
      const firstUrl=await page.evaluate(()=>window.drafts.at(-1));
      assert.equal(new URL(firstUrl).origin,'https://mail.google.com');
      const link=form.locator('[data-contact-draft-link]');
      await page.waitForTimeout(5500);
      assert.equal(await link.isVisible(),true,'a blocked-popup link stays available until the form is edited');
      assert.equal(await link.getAttribute('href'),firstUrl);
      await page.keyboard.press('Escape');
      await page.locator('footer a[onclick*="'+modal+'"]').click();
      await page.locator('#'+prefix+'Message').fill('Updated after reopening');
      assert.equal(await form.locator('[role="status"]').isVisible(),false);
      await form.getByRole('button',{name:'Open Gmail',exact:true}).click();
      const secondUrl=await page.evaluate(()=>window.drafts.at(-1));
      assert.ok(new URL(secondUrl).searchParams.get('body').endsWith('Updated after reopening'));
      await page.keyboard.press('Escape');
    }
    assert.deepEqual(errors,[]);await page.close();
  }
});

test('a missing email notice does not prevent Gmail opening or editing the form',async()=>{
  const page=await browser.newPage(),errors=[];
  page.on('pageerror',error=>errors.push(error.message));
  await page.addInitScript(()=>{window.drafts=[];window.open=url=>window.drafts.push(url);});
  await page.goto(origin+'/fixtures/guest-contact-draft?report=1');
  await page.locator('#reportModal [data-contact-draft-notice]').evaluate(el=>el.remove());
  await page.locator('#guestReportMessage').fill('Details stay usable');
  await page.locator('#reportModal button[type="submit"]').click();
  assert.equal(new URL(await page.evaluate(()=>window.drafts.at(-1))).origin,'https://mail.google.com');
  assert.deepEqual(errors,[]);await page.close();
});

test('Gmail opens in a separate tab without access to the guest page',async()=>{
  const context=await browser.newContext();
  await context.route('https://mail.google.com/**',route=>route.fulfill({contentType:'text/html',body:'<h1>Gmail draft fixture</h1>'}));
  const page=await context.newPage();
  await page.goto(origin+'/fixtures/guest-contact-draft?report=1');
  await page.locator('#guestReportName').fill('Passenger');
  await page.locator('#guestReportMessage').fill('Draft only; no message delivery.');
  const popupPromise=context.waitForEvent('page');
  await page.locator('#reportModal button[type="submit"]').click();
  const popup=await popupPromise;await popup.waitForLoadState();
  assert.equal(new URL(popup.url()).origin,'https://mail.google.com');
  assert.equal(await popup.evaluate(()=>window.opener),null);
  assert.equal(await page.locator('#reportModal').isVisible(),true);
  await context.close();
});

test('optional guest fields and distinct searchable topics prepare drafts on mobile and desktop',async()=>{
  for (const width of [320,1365]) {
    const page=await browser.newPage({viewport:{width,height:850}}),errors=[];
    page.on('pageerror',error=>errors.push(error.message));
    await page.addInitScript(()=>{window.drafts=[];window.open=url=>window.drafts.push(url);});
    await page.goto(origin+'/fixtures/guest-contact-draft');
    for (const [type,prefix,modal,query,topic,nextTopic] of [
      ['report','guestReport','reportModal','Unsafe','Unsafe driving or vehicle condition','Delayed or cancelled trip'],
      ['contact','guestContact','contactModal','feedback','Travel feedback or suggestion','Terminal hours and facilities'],
    ]) {
      await page.locator('footer a[onclick*="'+modal+'"]').click();
      const form=page.locator('#'+modal+' form');
      assert.equal(await form.locator('[required]').count(),0);
      assert.ok(await page.locator('#'+prefix+'Subject option').count()>=10);
      await page.locator('#'+prefix+'Subject_autocomplete_search').fill(query);
      await form.locator('.autocomplete-item').filter({hasText:topic}).click();
      await form.locator('button[type="submit"]').click();
      let draft=new URL(await page.evaluate(()=>window.drafts.at(-1)));
      const label=type==='report'?'Report Issue':'Contact Us';
      assert.equal(draft.searchParams.get('body'),'');
      assert.ok(draft.searchParams.get('su').endsWith(': '+topic));
      await page.screenshot({path:path.join(__dirname,'artifacts','guest-optional-'+type+'-'+width+'.png')});
      await page.locator('#'+prefix+'Name').fill('   ');
      await page.locator('#'+prefix+'Message').fill('   ');
      await page.locator('#'+prefix+'Subject').selectOption(nextTopic);
      assert.equal(await form.locator('[role="status"]').isVisible(),false);
      assert.equal(await form.locator('[data-contact-draft-link]').getAttribute('href'),null);
      await form.locator('button[type="submit"]').click();
      draft=new URL(await page.evaluate(()=>window.drafts.at(-1)));
      assert.equal(draft.searchParams.get('body'),'');
      assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
      await page.keyboard.press('Escape');
    }
    assert.deepEqual(errors,[]);await page.close();
  }
});

test('the production CSP blocks an untrusted script without breaking local guest controls',async()=>{
  const page=await browser.newPage(),blocked=[];
  await page.goto(origin+'/fixtures/guest-contact-draft');
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

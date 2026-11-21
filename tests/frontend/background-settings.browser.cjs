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
const fixtures = new Map();
let browser, server, origin, savedMode = 'slideshow', saveRequests = 0, savedImage = '/fixture.svg';
const slideshow = ['/fixture-second.svg', '/fixture-third.svg'];
function brandingData() { return {category:'background',app_bg_mode:savedMode,app_background_image:savedImage,app_bg_slideshow:slideshow}; }

function fixture(name, mode) {
  const key = name + ':' + mode;
  if (!fixtures.has(key)) {
    const viewName=name==='guest'?'guest-contact-draft':name;
    let html = execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), viewName, 'normal', 'theme', 'distinct-backgrounds', mode, '', 'shared-dialogs'], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
    html = html.replace(/https:\/\/cdn\.jsdelivr\.net\/npm\/bootstrap@5\.3\.0\/dist\/(css\/bootstrap\.min\.css|js\/bootstrap\.bundle\.min\.js)/g, '/bootstrap/$1');
    fixtures.set(key, html);
  }
  return fixtures.get(key);
}

test.before(async () => {
  server = http.createServer(async (req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname === '/admin/settings/save-bg-mode' && req.method === 'POST') {
      let body = ''; for await (const chunk of req) body += chunk;
      const match = body.match(/name="mode"\r\n\r\n(slideshow|single|none)\r\n/);
      if (!match) { res.writeHead(400); res.end(); return; }
      savedMode = match[1]; saveRequests++;
      const data = brandingData();
      res.setHeader('Content-Type', 'application/json');
      res.end(JSON.stringify({ success: true, data, ...data })); return;
    }
    if (url.pathname === '/api/system-branding') {
      res.setHeader('Content-Type', 'application/json');
      res.end(JSON.stringify({success:true,data:brandingData()})); return;
    }
    if (/^\/fixture(?:-second|-third)?\.svg$/.test(url.pathname)) {
      res.setHeader('Content-Type', 'image/svg+xml');
      const color = url.pathname.includes('second') ? '#3b82f6' : url.pathname.includes('third') ? '#15803d' : '#b71c1c';
      res.end('<svg xmlns="http://www.w3.org/2000/svg" width="320" height="240"><rect width="320" height="240" fill="' + color + '"/></svg>'); return;
    }
    if (url.pathname.startsWith('/view/')) {
      res.setHeader('Content-Type', 'text/html');
      res.end(fixture(url.pathname.slice(6), url.searchParams.get('mode') || savedMode)); return;
    }
    const navigationView={'/guest':'guest','/login':'login','/staff/queue':'staff-queue','/admin/settings':'admin-settings'}[url.pathname];
    if (navigationView) {
      res.setHeader('Content-Type','text/html');
      const html=fixture(navigationView,savedMode);
      if(url.searchParams.has('stream')) {
        const split=html.indexOf('<script>',html.indexOf('id="authBgSlideshow"'));
        res.write(html.slice(0,split));await new Promise(resolve=>setTimeout(resolve,200));res.end(html.slice(split));
      } else res.end(html);
      return;
    }
    const base = url.pathname.startsWith('/bootstrap/') ? path.join(__dirname, 'node_modules/bootstrap/dist') : path.join(root, 'public');
    const relative = url.pathname.startsWith('/bootstrap/') ? url.pathname.slice('/bootstrap/'.length) : url.pathname.slice(1);
    const file = path.resolve(base, relative);
    if (!file.startsWith(base + path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.writeHead(404); res.end(); return; }
    res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
  browser = await playwright[engine].launch({ headless: true, ...(engine === 'chromium' && process.env.TQ_BROWSER_CHANNEL ? {channel:process.env.TQ_BROWSER_CHANNEL} : {}) });
});

test.after(async () => {
  if (browser) await browser.close();
  if (server) { server.closeAllConnections(); await new Promise(resolve => server.close(resolve)); }
});

async function open(t, name, mode, width = 1280, motion = 'full') {
  if (mode) savedMode = mode;
  const context = await browser.newContext({ viewport: { width, height: 900 }, reducedMotion: motion === 'reduced' ? 'reduce' : 'no-preference' });
  t.after(() => context.close());
  await context.addInitScript(({ constrained }) => {
    Object.defineProperty(Navigator.prototype, 'hardwareConcurrency', { configurable: true, get: () => constrained ? 2 : 8 });
    Object.defineProperty(Navigator.prototype, 'deviceMemory', { configurable: true, get: () => constrained ? 2 : 8 });
  }, { constrained: motion === 'lite' });
  await context.route('**/*', route => new URL(route.request().url()).origin === origin ? route.continue() : route.abort());
  const page = await context.newPage(), errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.goto(origin + '/view/' + name + (mode ? '?mode=' + mode : ''));
  await page.evaluate(() => document.fonts.ready);
  return { page, context, errors };
}

async function background(page) {
  return page.evaluate(() => ['::before', '::after'].map(pseudo => {
    const style = getComputedStyle(document.body, pseudo);
    return { content: style.content, image: style.backgroundImage, animation: style.animationName };
  }));
}

test('plain backgrounds render without decorative images on sign-in, recovery, and signed-in pages', { timeout: 30000 }, async t => {
  for (const name of ['login', 'forgot', 'reset', 'verify', 'guest', 'staff-queue', 'admin-settings']) {
    const { page, context, errors } = await open(t, name, 'none');
    const styles = await background(page);
    for (const style of styles) {
      assert.ok(style.content === 'none' || style.content === 'normal', name + ': ' + JSON.stringify(style));
      assert.equal(style.image, 'none', name);
      assert.equal(style.animation, 'none', name);
    }
    if (['login', 'forgot', 'reset', 'verify'].includes(name)) {
      assert.equal(await page.locator('#authBgSlideshow').isVisible(), false, name);
      assert.equal(await page.locator('#authBgSlideshow img[src]').count(), 0, name);
      assert.equal(await page.locator('.brand-mark img').isVisible(), true, name);
      await page.evaluate(() => window.applyLiveBranding({ category: 'background', app_bg_mode: 'slideshow', app_bg_slideshow: ['/fixture.svg', '/fixture-second.svg'] }));
      assert.equal(await page.locator('#authBgSlideshow').isVisible(), true, name);
      await page.evaluate(() => window.applyLiveBranding({ category: 'background', app_bg_mode: 'none' }));
      assert.equal(await page.locator('#authBgSlideshow').isVisible(), false, name);
    }
    assert.deepEqual(errors, [], name);
    await context.close();
  }
});

test('a saved plain background can restore photos in every motion mode through both live update paths', { timeout: 30000 }, async t => {
  for (const motion of ['full', 'lite', 'reduced']) {
    const { page, context, errors } = await open(t, 'admin-settings', 'none', 1280, motion);
    assert.equal(await page.evaluate(() => window.TerminalMotion.getMode()), motion);
    for (const method of ['applyLiveBgMode', 'applyLiveBranding']) {
      await page.evaluate(method => {
        // Seed the shared clock so lifecycle realignment preserves this phase.
        // Seeking one CSS animation is temporary when the viewport settles.
        const slides = ['/fixture.svg', '/fixture-second.svg'];
        const fingerprint = JSON.stringify(slides);
        localStorage.setItem('tq-background-timeline:' + fingerprint, JSON.stringify({fingerprint, epoch:Date.now() - 6500}));
        if (method === 'applyLiveBgMode') window[method]('slideshow', '/fixture.svg', ['/fixture.svg', '/fixture-second.svg']);
        else window[method]({ category: 'background', app_bg_mode: 'slideshow', app_bg_slideshow: ['/fixture.svg', '/fixture-second.svg'] });
      }, method);
      if (motion === 'full') {
        await page.waitForFunction(() => document.getAnimations().some(item => /BgSlideshowLive$/.test(item.animationName)));
        await page.waitForFunction(()=>getComputedStyle(document.body,'::before').backgroundImage.includes('fixture-second.svg'));
      }
      await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
      const style = (await background(page))[0];
      assert.match(style.image, motion === 'full' ? /fixture-second\.svg/ : /fixture\.svg/, motion + ': ' + method);
      assert.notEqual(style.content, 'none', motion + ': ' + method);
      if (motion !== 'full') assert.equal(style.animation, 'none');
      await page.evaluate(method => method === 'applyLiveBgMode' ? window[method]('none') : window[method]({ category: 'background', app_bg_mode: 'none' }), method);
      for (const hiddenStyle of await background(page)) assert.equal(hiddenStyle.content, 'none');
    }
    assert.deepEqual(errors, [], motion);
    await context.close();
  }
});

test('the third option waits for Save, persists on reload, and keeps notice and button aligned', { timeout: 30000 }, async t => {
  for (const width of [1280, 375, 320]) {
    savedMode = 'slideshow'; saveRequests = 0;
    const { page, context, errors } = await open(t, 'admin-settings', '', width);
    await page.locator('#tabBtn-media').click();
    await page.locator('#modeCardNone').click();
    assert.equal(await page.locator('#modeCardNone input').isChecked(), true);
    assert.equal(await page.locator('#sectionSlideshowManager').isVisible(), false);
    assert.equal(await page.locator('#sectionSingleBgManager').isVisible(), false);
    assert.equal(saveRequests, 0);
    assert.notEqual((await background(page))[0].content, 'none');
    const alignment = await page.evaluate(() => {
      const bar = document.querySelector('.bg-save-bar').getBoundingClientRect();
      const notice = document.querySelector('#bgPendingNotice');
      const icon = notice.querySelector('i').getBoundingClientRect();
      const text = notice.querySelector('span').getBoundingClientRect();
      const button = document.querySelector('#btnSaveBgSettings').getBoundingClientRect();
      return { centerDelta: Math.abs((icon.top + icon.height / 2) - (text.top + text.height / 2)), textFits: text.right <= bar.right && text.left >= bar.left, buttonFits: button.right <= bar.right && button.left >= bar.left, overflow: document.documentElement.scrollWidth > innerWidth };
    });
    assert.ok(alignment.centerDelta < 2, JSON.stringify(alignment));
    assert.equal(alignment.textFits, true); assert.equal(alignment.buttonFits, true); assert.equal(alignment.overflow, false);
    if (process.env.TQ_BG_SCREENSHOTS) {
      fs.mkdirSync(process.env.TQ_BG_SCREENSHOTS, { recursive: true });
      await page.locator('.settings-card').filter({ has: page.getByRole('heading', { name: 'Authentication & Login Background Pictures', exact: true }) }).screenshot({ path: path.join(process.env.TQ_BG_SCREENSHOTS, 'background-settings-' + width + '.png'), animations: 'disabled' });
    }
    await page.locator('#btnSaveBgSettings').click();
    await page.waitForFunction(() => getComputedStyle(document.querySelector('#bgPendingNotice')).display === 'none');
    assert.equal(savedMode, 'none'); assert.equal(saveRequests, 1);
    for (const style of await background(page)) assert.equal(style.content, 'none');
    await page.reload();
    await page.locator('#tabBtn-media').click();
    assert.equal(await page.locator('#modeCardNone input').isChecked(), true);
    for (const style of await background(page)) assert.equal(style.content, 'none');
    assert.deepEqual(errors, []);
    await context.close();
  }
});

test('Single Hero uses the saved image instead of the first slideshow photo on every auth page', {timeout:30000}, async t => {
  savedImage='/fixture.svg';
  for (const name of ['login','forgot','reset','verify']) {
    const {page,context,errors}=await open(t,name,'single');
    assert.match(await page.locator('#authBgSlideshow img.is-active').getAttribute('src'),/\/fixture\.svg$/,name);
    assert.deepEqual(errors,[],name);await context.close();
  }
});

test('saving a background updates already-open auth, guest and management pages without reloading', {timeout:60000}, async t => {
  savedMode='slideshow';savedImage='/fixture.svg';
  const {page:settings,context,errors}=await open(t,'admin-settings','slideshow');
  await context.routeWebSocket('**/ws',socket=>socket.close());
  const pages=[];
  for (const name of ['login','forgot','reset','verify','guest','staff-queue']) {
    const page=await context.newPage();page.on('pageerror',error=>errors.push(name+': '+error.message));
    await page.goto(origin+'/view/'+name);
    await page.evaluate(()=>{
      window.backgroundUpdateModes=[];
      document.addEventListener('pttm:branding-applied',event=>{
        if(event.detail.app_bg_mode)window.backgroundUpdateModes.push(event.detail.app_bg_mode);
      });
    });
    pages.push({name,page,timeOrigin:await page.evaluate(()=>performance.timeOrigin)});
  }
  await settings.locator('#tabBtn-media').click();
  for (const mode of ['single','none','slideshow']) {
    await settings.locator({single:'#modeCardSingle',none:'#modeCardNone',slideshow:'#modeCardSlideshow'}[mode]).click();
    await settings.locator('#btnSaveBgSettings').click();
    await settings.waitForFunction(()=>getComputedStyle(document.querySelector('#bgPendingNotice')).display==='none');
    for (const {name,page,timeOrigin} of pages) {
      t.diagnostic(mode+': '+name);
      if (['login','forgot','reset','verify'].includes(name)) {
        await page.waitForFunction(({mode,slides})=>{
          const root=document.querySelector('#authBgSlideshow');
          const expected=mode==='single'?'/fixture.svg':slides[TerminalMotion.getBackgroundPhase(slides).index];
          return window.backgroundUpdateModes.includes(mode)&&(mode==='none'?getComputedStyle(root).display==='none':getComputedStyle(root).display!=='none'&&root.querySelector('img.is-active').getAttribute('src')===expected);
        },{mode,slides:slideshow},{timeout:5000});
      } else {
        await page.waitForFunction(({mode,slides})=>{
          const style=getComputedStyle(document.body,'::before');
          const expected=mode==='single'?'/fixture.svg':slides[TerminalMotion.getBackgroundPhase(slides).index];
          return window.backgroundUpdateModes.includes(mode)&&(mode==='none'?style.content==='none':style.backgroundImage.includes(expected));
        },{mode,slides:slideshow},{timeout:5000});
      }
      assert.equal(await page.evaluate(()=>performance.timeOrigin),timeOrigin,name+' must stay on the same document');
    }
  }
  assert.deepEqual(errors,[]);
});

test('unchanged background saves use the warning notice without broadcasting and real mode changes still save', {timeout:60000}, async t=>{
  for(const width of [375,1280]) for(const mode of ['none','single','slideshow']) {
    const {page,context,errors}=await open(t,'admin-settings',mode,width,'reduced');
    await page.locator('#tabBtn-media').click();
    let stored=mode,requests=0;
    await page.route('**/admin/settings/save-bg-mode',async route=>{
      requests++;
      const body=route.request().postData(),selected=body.match(/name="mode"\r\n\r\n(slideshow|single|none)\r\n/)[1];
      const no_change=selected===stored;stored=selected;
      await route.fulfill({json:{success:true,no_change,app_bg_mode:stored,
        message:no_change?'No changes detected — background settings are already up to date.':'Background display mode saved successfully!',
        csrf_token:'csrf_fixture',csrf_hash:'updated-background-csrf',
        ...(no_change?{}:{data:{...brandingData(),app_bg_mode:stored}})}});
    });
    await page.evaluate(()=>{
      window.backgroundSaveEffects=[];
      for(const name of ['broadcastLiveBranding','applyLiveBgMode']) {
        const original=window[name];window[name]=function(){window.backgroundSaveEffects.push(name);if(original)return original.apply(this,arguments);};
      }
    });
    const save=page.locator('#btnSaveBgSettings');
    await save.click();
    await page.locator('#sbToast.warning.show').waitFor({state:'visible'});
    assert.match(await page.locator('#sbToastMsg').innerText(),/No changes detected/);
    assert.match(await page.locator('#sbToastIcon').getAttribute('class'),/fa-exclamation-triangle/);
    assert.equal(await page.locator('#sbToast').evaluate(el=>getComputedStyle(el).borderLeftColor),'rgb(245, 158, 11)');
    assert.deepEqual(await page.evaluate(()=>window.backgroundSaveEffects),[]);
    assert.equal(await save.isEnabled(),true);assert.equal(await page.locator('#bgPendingNotice').isVisible(),false);
    const other=mode==='none'?'single':'none';
    await page.locator('input[name="bg_display_mode"][value="'+other+'"]').check();
    await page.locator('input[name="bg_display_mode"][value="'+mode+'"]').check();
    await save.click();await page.waitForFunction(()=>!document.querySelector('#btnSaveBgSettings').disabled);
    assert.equal(requests,2);assert.match(await page.locator('#sbToast').getAttribute('class'),/\bwarning\b/);
    assert.deepEqual(await page.evaluate(()=>window.backgroundSaveEffects),[]);
    await page.locator('input[name="bg_display_mode"][value="'+other+'"]').check();
    await save.click();await page.locator('#sbToast.success.show').waitFor({state:'visible'});
    assert.equal(stored,other);assert.equal(requests,3);
    const effects=await page.evaluate(()=>window.backgroundSaveEffects);
    assert.equal(effects.filter(effect=>effect==='broadcastLiveBranding').length,1);
    assert.ok(effects.includes('applyLiveBgMode'));
    assert.equal(await page.locator('#bgPendingNotice').isVisible(),false);
    assert.deepEqual(errors,[]);await context.close();
  }
});

test('a staged background image saves successfully even when Single Hero mode is unchanged', {timeout:40000}, async t=>{
  for(const width of [375,1280]) {
    const {page,context,errors}=await open(t,'admin-settings','single',width,'reduced');
    await page.locator('#tabBtn-media').click();
    const uploads=[];let modeOnlyRequests=0;
    await page.route('**/admin/settings/save-bg-mode',route=>{modeOnlyRequests++;return route.fulfill({json:{success:true,no_change:true}});});
    await page.route('**/admin/settings/upload-background',async route=>{
      uploads.push(route.request().postDataBuffer().toString('latin1'));
      await route.fulfill({json:{success:true,message:'Background settings saved successfully!',image_url:'/fixture-third.svg',
        app_bg_mode:'single',app_bg_slideshow:slideshow,data:{...brandingData(),app_bg_mode:'single',app_background_image:'/fixture-third.svg'}}});
    });
    const file=await page.evaluate(()=>{const canvas=document.createElement('canvas');canvas.width=80;canvas.height=50;const ctx=canvas.getContext('2d');ctx.fillStyle='#15803d';ctx.fillRect(0,0,80,50);return canvas.toDataURL('image/png').split(',')[1];});
    await page.locator('#bgFileInput').setInputFiles({name:'new-background.png',mimeType:'image/png',buffer:Buffer.from(file,'base64')});
    await page.getByRole('button',{name:'Use original',exact:true}).click();
    await page.getByRole('dialog',{name:'Crop image'}).waitFor({state:'hidden'});
    assert.equal(await page.locator('#bgPendingNotice').isVisible(),true);assert.equal(uploads.length,0);
    await page.locator('#btnSaveBgSettings').click();await page.locator('#sbToast.success.show').waitFor({state:'visible'});
    assert.equal(uploads.length,1);assert.equal(modeOnlyRequests,0);
    assert.match(uploads[0],/name="background"; filename="new-background\.png"/);
    assert.match(uploads[0],/name="mode"\r\n\r\nsingle/);
    assert.equal(await page.locator('#bgPendingNotice').isVisible(),false);
    assert.match(await page.locator('#bgPreviewImg').getAttribute('src'),/fixture-third\.svg/);
    assert.deepEqual(errors,[]);await context.close();
  }
});

test('an open Login receives socket artwork updates and polls when its connection is unavailable', {timeout:45000}, async t => {
  savedMode='slideshow';savedImage='/fixture.svg';
  const context=await browser.newContext({viewport:{width:1280,height:900}});t.after(()=>context.close());
  await context.route('**/*',route=>new URL(route.request().url()).origin===origin?route.continue():route.abort());
  let socket;
  await context.routeWebSocket('**/ws',connection=>{socket=connection;});
  const page=await context.newPage(),errors=[];page.on('pageerror',error=>errors.push(error.message));
  await page.clock.install();
  await page.goto(origin+'/view/login');
  await page.clock.runFor(1000);await page.waitForFunction(()=>QueueWS.isConnected());
  const timeOrigin=await page.evaluate(()=>performance.timeOrigin);
  savedMode='single';savedImage='/fixture-third.svg';
  socket.send(JSON.stringify({type:'branding_updated',data:brandingData()}));
  await page.waitForFunction(()=>document.querySelector('#authBgSlideshow img.is-active').getAttribute('src')==='/fixture-third.svg');
  socket.close();await page.waitForFunction(()=>!QueueWS.isConnected());
  await context.routeWebSocket('**/ws',connection=>connection.close());
  savedImage='/fixture.svg';
  await page.clock.runFor(21000);
  await page.waitForFunction(()=>document.querySelector('#authBgSlideshow img.is-active').getAttribute('src')==='/fixture.svg');
  assert.equal(await page.evaluate(()=>performance.timeOrigin),timeOrigin);
  assert.deepEqual(errors,[]);
});

test('Guest, Login and management navigation keep the same mid-cycle slideshow picture', {timeout:60000}, async t => {
  savedMode='slideshow';savedImage='/fixture.svg';
  const {page,context,errors}=await open(t,'guest','slideshow');
  const now=Date.now();
  await context.addInitScript(now=>{
    Date.now=()=>now;window.firstBackgroundPhoto=null;
    function readFirstFrame(){
      if(document.body){
        const auth=document.querySelector('#authBgSlideshow');
        const image=auth&&auth.querySelector('img.is-active');
        const visibleImage=image&&image.complete&&image.naturalWidth>0&&Number(getComputedStyle(image).opacity)>0;
        const value=auth?(visibleImage?image.getAttribute('src'):getComputedStyle(auth).backgroundImage):getComputedStyle(document.body,'::before').backgroundImage;
        if(value&&value!=='none'){window.firstBackgroundPhoto=value;return;}
      }
      requestAnimationFrame(readFirstFrame);
    }
    requestAnimationFrame(readFirstFrame);
  },now);
  await page.evaluate(({now,slides})=>{
    const fingerprint=JSON.stringify(slides);
    localStorage.setItem('tq-background-timeline:'+fingerprint,JSON.stringify({fingerprint,epoch:now-7500}));
  },{now,slides:slideshow});
  for (const name of ['guest','login','forgot','staff-queue','admin-settings','guest']) {
    if (name==='login') {
      await page.locator('a[href="/login"]:visible').first().evaluate(link=>link.href='/login?stream=1');
      await page.locator('a[href="/login?stream=1"]:visible').first().click();await page.waitForURL(origin+'/login?stream=1');
    } else await page.goto(origin+'/view/'+name);
    await page.waitForFunction(()=>window.firstBackgroundPhoto);
    assert.match(await page.evaluate(()=>window.firstBackgroundPhoto),/fixture-third\.svg/,name+' must paint the shared photo in its first frame');
    if (['login','forgot'].includes(name)) {
      assert.equal(await page.locator('#authBgSlideshow img.is-active').getAttribute('src'),'/fixture-third.svg',name);
    } else {
      await page.evaluate(()=>document.getAnimations().filter(animation=>/BgSlideshow/.test(animation.animationName||'')).forEach(animation=>{animation.pause();animation.currentTime=0;}));
      assert.match((await background(page))[0].image,/fixture-third\.svg/,name);
    }
  }
  const other=await context.newPage();await other.goto(origin+'/view/login');
  assert.equal(await other.locator('#authBgSlideshow img.is-active').getAttribute('src'),'/fixture-third.svg','An already-shared tab epoch also applies to a new Login tab.');
  assert.deepEqual(errors,[]);
});

test('auth backgrounds keep their static first-photo fallback when JavaScript is unavailable', {timeout:30000}, async t=>{
  for(const mode of ['slideshow','single','none']){
    const context=await browser.newContext({javaScriptEnabled:false,viewport:{width:1280,height:900}});t.after(()=>context.close());
    await context.route('**/*',route=>new URL(route.request().url()).origin===origin?route.continue():route.abort());
    const page=await context.newPage();await page.goto(origin+'/view/login?mode='+mode);
    const root=page.locator('#authBgSlideshow'),image=root.locator('img').first();
    if(mode==='none'){
      assert.equal(await root.isVisible(),false);assert.equal(await image.getAttribute('src'),null);
    }else{
      assert.equal(await root.isVisible(),true);
      assert.equal(await image.evaluate(el=>getComputedStyle(el).opacity),'1');
      assert.equal(await image.getAttribute('src'),mode==='single'?'/fixture.svg':'/fixture-second.svg');
    }
    await context.close();
  }
});

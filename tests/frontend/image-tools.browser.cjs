'use strict';
const test=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs'),path=require('node:path'),http=require('node:http');
const {execFileSync}=require('node:child_process');
const {root,assetContentType,phpBinary}=require('./harness.cjs');
const engine=process.env.TQ_BROWSER || 'chromium';
const uploads=[], fixtures=new Map();
let browser,server,origin;

test.before(async()=>{
  for(const name of ['admin-settings','admin-vehicles','admin-vehicles-admin','admin-users','admin-vehicle-edit','staff-queue','guest']) {
    const html=execFileSync(phpBinary(),[path.join(__dirname,'render-responsive-fixture.php'),name.replace(/-admin$/,''),'normal','theme','vehicle-type-photos','',name.endsWith('-admin')?'admin':'super_admin'],{encoding:'utf8',maxBuffer:3*1024*1024});
    // Keep actual image handlers, Bootstrap, and the shared motion/image policy.
    // Queue polling and unrelated account/network code are isolated from these tests.
    const isolated=html.replace(/<script\b[^>]*>[\s\S]*?<\/script>/g,script=>{
      if(/image-tools\.js|bootstrap\.bundle|window.TerminalMotion|function uploadMedia|function previewRegVehiclePhoto|previewEditVehPhoto|var fileInput = document.getElementById\('modalAvatarInput'\)/.test(script)) return script;
      return '';
    });
    fixtures.set(name,isolated);
  }
  server=http.createServer((req,res)=>{
    const url=new URL(req.url,'http://fixture');
    if(req.method==='POST') {
      const chunks=[];req.on('data',chunk=>chunks.push(chunk));req.on('end',()=>{
        uploads.push({path:url.pathname,contentType:req.headers['content-type'],body:Buffer.concat(chunks)});
        res.setHeader('Content-Type','application/json');res.end(JSON.stringify({success:true,image_url:'/fixture.svg',slot:6,message:'Saved',csrf_token:'csrf_fixture',csrf_hash:'unchanged'}));
      });return;
    }
    if(fixtures.has(url.pathname.slice(1))) {res.setHeader('Content-Type','text/html');return res.end(fixtures.get(url.pathname.slice(1)));}
    if(/^\/fixture(?:-custom|-updated)?\.svg$/.test(url.pathname)) {res.setHeader('Content-Type','image/svg+xml');return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450"><rect width="800" height="450" fill="'+(url.pathname.includes('updated')?'#15803d':'#1565c0')+'"/><text x="200" y="220" fill="white" font-size="56">Vehicle photo</text></svg>');}
    const file=path.resolve(root,'public','.'+url.pathname);
    if(!file.startsWith(path.join(root,'public')+path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) {res.statusCode=404;return res.end();}
    res.setHeader('Content-Type',assetContentType(file));res.end(fs.readFileSync(file));
  });
  await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));origin='http://127.0.0.1:'+server.address().port;
  browser=await require('playwright')[engine].launch({headless:true,...(process.env.TQ_BROWSER_EXECUTABLE?{executablePath:process.env.TQ_BROWSER_EXECUTABLE}:{})});
});
test.after(async()=>{await browser?.close();server?.closeAllConnections();if(server) await new Promise(resolve=>server.close(resolve));});
async function open(t,name,width=375,mode='lite') {
  const context=await browser.newContext({viewport:{width,height:812},hasTouch:engine==='chromium' && width<768,reducedMotion:mode==='reduced'?'reduce':'no-preference'});t.after(()=>context.close());
  await context.addInitScript(mode=>{
    Object.defineProperty(Navigator.prototype,'hardwareConcurrency',{get:()=>mode==='lite'?2:8});
    Object.defineProperty(Navigator.prototype,'deviceMemory',{get:()=>mode==='lite'?2:8});
  },mode);
  await context.route('**/*',route=>route.request().url().startsWith(origin) || route.request().url().startsWith('data:') || route.request().url().startsWith('blob:') ? route.continue():route.abort());
  const page=await context.newPage(),errors=[];page.on('pageerror',error=>errors.push(error.message));
  await page.goto(origin+'/'+name);await page.waitForFunction(()=>!!window.TerminalImages);
  if(name==='admin-settings') await page.locator('#tabBtn-media').click();
  return {page,context,errors};
}
async function imageFile(page,width=800,height=600,alpha=false) {
  const encoded=await page.evaluate(({width,height,alpha})=>{
    const canvas=document.createElement('canvas');canvas.width=width;canvas.height=height;
    const ctx=canvas.getContext('2d');if(!alpha) {ctx.fillStyle='#1565c0';ctx.fillRect(0,0,width,height);}
    ctx.fillStyle='#dc2626';ctx.fillRect(0,0,width/2,height/2);
    return canvas.toDataURL('image/png').split(',')[1];
  },{width,height,alpha});
  return {name:'photo.png',mimeType:'image/png',buffer:Buffer.from(encoded,'base64')};
}
async function fileInfo(page,selector) {
  return page.locator(selector).evaluate(async input=>{
    const file=input.files[0],url=URL.createObjectURL(file),img=new Image();img.src=url;await img.decode();
    const canvas=document.createElement('canvas');canvas.width=img.naturalWidth;canvas.height=img.naturalHeight;const ctx=canvas.getContext('2d');ctx.drawImage(img,0,0);const corner=Array.from(ctx.getImageData(canvas.width-1,canvas.height-1,1,1).data);URL.revokeObjectURL(url);
    return {name:file.name,type:file.type,width:img.naturalWidth,height:img.naturalHeight,corner};
  });
}
async function applyCrop(page) {
  await page.getByRole('button',{name:'Use crop',exact:true}).click();
  await page.getByRole('dialog',{name:'Crop image'}).waitFor({state:'hidden'});
}

test('crop shapes change real pixels and stage only the confirmed logo without an early upload',{timeout:60000},async t=>{
  for(const [width,mode] of [[320,'lite'],[1365,'full'],[375,'reduced']]) {
    const {page,context,errors}=await open(t,'admin-settings',width,mode);
    const before=uploads.length;
    await page.locator('#logoFileInput').setInputFiles(await imageFile(page));
    const dialog=page.getByRole('dialog',{name:'Crop image'});await dialog.waitFor({state:'visible'});
    assert.equal(uploads.length,before,'selecting a file never uploads before a crop decision');
    assert.ok(await dialog.evaluate(el=>el.getBoundingClientRect().right<=innerWidth && el.getBoundingClientRect().left>=0));
    await page.getByLabel('Crop shape').selectOption('1.7777777778');
    if(width===375 && engine==='chromium') {
      fs.mkdirSync(path.join(__dirname,'artifacts'),{recursive:true});
      await page.screenshot({path:path.join(__dirname,'artifacts','mobile-image-crop.png')});
    }
    await applyCrop(page);
    const image=await fileInfo(page,'#logoFileInput');
    assert.equal(image.width,800);assert.equal(image.height,450);assert.match(image.name,/-cropped\.png$/);
    assert.equal(uploads.length,before,'branding still waits for its existing Save action');
    await page.locator('#btnSaveLogo').click();
    await page.waitForFunction(()=>document.querySelector('#logoFileInput').files.length===0 || document.querySelector('#logoStatusChip')?.textContent.includes('Custom'));
    assert.equal(uploads.length,before+1);
    assert.match(uploads[before].body.toString('latin1'),/name="logo"; filename="photo-cropped\.png"/);
    assert.match(uploads[before].body.toString('latin1'),/name="csrf_fixture"/);
    assert.deepEqual(errors,[]);await context.close();
  }
});

test('free crop supports touch resizing, preserves transparency and keeps native form fields',{timeout:40000},async t=>{
  const {page,context,errors}=await open(t,'admin-vehicles',375);
  await page.evaluate(()=>bootstrap.Modal.getOrCreateInstance(document.querySelector('#registerVehicleModal')).show());
  const input=page.locator('#reg_veh_photo_input');await input.setInputFiles(await imageFile(page,800,600,true));
  await page.getByLabel('Crop shape').selectOption('free');
  const box=page.locator('.tq-crop-selection'),handle=page.getByRole('button',{name:'Resize crop area'});
  const bounds=await handle.boundingBox();assert.ok(bounds);
  if(engine==='chromium') {
    const touch=await context.newCDPSession(page);
    await touch.send('Input.dispatchTouchEvent',{type:'touchStart',touchPoints:[{x:bounds.x+bounds.width/2,y:bounds.y+bounds.height/2}]});
    await touch.send('Input.dispatchTouchEvent',{type:'touchMove',touchPoints:[{x:bounds.x-65,y:bounds.y-35}]});
    await touch.send('Input.dispatchTouchEvent',{type:'touchEnd',touchPoints:[]});await touch.detach();
  } else {
    await page.mouse.move(bounds.x+bounds.width/2,bounds.y+bounds.height/2);await page.mouse.down();await page.mouse.move(bounds.x-65,bounds.y-35);await page.mouse.up();
  }
  const before=await box.evaluate(el=>({w:el.getBoundingClientRect().width,h:el.getBoundingClientRect().height}));
  await box.focus();await box.press('Shift+ArrowLeft');
  assert.ok((await box.boundingBox()).width<before.w);
  await applyCrop(page);
  const image=await fileInfo(page,'#reg_veh_photo_input');assert.ok(image.width<800 && image.height<600);assert.equal(image.type,'image/png');assert.equal(image.corner[3],0,'transparent area remains transparent');
  const form=await page.locator('#registerVehicleForm').evaluate(form=>{const data=new FormData(form);return {photo:data.get('photo').name,csrf:data.get('csrf_fixture')};});
  assert.deepEqual(form,{photo:'photo-cropped.png',csrf:'unchanged'});assert.deepEqual(errors,[]);
});

test('dropped logos and backgrounds use the same crop decision and preserve existing Save actions',{timeout:40000},async t=>{
  const {page,errors}=await open(t,'admin-settings',375);
  for(const [inputId,zoneId,saveId,field] of [
    ['logoFileInput','logoUploadZone','btnSaveLogo','logo'],
    ['bgFileInput','bgUploadZone','btnSaveBgSettings','background'],
    ['loginCardFileInput','loginCardUploadZone','btnSaveLoginCard','login_card']
  ]) {
    const file=await imageFile(page),before=uploads.length;
    await page.locator('#'+zoneId).evaluate((zone,bytes)=>{
      const transfer=new DataTransfer();transfer.items.add(new File([new Uint8Array(bytes)],'dropped.png',{type:'image/png'}));
      zone.classList.add('drag-over');zone.dispatchEvent(new DragEvent('drop',{bubbles:true,cancelable:true,dataTransfer:transfer}));
    },Array.from(file.buffer));
    await page.getByRole('dialog',{name:'Crop image'}).waitFor({state:'visible'});
    assert.equal(uploads.length,before);assert.equal(await page.locator('#'+zoneId).evaluate(el=>el.classList.contains('drag-over')),false);
    await page.getByLabel('Crop shape').selectOption('1');await applyCrop(page);
    assert.equal((await fileInfo(page,'#'+inputId)).width,600);assert.equal(uploads.length,before);
    const saved=page.waitForResponse(response=>response.request().method()==='POST' && response.status()===200);
    await page.locator('#'+saveId).click();await saved;
    assert.equal(uploads.length,before+1);assert.match(uploads[before].body.toString('latin1'),new RegExp('name="'+field+'"; filename="dropped-cropped.png"'));
  }
  assert.deepEqual(errors,[]);
});

test('large exports are bounded and newly added slideshow inputs wait for a crop decision',{timeout:40000},async t=>{
  const {page,errors}=await open(t,'admin-settings',1365,'full');
  await page.locator('#logoFileInput').setInputFiles(await imageFile(page,4096,3072));
  await page.addScriptTag({url:origin+'/assets/js/autocomplete-search.js'});await page.evaluate(()=>window.initLocationAutocomplete());
  assert.equal(await page.getByLabel('Crop shape').evaluate(el=>getComputedStyle(el).opacity),'1','the crop shape control stays a usable native select');
  await applyCrop(page);
  const image=await fileInfo(page,'#logoFileInput');assert.equal(image.width,2048);assert.equal(image.height,1536);
  let before=uploads.length;
  await page.locator('#newSlotFileInput').setInputFiles(await imageFile(page));
  await page.getByRole('button',{name:'Cancel',exact:true}).click();assert.equal(uploads.length,before);
  await page.locator('#newSlotFileInput').setInputFiles(await imageFile(page));
  const uploaded=page.waitForResponse(response=>response.url().includes('add-slideshow-slot') && response.status()===200);
  await applyCrop(page);await uploaded;
  await page.locator('#slotFileInput-6').waitFor({state:'attached'});assert.equal(uploads.length,before+1);
  before=uploads.length;await page.locator('#slotFileInput-6').setInputFiles(await imageFile(page));
  assert.equal(uploads.length,before);
  const replaced=page.waitForResponse(response=>response.url().includes('upload-slideshow-slot') && response.status()===200);
  await applyCrop(page);await replaced;
  assert.equal(uploads.length,before+1);assert.match(uploads[before].body.toString('latin1'),/name="slot"\r\n\r\n6/);assert.deepEqual(errors,[]);
});

test('Cancel and Escape keep the previous staged photo and Use original preserves original bytes',{timeout:30000},async t=>{
  const {page,errors}=await open(t,'admin-settings',375);
  const input=page.locator('#logoFileInput'),file=await imageFile(page);
  await input.setInputFiles(file);await page.getByRole('button',{name:'Use original',exact:true}).click();
  assert.equal(await input.evaluate(input=>input.files[0].name),'photo.png');
  const original=await input.evaluate(async input=>Array.from(new Uint8Array(await input.files[0].arrayBuffer())));
  assert.deepEqual(Buffer.from(original),file.buffer);
  await input.setInputFiles({...file,name:'second.png'});await page.getByRole('button',{name:'Cancel',exact:true}).click();
  assert.equal(await input.evaluate(input=>input.files[0].name),'photo.png');
  await input.setInputFiles({...file,name:'third.png'});await page.keyboard.press('Escape');
  assert.equal(await input.evaluate(input=>input.files[0].name),'photo.png');assert.equal(await page.locator('dialog[open]').count(),0);assert.deepEqual(errors,[]);
});

test('avatar cropping defaults to square inside its existing modal and cancel sends nothing',{timeout:30000},async t=>{
  const {page,errors}=await open(t,'admin-users',375);
  await page.evaluate(()=>window.openAvatarModal(2,'Fixture Driver','/fixture.svg','FD','staff'));
  const before=uploads.length;
  await page.locator('#modalAvatarInput').setInputFiles(await imageFile(page));
  const dialog=page.getByRole('dialog',{name:'Crop image'});await dialog.waitFor({state:'visible'});
  assert.equal(await page.getByLabel('Crop shape').inputValue(),'1');
  await applyCrop(page);
  const image=await fileInfo(page,'#modalAvatarInput');assert.equal(image.width,600);assert.equal(image.height,600);
  assert.equal(uploads.length,before);await page.locator('#modalSaveAvatarBtn').click();
  await page.waitForFunction(()=>document.querySelector('#modalAvatarInput').files.length===0);
  assert.equal(uploads.length,before+1);assert.match(uploads[before].body.toString('latin1'),/name="avatar"; filename="photo-cropped\.png"/);assert.deepEqual(errors,[]);
});

test('vehicle photos open clearly for guests, dispatchers, admins and superadmins without taking filter actions',{timeout:60000},async t=>{
  for(const name of ['guest','staff-queue','admin-vehicles','admin-vehicles-admin']) {
    for(const width of [375,1365]) {
      const {page,context,errors}=await open(t,name,width);
      t.diagnostic(name+' · '+width+'px');
      const photo=page.locator('img[data-vehicle-custom-photo][data-image-preview]').first();
      await photo.locator('..').scrollIntoViewIfNeeded();await photo.waitFor({state:'visible'});
      const source=await photo.getAttribute('src');await photo.focus();await photo.press('Enter');
      const dialog=page.getByRole('dialog');await dialog.waitFor({state:'visible'});
      assert.ok(await dialog.evaluate(el=>el.getBoundingClientRect().right<=innerWidth));
      assert.equal(await dialog.locator('img').getAttribute('src'),new URL(source,origin).href);
      await page.getByRole('button',{name:'Zoom in',exact:true}).click();assert.equal(await dialog.locator('[data-zoom]').textContent(),'150%');
      await page.getByRole('button',{name:'Fit image',exact:true}).click();assert.equal(await dialog.locator('[data-zoom]').textContent(),'100%');
      await page.keyboard.press('Escape');assert.equal(await page.locator('dialog[open]').count(),0);assert.equal(await photo.evaluate(img=>document.activeElement===img),true);
      await photo.evaluate(img=>img.src='/fixture-updated.svg');await photo.click();
      assert.match(await dialog.locator('img').getAttribute('src'),/fixture-updated\.svg$/,'the viewer uses the latest live photo address');
      await page.getByRole('button',{name:'Close',exact:true}).click();
      const filterImages=page.locator('button img[data-vt-photo], a img[data-vt-photo]');
      assert.equal(await filterImages.evaluateAll(imgs=>imgs.some(img=>img.hasAttribute('data-image-preview'))),false,'filter icons keep filtering');
      assert.deepEqual(errors,[]);await context.close();
    }
  }
});

test('announcement text begins off the right edge, moves left and restarts from the right when changed',{timeout:30000},async t=>{
  const {page,errors}=await open(t,'guest',375);
  // Run the real header sizing code independently of the live announcement poller.
  const original=execFileSync(phpBinary(),[path.join(__dirname,'render-responsive-fixture.php'),'guest','normal','theme'],{encoding:'utf8',maxBuffer:3*1024*1024});
  const script=[...original.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(m=>m[1]).find(s=>s.includes('window.refreshGuestMarquee = rebuild'));
  await page.addScriptTag({content:script});await page.evaluate(()=>document.fonts.ready);
  const positions=await page.locator('#guestMarquee').evaluate(track=>{
    const animation=track.getAnimations().find(a=>a.animationName==='gh-marquee');animation.pause();animation.currentTime=0;
    const viewport=track.parentElement.getBoundingClientRect(),start=track.firstElementChild.getBoundingClientRect();
    animation.currentTime=1000;const next=track.firstElementChild.getBoundingClientRect();
    animation.currentTime=animation.effect.getTiming().duration-1;const end=track.firstElementChild.getBoundingClientRect();
    track.firstElementChild.textContent='A newly published notice';window.refreshGuestMarquee();
    const updated=track.firstElementChild.getBoundingClientRect();
    return {edge:viewport.right,start:start.left,next:next.left,end:end.right,left:viewport.left,updated:updated.left,duplicates:[...track.children].filter(el=>el.getAttribute('aria-hidden')==='true').map(el=>getComputedStyle(el).display)};
  });
  assert.ok(Math.abs(positions.start-positions.edge)<2,JSON.stringify(positions));assert.ok(positions.next<positions.start);assert.ok(positions.end<=positions.left+2);assert.ok(Math.abs(positions.updated-positions.edge)<2);assert.deepEqual(positions.duplicates,['none']);assert.deepEqual(errors,[]);
});

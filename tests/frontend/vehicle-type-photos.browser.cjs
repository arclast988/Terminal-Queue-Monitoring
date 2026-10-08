'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const playwright = require('playwright');
const { root, assetContentType, phpBinary } = require('./harness.cjs');
const engine = process.env.TQ_BROWSER || 'chromium';
const cloudPhoto = 'https://res.cloudinary.com/demo/image/upload/v124/vehicle_types/jeepney.png';
const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" rx="12" fill="#1565c0"/></svg>';
let browser;
test.before(async () => {
  browser = await playwright[engine].launch({headless:true,
    ...(engine === 'chromium' && process.env.TQ_BROWSER_CHANNEL ? {channel:process.env.TQ_BROWSER_CHANNEL} : {}),
    ...(process.env.TQ_BROWSER_EXECUTABLE ? {executablePath:process.env.TQ_BROWSER_EXECUTABLE} : {})});
});
test.after(async () => { await browser?.close(); });

test('edit vehicle-type modal loads saved remote and local photos with an empty replacement picker', {timeout:60000}, async () => {
  async function openEditor(page,id) {
    await page.evaluate(id=>{
      window.photoEditorReady=new Promise(resolve=>document.getElementById('editVehicleTypeModal'+id).addEventListener('shown.bs.modal',()=>resolve(),{once:true}));
      window.openEditVehicleTypeModal(id);
    },id);
    await page.evaluate(()=>window.photoEditorReady);
  }
  const rendered=execFileSync(phpBinary(),[path.join(__dirname,'render-responsive-fixture.php'),'admin-vehicles','normal','theme','vehicle-type-edit-photos','','admin'],{encoding:'utf8',maxBuffer:2*1024*1024});
  const editor=[...rendered.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(m=>m[1]).find(s=>s.includes('window.previewEditVtPhoto'));
  assert.ok(editor,'use the production modal and photo handlers');
  const html=rendered.replace(/<script\b[^>]*>[\s\S]*?<\/script>/g,'')
    .replace('</body>','<script src="/fixture/bootstrap.js"></script><script>'+editor+'</script></body>');
  for (const width of [375,1365]) {
    const context=await browser.newContext({viewport:{width,height:900}});
    try {
      await context.route('**/*',async route=>{
        const url=new URL(route.request().url());
        if(url.href===cloudPhoto || url.pathname==='/fixture.svg') return route.fulfill({status:200,contentType:'image/svg+xml',body:svg});
        if(url.hostname!=='terminal.test') return route.abort();
        if(url.pathname==='/admin-vehicles') return route.fulfill({status:200,contentType:'text/html',body:html});
        if(url.pathname==='/fixture/bootstrap.js') return route.fulfill({status:200,contentType:'text/javascript',body:fs.readFileSync(path.join(__dirname,'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'))});
        const file=path.resolve(root,'public','.'+url.pathname);
        if(!file.startsWith(path.join(root,'public')+path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) return route.abort();
        return route.fulfill({status:200,contentType:assetContentType(file),body:fs.readFileSync(file)});
      });
      const page=await context.newPage(),errors=[];
      page.on('pageerror',e=>errors.push(e.message));
      await page.goto('https://terminal.test/admin-vehicles');
      for(const [id,photo] of [[1,cloudPhoto],[2,'/fixture.svg'],[3,null]]) {
        await openEditor(page,id);
        const modal=page.locator('#editVehicleTypeModal'+id);
        await modal.waitFor({state:'visible'});
        assert.equal(await modal.locator('input[type="file"]').evaluate(input=>input.files.length),0,'a saved photo is not a newly selected file');
        assert.equal(await modal.locator('input[name="remove_photo"]').isChecked(),false);
        for(const prefix of ['edit_vt_photo_preview_','edit_vt_preview_photo_']) {
          const img=modal.locator('#'+prefix+id);
          assert.equal(await img.getAttribute('src'),photo || '','saved photo address');
          if(photo) {
            await img.evaluate(img=>img.decode());
            assert.ok(await img.evaluate(img=>img.naturalWidth>0 && getComputedStyle(img).display!=='none'));
          } else assert.equal(await img.isVisible(),false);
        }
        const before=await modal.locator('form').evaluate(form=>{
          const data=new FormData(form);return {remove:data.has('remove_photo'),selectedName:data.get('photo').name};
        });
        assert.deepEqual(before,{remove:false,selectedName:''},'saving without a replacement must keep the saved photo');
        if(engine==='chromium' && id===1) {
          const dir=path.join(__dirname,'artifacts');fs.mkdirSync(dir,{recursive:true});
          await page.screenshot({path:path.join(dir,'vehicle-type-edit-photo-'+width+'.png')});
        }
        await page.evaluate(()=>{
          window.photoManageReady=new Promise(resolve=>document.getElementById('manageVehicleTypesModal').addEventListener('shown.bs.modal',()=>resolve(),{once:true}));
        });
        await modal.locator('.btn-close').click();
        await modal.waitFor({state:'hidden'});
        await page.evaluate(()=>window.photoManageReady);
      }
      await openEditor(page,1);
      const modal=page.locator('#editVehicleTypeModal1');
      await modal.waitFor({state:'visible'});
      assert.match(await modal.locator('#edit_vt_photo_help_1').textContent(),/Leave empty to keep the current photo/);
      await modal.locator('input[type="file"]').setInputFiles({name:'replacement.webp',mimeType:'image/webp',buffer:fs.readFileSync(path.join(root,'public/images/logo.webp'))});
      await page.waitForFunction(()=>['edit_vt_photo_preview_1','edit_vt_preview_photo_1'].every(id=>{
        const img=document.getElementById(id);return img.src.startsWith('data:image/webp;') && img.complete && img.naturalWidth>0;
      }));
      assert.equal(await modal.locator('input[name="remove_photo"]').isChecked(),false);
      assert.equal(await modal.locator('form').evaluate(form=>new FormData(form).get('photo').name),'replacement.webp');
      await modal.locator('#edit_vt_photo_clear_1').click();
      assert.equal(await modal.locator('input[name="remove_photo"]').isChecked(),true);
      assert.equal(await modal.locator('input[type="file"]').evaluate(input=>input.files.length),0);
      assert.equal(await modal.locator('#edit_vt_photo_preview_1').isVisible(),false);
      assert.equal(await modal.locator('#edit_vt_preview_photo_1').isVisible(),false);
      assert.equal(await modal.locator('#edit_vt_photo_placeholder_1').isVisible(),true);
      assert.deepEqual(errors,[]);
    } finally {await context.close();}
  }
});

for (const name of ['admin-vehicles', 'staff-queue', 'guest']) {
  test(name + ' restores updated type images and preserves individual vehicle photos', {timeout:60000}, async () => {
    const role = name === 'admin-vehicles' ? 'admin' : name === 'staff-queue' ? 'staff' : '';
    const rendered = execFileSync(phpBinary(), [path.join(__dirname,'render-responsive-fixture.php'), name, 'normal', 'theme', 'vehicle-type-photos', '', role], {encoding:'utf8',maxBuffer:2*1024*1024});
    const photoScript = name === 'admin-vehicles' ? 'vehicle-type-live' : 'queue-sync';
    // Keep production markup and styles; isolate image updates from unrelated timers/forms.
    const html = rendered.replace(/<script\b[^>]*>[\s\S]*?<\/script>/g,'')
      .replace('</body>', '<script>window.QueueWS={init:function(c){window.photoHandlers=c;}};</script><script src="/js/' + photoScript + '.js"></script></body>');
    for (const width of [375,1365]) {
      const context = await browser.newContext({viewport:{width,height:900}});
      try {
        await context.route('**/*', async route => {
          const url = new URL(route.request().url());
          if (url.href === cloudPhoto || url.pathname === '/fixture.svg' || url.pathname === '/fixture-custom.svg')
            return route.fulfill({status:200,contentType:'image/svg+xml',body:svg});
          if (url.hostname !== 'terminal.test') return route.abort();
          if (url.pathname === '/' + name) return route.fulfill({status:200,contentType:'text/html',body:html});
          const file = path.resolve(root,'public','.'+url.pathname);
          if (!file.startsWith(path.join(root,'public')+path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) return route.abort();
          return route.fulfill({status:200,contentType:assetContentType(file),body:fs.readFileSync(file)});
        });
        const page = await context.newPage(), errors=[];
        page.on('pageerror', e=>errors.push(e.message));
        await page.goto('https://terminal.test/'+name);
        if (name !== 'admin-vehicles') await page.evaluate(()=>window.QueueSync.init({pollInterval:60000,customRefresh:function(){}}));
        const custom = page.locator('img[data-vehicle-custom-photo="true"]');
        assert.ok(await custom.count() > 0, 'production markup must identify individual photos');
        const customSources = await custom.evaluateAll(imgs=>imgs.map(img=>img.src));
        const defaultSelector = name === 'admin-vehicles'
          ? '#vehicles-table img[data-vt-photo="jeepney"]' : 'img[data-vt-photo="jeepney"]';
        const defaults = page.locator(defaultSelector);
        assert.ok(await defaults.count() > 0, 'production type images must exist');
        await defaults.evaluateAll(imgs=>imgs.forEach(img=>{
          img.style.display='none';
          if (img.nextElementSibling && img.nextElementSibling.matches('i.fas, i.bi')) img.nextElementSibling.style.display='inline-flex';
        }));
        await page.evaluate(({cloudPhoto,name})=>{
          const data={new_slug:'jeepney',photo:cloudPhoto,icon:'fa-bus',color:'#1565c0',colors:{jeepney:{photo:cloudPhoto,icon:'fa-bus',color:'#1565c0'}}};
          const handler=name==='admin-vehicles' ? window.photoHandlers.onVehicleTypeUpdate : window.photoHandlers.onQueueUpdate;
          handler({type:'vehicle_type_update',data});
        }, {cloudPhoto,name});
        await page.waitForFunction(({selector,photo})=>[...document.querySelectorAll(selector)].every(img=>img.src===photo && img.complete && img.naturalWidth>0 && getComputedStyle(img).display!=='none'), {selector:defaultSelector,photo:cloudPhoto});
        assert.deepEqual(await custom.evaluateAll(imgs=>imgs.map(img=>img.src)),customSources,'type upload must preserve individual photos');
        assert.equal(await defaults.evaluateAll(imgs=>imgs.every(img=>!img.nextElementSibling || !img.nextElementSibling.matches('i.fas, i.bi') || getComputedStyle(img.nextElementSibling).display==='none')),true);
        if (engine === 'chromium' && width === 1365) {
          const dir=path.join(__dirname,'artifacts'); fs.mkdirSync(dir,{recursive:true});
          await defaults.first().scrollIntoViewIfNeeded();
          await page.screenshot({path:path.join(dir,'vehicle-type-photo-'+name+'.png')});
        }
        await page.evaluate(name=>{
          const data={new_slug:'jeepney',photo:null,colors:{jeepney:{photo:null,icon:'fa-bus',color:'#1565c0'}}};
          const handler=name==='admin-vehicles' ? window.photoHandlers.onVehicleTypeUpdate : window.photoHandlers.onQueueUpdate;
          handler({type:'vehicle_type_update',data});
        },name);
        assert.deepEqual(await custom.evaluateAll(imgs=>imgs.map(img=>img.src)),customSources,'type removal must preserve individual photos');
        assert.equal(await page.locator(defaultSelector).count(),0,'type removal restores the icon');
        assert.deepEqual(errors,[]);
      } finally { await context.close(); }
    }
  });
}

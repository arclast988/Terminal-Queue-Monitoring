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

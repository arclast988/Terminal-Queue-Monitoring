'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const playwright = require('playwright');
const { root, assetContentType, phpBinary } = require('./harness.cjs');
let browser, server, origin;
const engine = process.env.TQ_BROWSER || 'chromium';
const cache = new Map();
function fixture(name) {
  if (!cache.has(name)) {
    const state = name === 'staff-queue' ? 'many' : name === 'admin-vehicles' ? 'dispatch-register' : '';
    let html = execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme', state], {encoding:'utf8', maxBuffer:3*1024*1024});
    html = html.replace('</head>', '<script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script><script>window.QueueWS={_isLoaded:true,init:function(){},isConnected:function(){return true}};</script></head>');
    cache.set(name, html);
  }
  return cache.get(name);
}
test.before(async () => {
  browser = await playwright[engine].launch({headless:true, ...(engine === 'chromium' && process.env.TQ_BROWSER_CHANNEL ? {channel:process.env.TQ_BROWSER_CHANNEL} : {})});
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname.startsWith('/view/')) {res.setHeader('Content-Type','text/html'); return res.end(fixture(url.pathname.slice(6)));}
    if (url.pathname === '/fixture.svg') {res.setHeader('Content-Type','image/svg+xml');return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" fill="#cbd5e1"/></svg>');}
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(root,'public') + path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) {res.statusCode=404;return res.end();}
    res.setHeader('Content-Type',assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve)); origin='http://127.0.0.1:'+server.address().port;
});
test.after(async()=>{await browser?.close();server?.closeAllConnections();await new Promise(resolve=>server?.close(resolve));});

test('shared text and controls remain readable and fit phone, tablet and desktop screens', {timeout:180000}, async t => {
  const failures=[];
  for (const width of [320,375,768,1366,1920]) {
    for (const name of ['staff-queue','admin-vehicles','admin-dashboard','admin-route-create','guest','login']) {
      const page=await browser.newPage({viewport:{width,height:900},reducedMotion:'reduce'});
      try {
        await page.route('**/*', route=>new URL(route.request().url()).origin===origin ? route.continue() : route.abort());
        await page.goto(origin+'/view/'+name); await page.evaluate(()=>document.fonts.ready);
        const sizes=await page.evaluate(()=>{
          const visible=el=>{const b=el.getBoundingClientRect();return b.width>0 && b.height>0 && getComputedStyle(el).visibility!=='hidden';};
          return [...document.querySelectorAll('.main-content .btn-modern, #queueRouteControls button:not(.queue-route-chip), .stat-card-link, #site-header .nav-hamburger-btn, #site-header .profile-trigger-btn')].filter(visible).map(el=>({text:el.textContent.trim(),font:parseFloat(getComputedStyle(el).fontSize),height:el.getBoundingClientRect().height,left:el.getBoundingClientRect().left,right:el.getBoundingClientRect().right,scroll:el.scrollWidth,client:el.clientWidth}));
        });
        for (const size of sizes) {
          assert.ok(size.font>=15 || !size.text, `${name} ${width} small text: ${JSON.stringify(size)}`);
          assert.ok(size.height>=43.5, `${name} ${width} small target: ${JSON.stringify(size)}`);
          assert.ok(size.left>=-1 && size.right<=width+1, `${name} ${width} clipped button: ${JSON.stringify(size)}`);
          assert.ok(size.scroll<=size.client+1, `${name} ${width} clipped label: ${JSON.stringify(size)}`);
        }
        if (name==='staff-queue') {
          for (const chip of await page.locator('.queue-route-chip').all()) {
            await chip.scrollIntoViewIfNeeded();
            assert.ok(await chip.evaluate(el=>parseFloat(getComputedStyle(el).fontSize)>=16 && el.getBoundingClientRect().height>=44));
            const bounds=await chip.boundingBox(); assert.ok(bounds.x>=-1 && bounds.x+bounds.width<=width+1,`route filter ${width} cannot scroll into view`);
          }
          await page.evaluate(()=>scrollTo(0,0));
          const change=page.locator('#card-101 [data-action="change-driver"]');
          assert.ok(await change.evaluate(el=>parseFloat(getComputedStyle(el).fontSize)>=16));
          assert.ok(await page.locator('#card-101 .small').first().evaluate(el=>parseFloat(getComputedStyle(el).fontSize)>=16));
        }
        if (name==='admin-dashboard') assert.ok(await page.locator('.stat-card-label').first().evaluate(el=>parseFloat(getComputedStyle(el).fontSize)>=15));
        if (name==='admin-vehicles') assert.ok(await page.locator('#vehicle-search').evaluate(el=>parseFloat(getComputedStyle(el).fontSize)>=16));
        if (name==='admin-route-create') assert.ok(await page.locator('label').first().evaluate(el=>parseFloat(getComputedStyle(el).fontSize)>=15));
        if (['staff-queue','admin-vehicles','admin-dashboard'].includes(name)) {
          await page.locator('.profile-trigger-btn').click();
          const entries=page.locator('.profile-dropdown-card .profile-dropdown-item');
          for(const entry of await entries.all()) assert.ok(await entry.evaluate(el=>parseFloat(getComputedStyle(el).fontSize)>=16 && el.getBoundingClientRect().height>=44));
          const menu=await page.locator('.profile-dropdown-card').boundingBox();
          assert.ok(menu.x>=-1 && menu.x+menu.width<=width+1, `${name} ${width} profile clipped`);
          await page.keyboard.press('Escape');
        }
        assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),`${name} ${width} horizontal overflow`);
        if ([375,1366].includes(width) && ['staff-queue','admin-vehicles','admin-dashboard'].includes(name)) {
          const dir=path.join(__dirname,'artifacts');fs.mkdirSync(dir,{recursive:true});
          await page.screenshot({path:path.join(dir,`readability-${name}-${width}.png`)});
        }
      } catch(error) {failures.push(error.message);} finally {await page.close();}
    }
  }
  assert.deepEqual(failures,[]);
});

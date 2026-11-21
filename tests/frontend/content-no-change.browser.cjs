'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const {execFileSync} = require('node:child_process');
const playwright = require('playwright');
const {root, assetContentType, phpBinary} = require('./harness.cjs');
const engine = process.env.TQ_BROWSER || 'chromium';
let browser, server, origin;
const fixtures = new Map();

test.before(async () => {
  for (const state of ['', 'content-no-change']) fixtures.set(state, execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), 'admin-content', 'normal', 'theme', state], {encoding:'utf8', maxBuffer:3*1024*1024}));
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname === '/content') {res.setHeader('Content-Type','text/html'); return res.end(fixtures.get(url.searchParams.get('notice') ? 'content-no-change' : ''));}
    if (url.pathname === '/fixture.svg') {res.setHeader('Content-Type','image/svg+xml'); return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" fill="#cbd5e1"/></svg>');}
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(root,'public') + path.sep) || !fs.existsSync(file) || !fs.statSync(file).isFile()) {res.statusCode=404; return res.end();}
    res.setHeader('Content-Type',assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0,'127.0.0.1',resolve)); origin = 'http://127.0.0.1:' + server.address().port;
  browser = await playwright[engine].launch({headless:true, ...(engine==='chromium' && process.env.TQ_BROWSER_CHANNEL ? {channel:process.env.TQ_BROWSER_CHANNEL} : {})});
});
test.after(async () => {await browser?.close(); server?.closeAllConnections(); await new Promise(resolve => server?.close(resolve));});
async function open(t, width, query='', javaScriptEnabled=true) {
  const context = await browser.newContext({viewport:{width,height:900},reducedMotion:'reduce',javaScriptEnabled});
  t.after(() => context.close());
  await context.route('**/*',route => route.request().url().startsWith(origin) ? route.continue() : route.abort());
  const page = await context.newPage(); const errors=[]; page.on('pageerror',error => errors.push(error.message));
  await page.goto(origin + '/content' + query);
  if (javaScriptEnabled) await page.waitForFunction(() => document.querySelector('#managedContentForm')?.hasAttribute('data-nc-attached'));
  return {page, errors};
}

test('unchanged Content Manager saves use the standard popup without posting, while real edits still submit', {timeout:45000}, async t => {
  for (const width of [375,1366]) {
    const {page,errors} = await open(t,width); const posts=[];
    await page.route('**/admin/settings/content', async route => {
      posts.push(new URLSearchParams(route.request().postData()));
      await route.fulfill({contentType:'text/html',body:'<!doctype html><title>Saved</title><p>Saved content</p>'});
    });
    await page.locator('#managedContentForm button[type="submit"]').click();
    const modal = page.locator('#noChangeGuardModal.show'); await modal.waitFor({state:'visible'});
    assert.match(await modal.innerText(),/No Changes Detected/);
    assert.match(await modal.innerText(),/You didn't change anything, so nothing was updated/);
    assert.equal(posts.length,0);
    assert.equal(await page.locator('.alert-modern-info').count(),0);
    if (engine === 'chromium' && width === 375) {
      fs.mkdirSync(path.join(__dirname,'artifacts'),{recursive:true});
      await page.screenshot({path:path.join(__dirname,'artifacts','content-no-changes-popup-375.png')});
    }
    await modal.getByRole('button',{name:'Got it'}).click(); await modal.waitFor({state:'hidden'});
    await page.locator('#content-contact summary').click();
    await page.locator('#content_contact_intro').fill('Updated support introduction.');
    await page.locator('#managedContentForm button[type="submit"]').click();
    await page.waitForURL('**/admin/settings/content');
    assert.equal(posts.length,1); assert.equal(posts[0].get('content_contact_intro'),'Updated support introduction.');
    assert.equal(posts[0].get('csrf_fixture'),'unchanged'); assert.deepEqual(errors,[]);
  }
});

test('server-detected no changes reuse the popup once and keep a readable native fallback', {timeout:30000}, async t => {
  const {page,errors} = await open(t,375,'?notice=1');
  const modal = page.locator('#noChangeGuardModal.show'); await modal.waitFor({state:'visible'});
  assert.equal(await page.locator('#noChangeGuardModal').count(),1);
  assert.equal(await page.locator('.alert-modern-info').count(),0);
  await modal.getByRole('button',{name:'Got it'}).click(); await modal.waitFor({state:'hidden'});
  assert.equal(await page.locator('[data-no-change-notice]').count(),0); assert.deepEqual(errors,[]);
  const native = await open(t,375,'?notice=1',false);
  assert.match(await native.page.locator('.alert-modern-info').innerText(),/No changes detected/);
  assert.equal(await native.page.locator('#noChangeGuardModal').count(),0);
});

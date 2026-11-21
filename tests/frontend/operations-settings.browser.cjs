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
let browser, server, origin, html;

function submitted(request) {
  const body = request.postData() || '';
  if (!(request.headers()['content-type'] || '').includes('multipart/form-data')) return Object.fromEntries(new URLSearchParams(body));
  return Object.fromEntries([...body.matchAll(/name="([^"]+)"\r\n\r\n([\s\S]*?)(?=\r\n--)/g)].map(match => [match[1], match[2]]));
}

test.before(async () => {
  const rendered = execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), 'admin-settings', 'normal', 'theme'], {encoding:'utf8', maxBuffer:3*1024*1024});
  const scripts = [...rendered.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script>/g)].filter(match => {
    const type = match[1].match(/\btype\s*=\s*["']([^"']+)["']/i);
    return !type || /^(?:module|(?:text|application)\/(?:java|ecma)script)$/i.test(type[1]);
  }).map(match => match[2]).filter(Boolean);
  html = rendered.replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, '')
    .replace(/https:\/\/cdn\.jsdelivr\.net\/npm\/bootstrap@5\.3\.0\/dist\/css\/bootstrap\.min\.css/g, '/fixture/bootstrap.css')
    .replace('</body>', '<script src="/fixture/bootstrap.js"></script>' + scripts.map(script => '<script>' + script + '</script>').join('') + '<script src="/assets/js/global-loader.js"></script></body>');
  server = http.createServer((req, res) => {
    const pathname = new URL(req.url, 'http://fixture').pathname;
    if (pathname === '/settings') {res.setHeader('Content-Type', 'text/html'); return res.end(html);}
    if (pathname === '/fixture.svg') {res.setHeader('Content-Type', 'image/svg+xml'); return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" fill="#cbd5e1"/></svg>');}
    if (pathname.startsWith('/fixture/bootstrap.')) {
      const css = pathname.endsWith('.css'); res.setHeader('Content-Type', css ? 'text/css' : 'application/javascript');
      return res.end(fs.readFileSync(path.join(__dirname, 'node_modules/bootstrap/dist', css ? 'css/bootstrap.min.css' : 'js/bootstrap.bundle.min.js')));
    }
    const file = path.resolve(root, 'public', '.' + pathname);
    if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file) || !fs.statSync(file).isFile()) {res.statusCode = 404; return res.end();}
    res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve)); origin = 'http://127.0.0.1:' + server.address().port;
  browser = await playwright[engine].launch({headless:true, ...(engine === 'chromium' && process.env.TQ_BROWSER_CHANNEL ? {channel:process.env.TQ_BROWSER_CHANNEL} : {})});
});

test.after(async () => {await browser?.close(); server?.closeAllConnections(); await new Promise(resolve => server?.close(resolve));});

async function open(t, width = 375, javaScriptEnabled = true) {
  const context = await browser.newContext({viewport:{width,height:900}, reducedMotion:'reduce', javaScriptEnabled});
  t.after(() => context.close());
  await context.route('**/*', route => route.request().url().startsWith(origin) ? route.continue() : route.abort());
  if (!javaScriptEnabled) await context.route('**/settings', route => route.fulfill({
    contentType:'text/html', body:html.replace('</head>', '<style>#tab-operations{display:block!important}</style></head>')
  }));
  const page = await context.newPage(); const errors = []; page.on('pageerror', error => errors.push(error.message));
  await page.goto(origin + '/settings#operations');
  await page.locator('#formOperations').waitFor({state:'visible', timeout:10000});
  return {page, errors};
}

function button(page, section) {return page.locator('#formOperations button[name="operations_section"][value="' + section + '"]');}
async function save(page, section, posts) {
  const count = posts.length; await button(page, section).click();
  await page.waitForFunction(count => window.fixtureOperationsPosts >= count + 1, count);
  await page.waitForFunction(() => document.querySelector('#formOperations').dataset.saving !== '1');
  assert.equal(posts.length, count + 1); return posts[count];
}
async function mock(page, posts, response = {}) {
  await page.evaluate(() => {window.fixtureOperationsPosts = 0;});
  await page.route('**/admin/settings/update-operations', async route => {
    posts.push(submitted(route.request()));
    await route.fulfill({contentType:'application/json', body:JSON.stringify({success:true, csrf_token:'csrf_fixture', csrf_hash:'token-' + posts.length, ...response})});
    await page.evaluate(() => {window.fixtureOperationsPosts++;});
  });
}

test('four card saves remain independent without a redundant Save All button', {timeout:90000}, async t => {
  for (const width of [375,1366]) {
    const {page, errors} = await open(t, width); const posts = []; await mock(page, posts);
    assert.equal(await page.locator('#btnSaveOperations, #formOperations button[value="all"]').count(), 0);
    assert.deepEqual(await page.locator('#formOperations button[name="operations_section"]').evaluateAll(buttons => buttons.map(button => button.value)), ['cooldown', 'daily_clear', 'audit_retention', 'departure_retention']);
    await page.locator('#inputCooldownMinutes').fill('45');
    await page.locator('#inputLogRetention').fill('0'); await page.locator('#inputDepRetention').fill('0');
    await page.locator('#inputQueueResetTime').fill('13:05');
    assert.deepEqual(await save(page, 'cooldown', posts), {csrf_fixture:'unchanged', vehicle_cooldown_minutes:'45', operations_section:'cooldown'});
    assert.equal(await page.locator('#inputQueueResetTime').inputValue(), '13:05');
    assert.equal(await page.locator('#inputLogRetention').inputValue(), '0');

    await page.locator('#inputCooldownMinutes').fill('-1');
    await page.locator('#inputQueueResetEnabled').uncheck(); await page.locator('#inputQueueResetTime').fill('13:05');
    assert.deepEqual(await save(page, 'daily_clear', posts), {csrf_fixture:'token-1', queue_reset_configured:'1', queue_reset_enabled:'0', queue_reset_time:'13:05', operations_section:'daily_clear'});
    assert.equal(await page.locator('#inputCooldownMinutes').inputValue(), '-1');

    await page.locator('#inputLogRetention').fill('7');
    assert.deepEqual(await save(page, 'audit_retention', posts), {csrf_fixture:'token-2', log_retention_days:'7', operations_section:'audit_retention'});
    await page.locator('#inputDepRetention').fill('9');
    assert.deepEqual(await save(page, 'departure_retention', posts), {csrf_fixture:'token-3', departure_retention_days:'9', operations_section:'departure_retention'});

    assert.deepEqual(errors, []);
  }
});

test('Enter and a submit event without a submitter save the focused card only', {timeout:60000}, async t => {
  const {page, errors} = await open(t); const posts = []; await mock(page, posts);
  await page.locator('#inputCooldownMinutes').fill('-1');
  await page.locator('#inputLogRetention').fill('90');
  await page.locator('#inputLogRetention').press('Enter');
  await page.waitForFunction(() => window.fixtureOperationsPosts === 1);
  assert.deepEqual(posts[0], {csrf_fixture:'unchanged', log_retention_days:'90', operations_section:'audit_retention'});
  await page.locator('#inputDepRetention').fill('120');
  await page.locator('#inputDepRetention').focus();
  await page.locator('#formOperations').evaluate(form => form.dispatchEvent(new Event('submit', {bubbles:true,cancelable:true})));
  await page.waitForFunction(() => window.fixtureOperationsPosts === 2);
  assert.deepEqual(posts[1], {csrf_fixture:'token-1', departure_retention_days:'120', operations_section:'departure_retention'});
  assert.equal(await page.locator('#inputCooldownMinutes').inputValue(), '-1');
  assert.deepEqual(errors, []);
});

test('selected invalid values block submission and server failures preserve every draft for retry', {timeout:60000}, async t => {
  const {page, errors} = await open(t); const posts = []; await mock(page, posts, {success:false, message:'Please try again.'});
  await page.locator('#inputCooldownMinutes').fill('-1'); await page.locator('#inputLogRetention').fill('120');
  await button(page, 'cooldown').click();
  assert.equal(posts.length, 0);
  assert.equal(await page.locator('#inputCooldownMinutes').evaluate(input => input.validity.rangeUnderflow), true);
  await page.locator('#inputCooldownMinutes').fill('45'); await save(page, 'cooldown', posts);
  assert.equal(await page.locator('#inputCooldownMinutes').inputValue(), '45');
  assert.equal(await page.locator('#inputLogRetention').inputValue(), '120');
  assert.equal(await button(page, 'cooldown').isEnabled(), true);
  assert.match(await page.locator('#sbToastMsg').innerText(), /Please try again/);
  assert.deepEqual(errors, []);
});

test('a failed AJAX request keeps the selected card scope during native fallback', {timeout:60000}, async t => {
  const {page} = await open(t); const requests = [];
  await page.route('**/admin/settings/update-operations', async route => {
    requests.push({ajax:route.request().headers()['x-requested-with'], body:submitted(route.request())});
    if (requests.length === 1) return route.abort('failed');
    return route.fulfill({contentType:'text/html', body:'<!doctype html><title>Saved</title><p>Card saved</p>'});
  });
  await page.locator('#inputCooldownMinutes').fill('-1'); await page.locator('#inputQueueResetTime').fill('13:05');
  await button(page, 'daily_clear').click(); await page.waitForURL('**/admin/settings/update-operations');
  assert.equal(requests.length, 2);
  assert.equal(requests[0].ajax, 'XMLHttpRequest'); assert.equal(requests[1].ajax, undefined);
  assert.equal(requests[1].body.operations_section, 'daily_clear');
  assert.equal(requests[1].body.queue_reset_time, '13:05'); assert.equal(requests[1].body.csrf_fixture, 'unchanged');
});

test('native per-card submitters work without JavaScript despite unrelated invalid fields', {timeout:60000}, async t => {
  const {page} = await open(t, 375, false); const posts = [];
  await page.route('**/admin/settings/update-operations', async route => {
    posts.push(submitted(route.request())); return route.fulfill({contentType:'text/html', body:'<!doctype html><title>Saved</title><p>Card saved</p>'});
  });
  await page.locator('#inputCooldownMinutes').fill('-1'); await page.locator('#inputLogRetention').fill('90');
  await button(page, 'audit_retention').click(); await page.waitForURL('**/admin/settings/update-operations');
  assert.equal(posts.length, 1); assert.equal(posts[0].operations_section, 'audit_retention');
  assert.equal(posts[0].log_retention_days, '90'); assert.equal(posts[0].csrf_fixture, 'unchanged');
});

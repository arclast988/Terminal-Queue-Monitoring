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
function fixture(name, state) {
  const key = name + ':' + state;
  if (!fixtures.has(key)) fixtures.set(key, execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme', state], {encoding:'utf8', maxBuffer:2*1024*1024}));
  return fixtures.get(key);
}
test.before(async () => {
  browser = await playwright[engine].launch({headless:true, ...(engine === 'chromium' && process.env.TQ_BROWSER_CHANNEL ? {channel:process.env.TQ_BROWSER_CHANNEL} : {})});
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname.startsWith('/view/')) {
      res.setHeader('Content-Type','text/html');
      return res.end(fixture(url.pathname.slice(6), url.searchParams.get('state')));
    }
    if (url.pathname === '/fixture.svg') {
      res.setHeader('Content-Type','image/svg+xml');
      return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60"><rect width="60" height="60" fill="#15803d"/></svg>');
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(root,'public') + path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) {res.writeHead(404); return res.end();}
    res.setHeader('Content-Type',assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0,'127.0.0.1',resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
});
test.after(async () => {
  await browser?.close(); server?.closeAllConnections();
  await new Promise(resolve => server?.close(resolve));
});

test('route create and edit reject the same origin immediately and re-filter suggestions when the terminal changes', {timeout:90000}, async t => {
  for (const name of ['admin-route-create','admin-route-edit']) for (const width of [375,1366]) {
    const page = await browser.newPage({viewport:{width,height:900},reducedMotion:'reduce'});
    t.after(() => page.close());
    const errors = []; page.on('pageerror',error => errors.push(error.message));
    await page.route('**/*',route => route.request().url().startsWith(origin) ? route.continue() : route.abort());
    await page.goto(origin + '/view/' + name + '?state=route-origin-multiple');
    const destination = page.locator('#destination');
    if (name === 'admin-route-edit') {
      assert.equal(await destination.inputValue(),'ORMOC');
      assert.equal(await destination.evaluate(input => input.validity.customError),true,'an existing same-place route is visibly invalid on load');
    }
    await page.locator('#terminal_id').selectOption('1',{force:true});
    await page.locator('.vt-checkbox').first().check();
    await page.locator('#destination').evaluate(input => {
      window.__routeSubmits = 0;
      input.form.addEventListener('submit',event => {event.preventDefault(); window.__routeSubmits++;});
    });
    await destination.fill('  oRmOc  ');
    assert.equal(await destination.evaluate(input => input.validity.customError),true);
    assert.match(await page.locator('#routeDestinationError').innerText(),/different from the origin/);
    assert.equal(await destination.getAttribute('aria-invalid'),'true');
    await page.locator('button[type="submit"]').first().click();
    assert.equal(await page.evaluate(() => window.__routeSubmits),0,'native validation blocks the invalid request');

    await destination.fill(''); await destination.focus();
    await page.waitForFunction(() => document.querySelector('#destination').closest('.autocomplete-wrapper').querySelector('.autocomplete-dropdown').style.display !== 'none');
    let values = await page.locator('#destination').evaluate(input => [...input.closest('.autocomplete-wrapper').querySelectorAll('.autocomplete-item')].map(item => item.dataset.value));
    assert.ok(!values.includes('ORMOC'));
    assert.ok(values.includes('Tacloban City'));
    await destination.fill('  oRmOc  ');
    const enteredDestination = await destination.inputValue();
    assert.equal(enteredDestination,'  ORMOC  ','the existing uppercase input behavior remains enabled');
    await page.locator('#terminal_id').selectOption('2',{force:true});
    assert.equal(await destination.inputValue(),enteredDestination,'changing the terminal preserves the entered destination');
    assert.equal(await destination.evaluate(input => input.validity.customError),false);
    assert.equal(await page.locator('#routeDestinationError').isVisible(),false);
    await destination.fill(''); await destination.focus();
    await page.waitForFunction(() => {
      const values = [...document.getElementById('destination').closest('.autocomplete-wrapper').querySelectorAll('.autocomplete-item')].map(item => item.dataset.value);
      return values.includes('ORMOC') && !values.includes('Tacloban City');
    });
    values = await destination.evaluate(input => [...input.closest('.autocomplete-wrapper').querySelectorAll('.autocomplete-item')].map(item => item.dataset.value));
    assert.ok(values.includes('ORMOC'));
    assert.ok(!values.includes('Tacloban City'));
    await destination.fill(' tacloban\u00a0 CITY ');
    assert.equal(await destination.evaluate(input => input.validity.customError),true,'case and Unicode spacing match the selected origin');
    await destination.fill('VILLABA');
    assert.equal(await destination.evaluate(input => input.validity.valid),true);
    await page.locator('button[type="submit"]').first().click();
    assert.equal(await page.evaluate(() => window.__routeSubmits),1);
    assert.deepEqual(errors,[]);
    await page.close();
  }
});

test('single-terminal routes reject the same destination without changing its text', {timeout:15000}, async t => {
  for (const name of ['admin-route-create','admin-route-edit']) {
    const page = await browser.newPage({viewport:{width:375,height:900},reducedMotion:'reduce'});
    t.after(() => page.close());
    await page.goto(origin + '/view/' + name + '?state=route-origin-single');
    assert.equal(await page.locator('#terminal_id').getAttribute('type'),'hidden');
    await page.locator('#destination').fill(' ormoc ');
    assert.equal(await page.locator('#destination').inputValue(),' ORMOC ');
    assert.equal(await page.locator('#destination').evaluate(input => input.validity.customError),true);
    await page.locator('#destination').fill('VILLABA');
    assert.equal(await page.locator('#destination').evaluate(input => input.validity.customError),false);
    await page.close();
  }
});

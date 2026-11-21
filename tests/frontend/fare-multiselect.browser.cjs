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
let browser, server, origin;
let publicFarePayload = farePayload();

function farePayload(legacy = false) {
  const discounts = [
    {id:1,terminal_id:3,type:'pwd',label:'PWD Discount',discount_percent:20,is_active:1},
    {id:2,terminal_id:3,type:'student',label:'Student Discount',discount_percent:15,is_active:1},
    {id:3,terminal_id:3,type:'senior_citizen',label:'Senior Citizen Discount',discount_percent:20,is_active:1},
  ];
  const routes_by_type = {};
  const vehicle_types = [{id:1,slug:'van',name:'VAN',color:'#c62828',icon:'fa-van-shuttle',photo:null},
    {id:2,slug:'minibus',name:'MINIBUS',color:'#2e7d32',icon:'fa-bus',photo:null}];
  vehicle_types.forEach((type,index)=>{
    routes_by_type[type.slug]=Object.entries({ISABEL:150,ORMOC:80,PALOMPON:100}).map(([destination,fare],routeIndex)=>({
      id:routeIndex+1+index*3,terminal_id:3,vehicle_type:type.slug,origin:'VILLABA',destination,fare,
      discounted_fares:legacy && destination!=='PALOMPON'?{}:Object.fromEntries(discounts.map(discount=>[discount.type,{
        label:discount.label,discount_percent:discount.discount_percent,amount:fare*(1-discount.discount_percent/100),
      }])),
    }));
  });
  return {success:true,vehicle_types,routes_by_type,discounts,van_routes:routes_by_type.van,minibus_routes:routes_by_type.minibus};
}

test.before(async () => {
  browser = await playwright[engine].launch({ headless: true, ...(process.env.TQ_BROWSER_CHANNEL ? { channel: process.env.TQ_BROWSER_CHANNEL } : {}) });
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    if (url.pathname === '/api/fares') {
      res.setHeader('Content-Type','application/json');return res.end(JSON.stringify(publicFarePayload));
    }
    if (url.pathname === '/public-fares') {
      const html = execFileSync(phpBinary(), [path.join(__dirname,'render-responsive-fixture.php'),'fares','normal','theme',url.searchParams.get('state') || 'route-discounts'], {encoding:'utf8',maxBuffer:2*1024*1024});
      res.setHeader('Content-Type','text/html');return res.end(html.replace('</head>','<script>window.QueueWS={_isLoaded:true,init:function(){},updateConfig:function(){},isConnected:function(){return false}};</script></head>'));
    }
    if (url.pathname === '/fares') {
      const html = execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), 'admin-fares', 'normal', 'theme', url.searchParams.get('state') || '', 'single', url.searchParams.get('role') || 'admin'], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
      res.setHeader('Content-Type', 'text/html');
      return res.end(html.replace('</head>', '<script>window.QueueWS={_isLoaded:true,init:function(){},updateConfig:function(){},isConnected:function(){return false}};</script></head>'));
    }
    if (url.pathname === '/fixture.svg') {
      res.setHeader('Content-Type', 'image/svg+xml');
      return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><circle cx="32" cy="32" r="28" fill="#1565c0"/></svg>');
    }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.statusCode = 404; return res.end(); }
    res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
});

test.after(async () => {
  await browser?.close();
  server?.closeAllConnections();
  await new Promise(resolve => server?.close(resolve));
});

async function open(t, width, query = '') {
  const context = await browser.newContext({ viewport: { width, height: 900 } });
  t.after(() => context.close());
  await context.route('**/*', route => route.request().url().startsWith(origin) || route.request().url().startsWith('data:') ? route.continue() : route.abort());
  await context.addInitScript(() => {
    document.addEventListener('shown.bs.modal', event => { event.target.dataset.fixtureShown = '1'; });
    document.addEventListener('hidden.bs.modal', event => { delete event.target.dataset.fixtureShown; });
  });
  const page = await context.newPage();
  const errors = []; page.on('pageerror', error => errors.push(error.message));
  await page.goto(origin + '/fares' + query);
  return { page, context, errors };
}

async function shown(page, id) {
  await page.waitForFunction(id => document.getElementById(id).dataset.fixtureShown === '1', id);
}

async function picked(page) {
  return page.locator('#editFareForm input[name="vehicle_types[]"]:checked').evaluateAll(inputs => inputs.map(input => input.value));
}

async function toggle(page, type) {
  const input = page.locator('#edit_vt_' + type);
  const checked = !(await input.isChecked());
  await page.locator('#editFareForm label').filter({ has: page.locator('#edit_vt_' + type) }).click();
  try {
    await page.waitForFunction(({ id, checked }) => document.getElementById(id).checked === checked,
      { id: 'edit_vt_' + type, checked }, { timeout: 3000 });
  } catch (error) {
    const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
    await page.screenshot({ path: path.join(dir, `${engine}-fare-toggle-${page.viewportSize().width}-${type}.png`), fullPage: true });
    throw error;
  }
}

test('Add Discount saves the label, percentage, and terminal without an internal type field', { timeout: 30000 }, async t => {
  for (const [width, query, terminal] of [[375, '', '1'], [1280, '?role=super_admin&state=fare-add-duplicate-terminals', '2']]) {
    const { page, context, errors } = await open(t, width, query);
    await page.locator('[data-bs-target="#addDiscountModal"]').click();
    await shown(page, 'addDiscountModal');
    const form = page.locator('#addDiscountForm');
    assert.deepEqual(await form.locator('label').allTextContents(), ['Terminal', 'Discount Label', 'Discount Percentage (%)']);
    assert.equal(await form.locator('[name="type"], #add_disc_type').count(), 0);
    assert.equal(await form.getAttribute('method'), 'post');
    assert.equal(await form.getAttribute('action'), '/admin/routes/discounts/store');
    if (terminal === '2') await form.locator('[name="terminal_id"]').selectOption(terminal);
    await form.locator('[name="label"]').fill('PWD Discount');
    await form.locator('[name="discount_percent"]').fill('20.50');
    assert.equal(await form.evaluate(el => el.checkValidity()), true);
    await form.evaluate(el => {
      window.fixtureDiscountPayload = null;
      el.addEventListener('submit', event => {
        event.preventDefault();
        window.fixtureDiscountPayload = Object.fromEntries(new FormData(el));
      });
    });
    await form.locator('button[type="submit"]').click();
    assert.deepEqual(await page.evaluate(() => window.fixtureDiscountPayload), {
      csrf_fixture: 'unchanged', form_action: 'create_discount', terminal_id: terminal, label: 'PWD Discount', discount_percent: '20.50',
    });
    const bounds = await page.locator('#addDiscountModal .modal-content').boundingBox();
    assert.ok(bounds.x >= -1 && bounds.x + bounds.width <= width + 1);
    const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
    await page.screenshot({ path: path.join(dir, `add-discount-${width}.png`) });
    assert.deepEqual(errors, []);
    await context.close();
  }
});

test('a failed Add Discount submission reopens only its form with the error and entered values', { timeout: 30000 }, async t => {
  const { page, errors } = await open(t, 375, '?state=discount-add-error');
  await shown(page, 'addDiscountModal');
  assert.equal(await page.locator('#addFareModal').isVisible(), false);
  assert.equal(await page.locator('#editFareModal').isVisible(), false);
  assert.match(await page.locator('#addDiscountModal [role="alert"]').textContent(), /A discount named "PWD Discount" already exists/);
  assert.equal(await page.locator('#addDiscountForm [name="terminal_id"]').inputValue(), '2');
  assert.equal(await page.locator('#add_disc_label').inputValue(), 'PWD Discount');
  assert.equal(await page.locator('#add_disc_percent').inputValue(), '20.50');
  assert.equal(await page.locator('#addDiscountForm [name="type"]').count(), 0);
  assert.deepEqual(errors, []);
});

test('edit fare allows several types, validates an empty selection, and submits every checked type', { timeout: 45000 }, async t => {
  for (const [width, role] of [[375, 'admin'], [1280, 'super_admin']]) {
    const { page, context, errors } = await open(t, width, '?role=' + role);
    await page.locator('[data-bs-target="#editFareModal"][data-id="1"]').click();
    await shown(page, 'editFareModal');
    assert.deepEqual(await picked(page), ['minibus']);
    assert.match(await page.locator('#editFareVehicleTypesHelp').textContent(), /selected vehicle types/);
    await toggle(page, 'van');
    assert.deepEqual(await picked(page), ['minibus', 'van']);
    await toggle(page, 'minibus');
    assert.deepEqual(await picked(page), ['van'], 'deselecting the original type must preserve another selection');
    await toggle(page, 'van');
    const form = page.locator('#editFareForm');
    await form.evaluate(el => {
      window.fixtureFarePayloads = [];
      window.fixtureFareInvalidReports = [];
      el.addEventListener('invalid', event => {
        setTimeout(() => window.fixtureFareInvalidReports.push({ name: event.target.name, prevented: event.defaultPrevented }), 0);
      }, true);
      el.addEventListener('submit', event => {
        event.preventDefault();
        const data = new FormData(el);
        window.fixtureFarePayloads.push({ intent: data.get('form_action'), source: data.get('edit_source_id'), destination: data.get('destination'), amount: data.get('fare'), types: data.getAll('vehicle_types[]'), url: el.getAttribute('action') });
      });
    });
    await form.locator('button[type="submit"]').click();
    assert.equal(await page.locator('#editFareVehicleTypesError').isVisible(), true);
    assert.equal(await page.locator('#edit_vt_minibus').evaluate(input => input.validationMessage), 'Select at least one vehicle type to update its fare.');
    await page.waitForFunction(() => window.fixtureFareInvalidReports.length > 0);
    assert.ok(await page.evaluate(() => window.fixtureFareInvalidReports.every(report => report.prevented)), 'the visible group message must replace native popups over the checkbox chips');
    assert.deepEqual(await page.evaluate(() => window.fixtureFarePayloads), []);
    await toggle(page, 'minibus'); await toggle(page, 'van');
    await page.locator('#edit_fare_amount').fill('165.00');
    assert.deepEqual(await picked(page), ['minibus', 'van']);
    assert.deepEqual(await form.evaluate(el => Array.from(el.elements).filter(input => input.willValidate && !input.validity.valid).map(input => ({ name: input.name, value: input.value, message: input.validationMessage }))), [], 'the corrected fare form must pass native validation');
    await form.locator('button[type="submit"]').click();
    await page.waitForFunction(() => window.fixtureFarePayloads.length === 1, null, { timeout: 3000 });
    assert.deepEqual(await page.evaluate(() => window.fixtureFarePayloads), [{ intent: 'update_fares', source: '1', destination: 'ISABEL', amount: '165.00', types: ['minibus', 'van'], url: '/admin/routes/update/1' }]);
    assert.equal(await page.locator('#editFareVehicleTypesError').isVisible(), false);
    const modalBounds = await page.locator('#editFareModal .modal-content').boundingBox();
    assert.ok(modalBounds.x >= -1 && modalBounds.x + modalBounds.width <= width + 1);
    if (width === 375) {
      const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
      await page.screenshot({ path: path.join(dir, 'fare-multiselect-phone.png'), fullPage: true });
    }
    assert.deepEqual(errors, []);
    await context.close();
  }
});

test('opening another fare directly or from Add Fare resets checked types to that fare', { timeout: 30000 }, async t => {
  const { page, errors } = await open(t, 1280);
  await page.locator('[data-bs-target="#editFareModal"][data-id="1"]').click();
  await shown(page, 'editFareModal');
  await toggle(page, 'van');
  await page.locator('#editFareModal .btn-close').click();
  await page.waitForFunction(() => !document.querySelector('.modal.show') && !document.querySelector('.modal-backdrop'));
  await page.locator('[data-bs-target="#editFareModal"][data-id="3"]').click();
  await shown(page, 'editFareModal');
  assert.deepEqual(await picked(page), ['jeepney']);
  await page.locator('#editFareModal .btn-close').click();
  await page.waitForFunction(() => !document.querySelector('.modal.show') && !document.querySelector('.modal-backdrop'));
  await page.evaluate(() => bootstrap.Modal.getOrCreateInstance(document.getElementById('addFareModal')).show());
  await shown(page, 'addFareModal');
  await page.evaluate(() => switchToEditFare(2));
  await shown(page, 'editFareModal');
  assert.deepEqual(await picked(page), ['van']);
  assert.equal(await page.locator('#editFareForm').getAttribute('action'), '/admin/routes/update/2');
  assert.equal(await page.locator('#edit_fare_source_id').inputValue(), '2');
  assert.equal(await page.locator('#edit_fare_amount').inputValue(), '130');
  assert.equal(await page.locator('.modal-backdrop').count(), 1);
  assert.deepEqual(errors, []);
});

test('a failed multi-type edit restores the Edit Fare form and entered selections', { timeout: 30000 }, async t => {
  const { page, errors } = await open(t, 375, '?state=fare-edit-error');
  await shown(page, 'editFareModal');
  assert.equal(await page.locator('#addFareModal').isVisible(), false);
  assert.deepEqual(await picked(page), ['minibus', 'bus']);
  assert.equal(await page.locator('#edit_fare_amount').inputValue(), '165.00');
  assert.equal(await page.locator('#edit_fare_destination').inputValue(), 'ISABEL');
  assert.equal(await page.locator('#editFareForm').getAttribute('action'), '/admin/routes/update/1');
  assert.match(await page.locator('#editFareForm [role="alert"]').first().textContent(), /Route Management first/);
  assert.deepEqual(errors, []);
});

async function chooseAddFareOption(page, id, text) {
  await page.locator('#' + id + '_autocomplete_search').click();
  await page.locator('#' + id).locator('..').locator('.autocomplete-item').filter({ hasText: text }).click();
}

async function captureAddFareSubmissions(page) {
  await page.locator('#addFareForm').evaluate(form => {
    window.fixtureAddFarePayloads = [];
    form.addEventListener('submit', event => {
      const accepted = !event.defaultPrevented;
      event.preventDefault();
      if (accepted) {
        const data = new FormData(form);
        window.fixtureAddFarePayloads.push({ terminal: data.get('terminal_id'), destination: data.get('destination'), amount: data.get('fare'), types: data.getAll('vehicle_types[]') });
      }
    });
  });
}

test('Add Fare keeps the chosen terminal route when destinations have equal values', { timeout: 60000 }, async t => {
  for (const [width, method] of [[1280, 'click'], [375, 'keyboard'], [1280, 'exact-text']]) {
    const { page, context, errors } = await open(t, width, '?state=fare-add-duplicate-terminals');
    await page.locator('[data-bs-target="#addFareModal"]').first().click();
    await shown(page, 'addFareModal');
    await chooseAddFareOption(page, 'add_fare_terminal_id', 'PALOMPON');
    const destinationInput = page.locator('#add_destination_autocomplete_search');
    if (method === 'click') {
      await chooseAddFareOption(page, 'add_destination', 'PALOMPON → ISABEL');
    } else if (method === 'keyboard') {
      await destinationInput.fill('PALOMPON');
      await page.waitForFunction(() => document.querySelector('#add_destination').parentNode.querySelectorAll('.autocomplete-item').length === 1);
      await destinationInput.press('ArrowDown');
      await destinationInput.press('Enter');
    } else {
      await destinationInput.fill('PALOMPON → ISABEL');
      await page.locator('#add_fare_amount').click();
    }
    await page.locator('#add_fare_amount').fill('155.50');
    await page.locator('#addFareForm label').filter({ has: page.locator('#add_vt_minibus') }).click();
    assert.equal(await destinationInput.inputValue(), 'PALOMPON → ISABEL', method);
    assert.deepEqual(await page.locator('#add_destination').evaluate(select => ({ index: select.selectedIndex, terminal: select.selectedOptions[0].dataset.terminal, disabled: select.selectedOptions[0].disabled })), { index: 2, terminal: '2', disabled: false }, method);
    await captureAddFareSubmissions(page);
    await page.locator('#addFareForm button[type="submit"]').click();
    assert.deepEqual(await page.evaluate(() => window.fixtureAddFarePayloads), [{ terminal: '2', destination: 'ISABEL', amount: '155.50', types: ['minibus'] }], method);
    assert.deepEqual(errors, []);
    await context.close();
  }
});

test('Set Fare opens the destination that belongs to its terminal', { timeout: 30000 }, async t => {
  const { page, errors } = await open(t, 1280, '?state=fare-add-duplicate-terminals&action=add&terminal_id=2&destination=ISABEL&vehicle_type=minibus');
  await shown(page, 'addFareModal');
  assert.equal(await page.locator('#add_fare_terminal_id_autocomplete_search').inputValue(), 'PALOMPON');
  assert.equal(await page.locator('#add_destination_autocomplete_search').inputValue(), 'PALOMPON → ISABEL');
  assert.equal(await page.locator('#add_destination').evaluate(select => select.selectedIndex), 2);
  assert.equal(await page.locator('#add_vt_minibus').isChecked(), true);
  assert.deepEqual(errors, []);
});

test('Add Fare explains missing fields locally and rejects a disabled route option', { timeout: 30000 }, async t => {
  const { page, errors } = await open(t, 375, '?state=fare-add-duplicate-terminals');
  await page.locator('a').filter({ hasText: 'Click Add Fare' }).click();
  await shown(page, 'addFareModal');
  await page.locator('#add_fare_amount').fill('155.50');
  await page.locator('#addFareForm label').filter({ has: page.locator('#add_vt_minibus') }).click();
  await captureAddFareSubmissions(page);
  await page.locator('#addFareForm button[type="submit"]').click();
  assert.match(await page.locator('#add_fare_terminal_error').textContent(), /Select the terminal/);
  assert.match(await page.locator('#add_destination_error').textContent(), /Select a destination route from the list/);
  assert.equal(await page.locator('#add_fare_terminal_error').isVisible(), true);
  assert.equal(await page.locator('#add_destination_error').isVisible(), true);
  assert.deepEqual(await page.evaluate(() => window.fixtureAddFarePayloads), []);
  await chooseAddFareOption(page, 'add_fare_terminal_id', 'PALOMPON');
  await chooseAddFareOption(page, 'add_destination', 'PALOMPON → ISABEL');
  assert.equal(await page.locator('#add_fare_terminal_error').isVisible(), false);
  assert.equal(await page.locator('#add_destination_error').isVisible(), false);
  // Disabled selected options satisfy native required validation but are omitted
  // from FormData. The Add Fare guard must reject this state as well.
  await page.locator('#add_destination').evaluate(select => { select.selectedIndex = 1; });
  assert.equal(await page.locator('#addFareForm').evaluate(form => form.checkValidity()), true);
  await page.locator('#addFareForm button[type="submit"]').click();
  assert.match(await page.locator('#add_destination_error').textContent(), /selected terminal/);
  assert.deepEqual(await page.evaluate(() => window.fixtureAddFarePayloads), []);
  await chooseAddFareOption(page, 'add_destination', 'PALOMPON → ISABEL');
  await page.locator('#addFareForm button[type="submit"]').click();
  assert.deepEqual(await page.evaluate(() => window.fixtureAddFarePayloads), [{ terminal: '2', destination: 'ISABEL', amount: '155.50', types: ['minibus'] }]);
  assert.deepEqual(errors, []);
});

test('failed Add Fare submissions reopen with the error and all available old entries', { timeout: 45000 }, async t => {
  for (const state of ['fare-add-error', 'fare-add-error-missing-destination', 'fare-add-error-missing-terminal']) {
    const { page, context, errors } = await open(t, 375, '?state=' + state);
    await shown(page, 'addFareModal');
    assert.equal(await page.locator('#editFareModal').isVisible(), false);
    assert.match(await page.locator('#addFareModal [role="alert"]').first().textContent(), /Select the route destination/);
    assert.equal(await page.locator('#add_fare_terminal_id').inputValue(), state.endsWith('missing-terminal') ? '' : '2');
    assert.equal(await page.locator('#add_fare_terminal_id_autocomplete_search').inputValue(), state.endsWith('missing-terminal') ? '' : 'PALOMPON');
    assert.equal(await page.locator('#add_destination').inputValue(), state.endsWith('missing-destination') ? '' : 'ISABEL');
    if (state === 'fare-add-error') assert.equal(await page.locator('#add_destination_autocomplete_search').inputValue(), 'PALOMPON → ISABEL');
    assert.equal(await page.locator('#add_fare_amount').inputValue(), '155.50');
    assert.deepEqual(await page.locator('#addFareForm input[name="vehicle_types[]"]:checked').evaluateAll(inputs => inputs.map(input => input.value)), ['minibus','van']);
    assert.deepEqual(errors, []);
    await context.close();
  }
});

test('public VAN and MINIBUS cards show discounts for every route on load and after legacy data refresh', {timeout:60000}, async t=>{
  for(const width of [375,1366]) {
    const context=await browser.newContext({viewport:{width,height:900}});t.after(()=>context.close());
    await context.route('**/*',route=>route.request().url().startsWith(origin)||route.request().url().startsWith('data:')?route.continue():route.abort());
    const page=await context.newPage(),errors=[];page.on('pageerror',error=>errors.push(error.message));
    let releaseFirstResponse;
    const firstResponseGate=new Promise(resolve=>releaseFirstResponse=resolve);
    t.after(()=>releaseFirstResponse());
    let firstRequest=true;
    await page.route('**/api/fares?*',async route=>{
      if(firstRequest) {firstRequest=false;await firstResponseGate;}
      await route.continue();
    });
    const checkQuotes=async()=>{
      for(const type of ['van','minibus']) {
        const entries=page.locator('#fare-card-'+type+' .fare-item');assert.equal(await entries.count(),3);
        for(const [index,destination,amounts] of [[0,'ISABEL',['₱120.00','₱127.50','₱120.00']],[1,'ORMOC',['₱64.00','₱68.00','₱64.00']],[2,'PALOMPON',['₱80.00','₱85.00','₱80.00']]]) {
          const text=await entries.nth(index).innerText();assert.ok(text.includes(destination));
          for(const label of ['PWD Discount','Student Discount','Senior Citizen Discount'])assert.ok(text.includes(label),type+' '+destination+' '+text);
          const values=await entries.nth(index).locator('span[style*="color: #16a34a"]').allTextContents();assert.deepEqual(values,amounts,type+' '+destination);
        }
      }
    };
    publicFarePayload=farePayload();await page.goto(origin+'/public-fares');
    await checkQuotes();
    await page.evaluate(()=>window.firstRenderedFare=document.querySelector('#fare-card-van .fare-item'));
    releaseFirstResponse();await page.waitForFunction(()=>window._fareFingerprint!=='' && !window._fareFetchPending);
    assert.equal(await page.evaluate(()=>window.firstRenderedFare===document.querySelector('#fare-card-van .fare-item')),true,'an unchanged first response preserves existing rows');

    // A repair can complete between the PHP render and its very first API reply.
    // It must update missing quotes immediately instead of seeding stale DOM forever.
    publicFarePayload=farePayload();await page.goto(origin+'/public-fares?state=route-discounts-legacy');
    await page.waitForFunction(()=>window._fareFingerprint!=='' && !window._fareFetchPending);
    await checkQuotes();

    publicFarePayload=farePayload(true);await page.goto(origin+'/public-fares?state=route-discounts-legacy');
    await page.waitForFunction(()=>window._fareFingerprint!=='' && !window._fareFetchPending);
    for(const type of ['van','minibus'])assert.doesNotMatch(await page.locator('#fare-card-'+type+' .fare-item').first().innerText(),/PWD Discount/);
    publicFarePayload=farePayload();await page.evaluate(()=>fetchFareData());
    await page.waitForFunction(()=>!window._fareFetchPending);
    await checkQuotes();
    assert.deepEqual(errors,[]);await context.close();
  }
});

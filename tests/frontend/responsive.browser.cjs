'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const playwright = require('playwright');
const { assetContentType } = require('./harness.cjs');
const root = path.resolve(__dirname, '../..');
const engine = process.env.TQ_BROWSER || 'chromium';
const managementPages = ['admin-users', 'admin-vehicles', 'admin-routes', 'admin-terminals', 'admin-announcements', 'admin-rules', 'admin-history', 'admin-logs', 'admin-settings'];
const pages = ['login', 'forgot', 'reset', 'verify', 'guest', 'fares', 'schedules', 'search', 'staff-queue', 'staff-schedules', 'staff-password', 'admin-schedules', ...managementPages];
const authPages = new Set(pages.slice(0, 4));
const cache = new Map();
const report = { engine, fixtures: 'production PHP views with seeded helper/data values', cases: [], beforeAfter: {} };
let browser, server, origin, beforeCSS;
function scriptsOf(html) { return [...html.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(m => m[1]).filter(Boolean); }
function fixture(name, long, baseline, state = '') {
  const key = name + ':' + long + ':' + state;
  if (!cache.has(key)) {
    const rendered = execFileSync('php', [path.join(__dirname, 'render-responsive-fixture.php'), name, long ? 'long' : 'normal', 'theme', state], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
    let scripts;
    if (authPages.has(name) || managementPages.includes(name)) scripts = scriptsOf(rendered);
    else if (!name.startsWith('staff') && !name.startsWith('admin')) {
      // Keep actual guest navigation/announcement controls; isolate realtime reads.
      const header = fs.readFileSync(path.join(root, 'app/Views/templates/guest_header.php'), 'utf8');
      scripts = [header.slice(header.indexOf('var guestMenuCloseSequence = 0;'), header.lastIndexOf('</script>'))];
    } else {
      // Header handlers remain real; queue API actions are covered by existing tests.
      scripts = scriptsOf(rendered).filter(s => s.includes('initOperationsHeaderClock') || s.includes('syncHeaderHeight'));
    }
    let html = rendered.replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, '');
    html = html.replace(/https:\/\/cdn\.jsdelivr\.net\/npm\/bootstrap@5\.3\.0\/dist\/css\/bootstrap\.min\.css/g, '/fixture/bootstrap.css');
    html = html.replace('</head>', '<script src="/assets/js/interaction-motion.js"></script></head>');
    html = html.replace('</body>', '<script src="/fixture/bootstrap.js"></script>' + scripts.map(s => '<script>' + s + '</script>').join('') + '<script src="/assets/js/global-loader.js"></script></body>');
    cache.set(key, html);
  }
  const html = cache.get(key);
  return baseline ? html.replace(/responsive\.css[^"']*/g, 'responsive.css?fixture-baseline=1') : html;
}
test.before(async () => {
  const base = process.env.TQ_RESPONSIVE_BASE_REF || 'c4048e1923aac4452526f7d9b45f7eca5192df8c';
  beforeCSS = execFileSync('git', ['show', base + ':public/assets/css/responsive.css'], { cwd: root, encoding: 'utf8' });
  // Render all fixtures eagerly so missing helper/data assumptions fail visibly.
  for (const name of pages) { fixture(name, false, false); fixture(name, true, false); }
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    const target = url.pathname;
    if (target === '/fixture.svg') { res.setHeader('Content-Type', 'image/svg+xml'); res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#b71c1c"/></svg>'); return; }
    if (target === '/images/logo.webp') { res.setHeader('Content-Type', 'image/webp'); res.end(fs.readFileSync(path.join(root, 'public/images/logo.webp'))); return; }
    if (target === '/fixture/bootstrap.css') { res.setHeader('Content-Type', 'text/css'); res.end(fs.readFileSync(path.join(__dirname, 'node_modules/bootstrap/dist/css/bootstrap.min.css'))); return; }
    if (target === '/fixture/bootstrap.js') { res.setHeader('Content-Type', 'text/javascript'); res.end(fs.readFileSync(path.join(__dirname, 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'))); return; }
    if (target.startsWith('/assets/')) {
      const file = path.resolve(root, 'public', '.' + target);
      if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file)) { res.writeHead(404); res.end(); return; }
      res.setHeader('Content-Type', assetContentType(file));
      res.end(target === '/assets/css/responsive.css' && url.searchParams.has('fixture-baseline') ? beforeCSS : fs.readFileSync(file)); return;
    }
    const name = target.slice(1);
    if (!pages.includes(name)) { res.writeHead(404); res.end(); return; }
    res.setHeader('Content-Type', 'text/html'); res.end(fixture(name, url.searchParams.has('long'), url.searchParams.has('baseline'), url.searchParams.get('state') || ''));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = `http://127.0.0.1:${server.address().port}`;
  browser = await playwright[engine].launch({ headless: true, ...(engine === 'chromium' && process.env.TQ_BROWSER_CHANNEL ? { channel:process.env.TQ_BROWSER_CHANNEL } : {}) });
  report.browserVersion = browser.version();
});
test.after(async () => {
  if (browser) await browser.close();
  if (server) { server.closeAllConnections(); await new Promise(resolve => server.close(resolve)); }
  const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
  fs.writeFileSync(path.join(dir, 'responsive-' + engine + '.json'), JSON.stringify(report, null, 2));
});
async function open(t, name, width, height, query = 'long=1', mode = 'full', touch = false) {
  const context = await browser.newContext({ viewport: { width, height }, hasTouch: touch, reducedMotion: mode === 'reduced' ? 'reduce' : 'no-preference' });
  t.after(() => context.close());
  await context.addInitScript(({ lite }) => {
    Object.defineProperty(Navigator.prototype, 'hardwareConcurrency', { configurable: true, get: () => lite ? 2 : 8 });
    Object.defineProperty(Navigator.prototype, 'deviceMemory', { configurable: true, get: () => lite ? 2 : 8 });
    document.addEventListener('shown.bs.modal', event => { event.target.dataset.fixtureShown = '1'; });
    document.addEventListener('hidden.bs.modal', event => { delete event.target.dataset.fixtureShown; });
  }, { lite: mode === 'lite' });
  await context.route('**/*', route => route.request().url().startsWith(origin) || route.request().url().startsWith('data:') ? route.continue() : route.abort());
  const page = await context.newPage();
  const errors = []; page.on('pageerror', e => errors.push(e.message));
  await page.goto(`${origin}/${name}?${query}`);
  assert.equal(await page.locator('link[rel="stylesheet"][href*="/interaction-motion.css"]').count(), 1, `${name} has duplicate motion stylesheets`);
  await page.evaluate(() => Promise.all(document.getAnimations().filter(a => a.effect?.getTiming().iterations !== Infinity).map(a => a.finished.catch(() => {}))));
  return { page, context, errors };
}
async function bounds(page, selectors) {
  return page.evaluate(selectors => {
    const width = document.documentElement.clientWidth;
    return selectors.flatMap(s => [...document.querySelectorAll(s)].map(el => {
      const r = el.getBoundingClientRect(), style = getComputedStyle(el);
      if (!r.width || style.display === 'none' || style.visibility === 'hidden') return null;
      return { selector: s, left: r.left, right: r.right, width: r.width, fits: r.left >= -1 && r.right <= width + 1 };
    }).filter(Boolean));
  }, selectors);
}
function checkBounds(items, label) { assert.ok(items.length > 0, label + ' has no visible targets'); assert.ok(items.every(e => e.fits), label + ': ' + JSON.stringify(items.filter(e => !e.fits))); }
async function screenshot(page, name, fullPage = true) {
  const dir = path.join(__dirname, 'artifacts'); fs.mkdirSync(dir, { recursive: true });
  await page.screenshot({ path: path.join(dir, `${engine}-${name}.png`), fullPage, animations: 'disabled' });
}

test('management pages keep long values and primary controls inside phone, tablet and desktop layouts', { timeout: 90000 }, async t => {
  const failures = [];
  for (const name of managementPages) {
    for (const [width, height] of [[320, 568], [768, 1024], [844, 390], [1280, 800]]) {
      const { page, context, errors } = await open(t, name, width, height);
      let passed = true;
      try {
        checkBounds(await bounds(page, ['.main-content', '.page-header-modern', '.modern-card', '.settings-page', '.route-card-item']), `${name} ${width}`);
        if (width <= 768) {
          const clipped = await page.evaluate(() => [...document.querySelectorAll('td[data-label]')].flatMap(td => {
            if (!/^(Name|Location|Label|Destination|Details|Message|User|Operator|Driver|Routes?|Assigned Route|Terminal)/.test(td.dataset.label)) return [];
            const r = td.getBoundingClientRect(); if (!r.width || !r.height) return [];
            const range = document.createRange(); range.selectNodeContents(td);
            const text = range.getBoundingClientRect();
            return text.left < r.left - 1 || text.right > r.right + 1 ? [{ label: td.dataset.label, text: td.textContent.trim(), cell: { left: r.left, right: r.right }, content: { left: text.left, right: text.right } }] : [];
          }));
          assert.deepEqual(clipped, [], `${name} ${width} clipped values`);
          if (name === 'admin-routes') {
            const clippedTitles = await page.locator('.route-card-item > .modern-card-header > .d-flex:first-child > .fw-bold').evaluateAll(titles => titles.flatMap(title => {
              const header = title.closest('.modern-card-header').getBoundingClientRect();
              const range = document.createRange(); range.selectNodeContents(title);
              const text = range.getBoundingClientRect();
              return text.left < header.left - 1 || text.right > header.right + 1 ? [title.textContent.trim()] : [];
            }));
            assert.deepEqual(clippedTitles, [], `route headings ${width} clipped values`);
          }
          if (name === 'admin-vehicles') {
            const labels = await page.evaluate(() => {
              const canvas = document.createElement('canvas'), ctx = canvas.getContext('2d');
              return ['Operator Name', 'Driver Name', 'Assigned Route'].map(label => {
                const td = document.querySelector(`#vehicles-table td[data-label="${label}"]`), style = getComputedStyle(td, '::before');
                ctx.font = style.font || `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
                const spacing = parseFloat(style.letterSpacing) || 0;
                const wordWidth = Math.max(...label.toUpperCase().split(' ').map(word => ctx.measureText(word).width + spacing * word.length));
                return { label, wordWidth, available: parseFloat(style.width) };
              });
            });
            assert.ok(labels.every(label => label.wordWidth <= label.available + 1), `label words are squeezed: ${JSON.stringify(labels)}`);
          }
        }
        assert.deepEqual(errors, [], name);
      } catch (error) {
        passed = false; failures.push({ page: name, width, message: error.message, scriptErrors: errors });
        const detail = page.locator('#vehicles-table td[data-label="Operator Name"]');
        if (name === 'admin-vehicles') await detail.scrollIntoViewIfNeeded();
        await screenshot(page, `${name}-failure-${width}`, false);
      }
      report.cases.push({ page: name, width, height, passed });
      if (width === 320 && passed) {
        const target = {
          'admin-vehicles': '#vehicles-table td[data-label="Operator Name"]',
          'admin-routes': '.route-card-item .modern-card-header',
          'admin-terminals': '#terminals-table td[data-label="Name"]',
          'admin-announcements': '#announcements-table td[data-label="Message"]',
          'admin-rules': '#departure-rules-table td[data-label="Destination"]',
          'admin-history': 'td[data-label="Driver"]',
          'admin-logs': 'td[data-label="Details"]'
        }[name];
        if (target) await page.locator(target).first().scrollIntoViewIfNeeded();
        await screenshot(page, `${name}-phone`, false);
      }
      await context.close();
    }
  }
  report.managementFailures = failures;
  assert.deepEqual(failures, []);
});



test('password account details precede the form on mobile with guidelines below it', { timeout: 30000 }, async t => {
  for (const width of [375,768,1280]) {
    const {page,context,errors} = await open(t, 'staff-password', width, 900, '');
    const positions = await page.evaluate(() => Object.fromEntries(['profile','form','guidelines'].map(name => {
      const box = document.querySelector('.password-' + name).getBoundingClientRect();
      return [name,{top:box.top,bottom:box.bottom,left:box.left,right:box.right}];
    })));
    if (width < 992) {
      assert.ok(positions.profile.bottom <= positions.form.top, JSON.stringify(positions));
      assert.ok(positions.form.bottom <= positions.guidelines.top, JSON.stringify(positions));
    } else {
      assert.ok(Math.abs(positions.profile.top - positions.form.top) <= 1, JSON.stringify(positions));
      assert.ok(positions.profile.right < positions.form.left, JSON.stringify(positions));
    }
    checkBounds(await bounds(page, ['.password-profile','.password-form','.password-guidelines']), 'password ' + width);
    assert.equal(await page.locator('#btnSubmitPassword').isEnabled(), true);
    if (width === 375) await screenshot(page, 'password-account-first-mobile', true);
    await page.locator('.password-guidelines').scrollIntoViewIfNeeded();
    assert.equal(await page.locator('.password-guidelines').isVisible(), true);
    assert.equal(await page.locator('.password-guidelines').evaluate(el => {
      const box = el.getBoundingClientRect();
      return box.top >= 0 && box.bottom <= innerHeight + 1;
    }), true);
    if (width === 375) await screenshot(page, 'password-guidelines-mobile', false);
    assert.deepEqual(errors, []);
    await context.close();
  }
});

test('management actions align with their headers and scroll as normal columns like User Management', { timeout: 90000 }, async t => {
  const reference = await open(t, 'admin-users', 1280, 900, '');
  await reference.page.locator('#users-table td[data-label="Action"]').first().scrollIntoViewIfNeeded();
  await screenshot(reference.page, 'user-management-action-reference', false);
  assert.deepEqual(reference.errors, []);
  await reference.context.close();
  for (const [name, id] of [['admin-terminals','terminals-table'], ['admin-vehicles','vehicles-table'], ['admin-rules','departure-rules-table'], ['admin-announcements','announcements-table']]) {
    for (const [width, height] of [[844, 600], [1280, 900], [1536, 900], [320, 900], [375, 900], [768, 1024]]) {
      const { page, context, errors } = await open(t, name, width, height, '');
      const table = page.locator('#' + id);
      const cell = table.locator('tbody .management-actions-col').first();
      const actions = cell.locator('.management-row-actions');
      await actions.scrollIntoViewIfNeeded();
      if (width > 768) {
        const scroll = table.locator('..');
        const positions = [];
        for (const fraction of [0, 1]) {
          await scroll.evaluate((el, fraction) => { el.scrollLeft = (el.scrollWidth - el.clientWidth) * fraction; }, fraction);
          await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
          const result = await cell.evaluate(el => {
            const box = el.getBoundingClientRect(), parent = el.closest('.table-responsive');
            const header = el.closest('table').querySelector('thead .management-actions-col');
            return { left:box.left, scrollLeft:parent.scrollLeft, position:getComputedStyle(el).position,
              headerPosition:getComputedStyle(header).position, headerLeft:header.getBoundingClientRect().left };
          });
          assert.equal(result.position, 'static', name + ' action cells should not be sticky');
          assert.equal(result.headerPosition, 'static', name + ' action header should not be sticky');
          assert.ok(Math.abs(result.headerLeft - result.left) <= 1, JSON.stringify({name,width,fraction,result}));
          positions.push(result);
        }
        const movement = positions[0].left - positions[1].left;
        const distance = positions[1].scrollLeft - positions[0].scrollLeft;
        assert.ok(Math.abs(movement - distance) <= 1, name + ' actions should move with the table: ' + JSON.stringify(positions));
        const alignment = await cell.evaluate(el => {
          const header = el.closest('table').querySelector('thead .management-actions-col');
          return { header:header.getBoundingClientRect().left + parseFloat(getComputedStyle(header).paddingLeft),
            buttons:[...el.querySelectorAll('.btn-modern')].map(button => button.getBoundingClientRect().left) };
        });
        assert.ok(alignment.buttons.every(left => Math.abs(left - alignment.header) <= 1), JSON.stringify({name,width,alignment}));
        assert.equal(await cell.evaluate(el => getComputedStyle(el).boxShadow), 'none');
        await cell.hover();
        const highlight = await cell.evaluate(el => ({ background:getComputedStyle(el).backgroundColor, image:getComputedStyle(el).backgroundImage }));
        assert.deepEqual(highlight, {background:'rgba(0, 0, 0, 0)',image:'none'}, name + ' Actions should share the row highlight');
        await page.mouse.move(0, 0);
        await page.evaluate(() => Promise.all(document.getAnimations().filter(a => a.effect?.getTiming().iterations !== Infinity).map(a => a.finished.catch(() => {}))));
      }
      checkBounds(await bounds(page, ['#' + id + ' .management-row-actions .btn-modern']), name + ' actions ' + width);
      const targets = await actions.locator('.btn-modern').evaluateAll(buttons => buttons.map(button => {
        const box = button.getBoundingClientRect(), label = button.querySelector('.action-label');
        return { top:box.top, bottom:box.bottom, height:box.height, label:label?.textContent.trim(), visibleLabel:label && getComputedStyle(label).display !== 'none' };
      }));
      assert.ok(targets.length >= 2 && targets.every(target => target.visibleLabel && target.height >= (width <= 768 ? 40 : 31) - 1), JSON.stringify({name,width,targets}));
      if (width > 768) {
        assert.ok(targets.every((target, index) => index === 0 || target.top >= targets[index - 1].bottom + 5), name + ' desktop actions do not stack: ' + JSON.stringify(targets));
      } else {
        const layout = await cell.evaluate(el => {
          const group = el.querySelector('.management-row-actions'), box = el.getBoundingClientRect(), style = getComputedStyle(el);
          return { label:el.getAttribute('data-label'), labelDisplay:getComputedStyle(el, '::before').display, right:group.getBoundingClientRect().right,
            cellRight:box.right - parseFloat(style.paddingRight), direction:getComputedStyle(group).flexDirection,
            wrap:getComputedStyle(group).flexWrap, labelWidth:parseFloat(getComputedStyle(el, '::before').width),
            labelFlex:getComputedStyle(el, '::before').flex, labelMin:getComputedStyle(el, '::before').minWidth,
            labelPadding:getComputedStyle(el, '::before').paddingRight, gap:style.gap,
            groupWidth:getComputedStyle(group).width, groupFlex:getComputedStyle(group).flex };
        });
        assert.equal(layout.direction, 'row', name + ' mobile actions should share a row');
        assert.equal(layout.wrap, 'wrap', name + ' mobile actions should wrap when needed');
        assert.match(layout.label, /Action/);
        assert.notEqual(layout.labelDisplay, 'none');
        assert.ok(Math.abs(layout.right - layout.cellRight) <= 1, JSON.stringify({name,width,layout}));
        if (width >= 375) assert.ok(Math.abs(targets[0].top - targets[1].top) <= 1, name + ' first actions should fit beside one another: ' + JSON.stringify(targets));
      }
      if (width === 375 || width === 1280 || width === 1536) await screenshot(page, name + '-management-actions-' + width, false);
      assert.deepEqual(errors, [], name + ' ' + width);
      await context.close();
    }
  }
});

test('vehicle register shows every column without horizontal scrolling on desktop, including bulk selection', { timeout: 90000 }, async t => {
  for (const width of [1280,1366,1440,1536]) {
    for (const query of ['', 'long=1']) {
      const { page, context, errors } = await open(t, 'admin-vehicles', width, 900, query);
      await page.evaluate(() => document.fonts.ready);
      for (const selection of [false,true]) {
        if (selection) await page.locator('#btn-toggle-select-vehicles').click();
        const result = await page.locator('#vehicles-table').evaluate(table => {
          const wrapper = table.closest('.table-responsive'), outer = wrapper.getBoundingClientRect();
          const visible = el => el.getBoundingClientRect().width > 0;
          const headers = [...table.querySelectorAll('thead th')].filter(visible);
          const measure = document.createElement('canvas').getContext('2d');
          const splitHeaderWords = headers.flatMap(header => {
            const style = getComputedStyle(header);
            measure.font = `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
            const label = style.textTransform === 'uppercase' ? header.textContent.toUpperCase() : header.textContent;
            const spacing = parseFloat(style.letterSpacing) || 0;
            const longest = Math.max(0,...label.trim().split(/\s+/).map(word => measure.measureText(word).width + spacing * word.length));
            const available = header.getBoundingClientRect().width - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
            return longest > available + 1 ? [{label:header.textContent.trim(),longest,available}] : [];
          });
          const rows = [...table.querySelectorAll('tbody tr[data-vehicle-id]')].filter(visible);
          const clipped = rows.flatMap(row => [...row.children].filter(visible).flatMap(cell => {
            const box = cell.getBoundingClientRect(), range = document.createRange();
            range.selectNodeContents(cell);
            const text = range.getBoundingClientRect();
            return text.width > 0 && (text.left < box.left - 1 || text.right > box.right + 1)
              ? [{label:cell.dataset.label,cellLeft:box.left,cellRight:box.right,textLeft:text.left,textRight:text.right}] : [];
          }));
          const action = table.querySelector('tbody .management-actions-col').getBoundingClientRect();
          return {scroll:wrapper.scrollWidth,client:wrapper.clientWidth,labels:headers.map(header=>header.textContent.trim()),
            actionFits:action.left >= outer.left - 1 && action.right <= outer.right + 1,clipped,splitHeaderWords};
        });
        assert.ok(result.scroll <= result.client + 1, 'unnecessary horizontal scrollbar: ' + JSON.stringify({width,query,selection,result}));
        assert.equal(result.labels.length, selection ? 11 : 10);
        assert.ok(result.labels.includes('Registered') && result.labels.includes('Action'));
        assert.equal(result.actionFits,true, 'Actions must be visible without horizontal scrolling');
        assert.deepEqual(result.splitHeaderWords,[], 'header words must fit without splitting: ' + JSON.stringify({width,query,selection,result}));
        assert.deepEqual(result.clipped,[], 'vehicle text must remain inside its column: ' + JSON.stringify({width,query,selection,result}));
        if (width === 1366 && !query) {
          await page.locator('#vehicles-table').scrollIntoViewIfNeeded();
          await screenshot(page,'vehicle-desktop-fit-1366' + (selection ? '-selection' : ''),false);
        }
      }
      assert.deepEqual(errors,[]); await context.close();
    }
  }
});

test('vehicle filters scroll in one row on smaller devices and every filter stays usable', {timeout:60000}, async t => {
  for (const width of [320,375,768,844]) {
    const {page,context,errors}=await open(t,'admin-vehicles',width,900,'state=register-order');
    const strip=page.locator('.vehicle-filter-strip');
    await strip.scrollIntoViewIfNeeded();
    await page.evaluate(()=>document.fonts.ready);
    const layout=await strip.evaluate(el=>({
      wrap:getComputedStyle(el).flexWrap,overflow:getComputedStyle(el).overflowX,
      tops:Array.from(el.querySelectorAll('.vf-btn')).map(btn=>btn.getBoundingClientRect().top),
      width:el.clientWidth,total:el.scrollWidth,pageWidth:document.documentElement.scrollWidth,viewport:innerWidth
    }));
    assert.equal(layout.wrap,'nowrap');assert.equal(layout.overflow,'auto');
    assert.ok(Math.max(...layout.tops)-Math.min(...layout.tops)<=1,JSON.stringify({width,layout}));
    assert.ok(layout.pageWidth<=layout.viewport+1,JSON.stringify({width,layout}));
    if(width<=375) assert.ok(layout.total>layout.width,'mobile filters should scroll within their own strip');
    await screenshot(page,'vehicle-scroll-filters-'+width,false);
    await page.locator('#filter-btn-archived').click();
    assert.match(await page.locator('#filter-label').innerText(),/archived/i);
    assert.equal(await page.locator('tr[data-vehicle-id="205"]').isVisible(),true);
    assert.match(await page.locator('#filter-btn-archived').getAttribute('class'),/\bactive\b/);
    await page.locator('#filter-btn-all').click();
    assert.match(await page.locator('#filter-label').innerText(),/in-service/);
    assert.deepEqual(errors,[]);await context.close();
  }
});

test('pages load their body fonts without unused font preload warnings', {timeout:60000}, async t=>{
  for (const name of ['login','guest','admin-vehicles','admin-users','staff-queue']) {
  const {page,context,errors}=await open(t,name,375,850);
  const warnings=[];
  page.on('console',message=>{if(message.text().includes('preloaded')&&message.text().includes('/fonts/')) warnings.push(message.text());});
  const preloads=page.locator('link[rel="preload"][as="font"]');
  assert.equal(await preloads.count(),name==='login'?0:1);
  if (name!=='login') assert.match(await preloads.getAttribute('href'),/outfit-latin-6c18d579fd87\.woff2$/);
  await page.evaluate(()=>document.fonts.ready);
  assert.match(await page.locator('body').evaluate(el=>getComputedStyle(el).fontFamily),name==='login'?/Inter/:/Outfit/);
  await page.waitForTimeout(4000);
  assert.deepEqual(warnings,[],name);assert.deepEqual(errors,[],name);
  await context.close();
  }
});

test('registered route groups retain daily queue positions through search, filters and bulk selection', { timeout: 90000 }, async t => {
  for (const [width, height] of [[320,900], [375,900], [768,1024], [1280,1000]]) {
    const { page, context, errors } = await open(t, 'admin-vehicles', width, height, 'state=register-order', 'full', width <= 375);
    await page.addScriptTag({ url:origin + '/assets/js/autocomplete-search.js' });
    const visibleRows = () => page.locator('#vehicles-table tr[data-vehicle-id]:visible').evaluateAll(rows => rows.map(row => ({
      plate:row.querySelector('.plate-number').textContent.trim(), number:row.querySelector('.row-number').textContent.trim()
    })));
    const headings = () => page.locator('#vehicles-table [data-route-heading]:visible').allTextContents();
    assert.deepEqual(await visibleRows(), [
      {plate:'BATO-004',number:'1'}, {plate:'AAA-002',number:'1'}, {plate:'MID-003',number:'2'},
      {plate:'ZZZ-001',number:'3'}, {plate:'MAINTENANCE-006',number:'—'}, {plate:'UNASSIGNED-007',number:'—'}
    ]);
    assert.deepEqual((await headings()).map(text => text.trim()), ['VILLABA → BATO','VILLABA → ORMOC','No active route']);
    const headingGap = await page.locator('.vehicle-route-heading-label').first().evaluate(el => {
      const icon = el.querySelector('i').getBoundingClientRect(), text = el.querySelector('span').getBoundingClientRect();
      return text.left - icon.right;
    });
    assert.ok(headingGap >= 6 && headingGap <= 12, 'route icon and label should stay together: ' + headingGap);
    assert.equal(await page.locator('.vehicle-start-order').count(), 0);
    const search = page.locator('#vehicle-search'), route = page.locator('#vehicle-route-filter');
    const routeInput = page.getByRole('textbox', {name:'Filter route'});
    const routeWrapper = route.locator('..');
    assert.equal(await routeInput.getAttribute('placeholder'), 'Search routes...');
    const searchBox = await search.boundingBox(), routeBox = await routeInput.boundingBox();
    assert.ok(Math.abs(searchBox.height - routeBox.height) <= 1, JSON.stringify({width,searchBox,routeBox}));
    if (width >= 576) assert.ok(Math.abs(searchBox.y - routeBox.y) <= 1, JSON.stringify({width,searchBox,routeBox}));
    checkBounds(await bounds(page, ['.vehicle-register-filter-row','#vehicle-search','#vehicle-route-filter_autocomplete_search','.vehicle-filter-strip']), 'register filters ' + width);
    await routeInput.fill('orm');
    await page.waitForFunction(() => document.getElementById('vehicle-route-filter').parentElement.querySelectorAll('.autocomplete-item').length === 1);
    assert.equal(await routeWrapper.locator('.autocomplete-item').count(), 1);
    const routeChoice = routeWrapper.locator('.autocomplete-item[data-value="1|ORMOC"]');
    if (width <= 375) await routeChoice.tap(); else await routeChoice.click();
    assert.equal(await route.inputValue(), '1|ORMOC');
    assert.deepEqual((await headings()).map(text => text.trim()), ['VILLABA → ORMOC']);
    await search.fill('ZZZ-001');
    assert.deepEqual(await visibleRows(), [{plate:'ZZZ-001',number:'3'}]);
    await page.locator('#btn-toggle-select-vehicles').click();
    assert.equal(await page.locator('[data-route-heading="1|ORMOC"] td').getAttribute('colspan'), '11');
    await page.locator('tr[data-vehicle-id="201"] .vehicle-row-checkbox').check();
    assert.equal((await page.locator('#vehicle-selected-count').textContent()).trim(), '1');
    await search.fill('');
    assert.equal(await page.locator('[data-route-heading="1|ORMOC"] td').getAttribute('colspan'), '10');
    assert.equal(await page.locator('.vehicle-row-checkbox:checked').count(), 0);
    await page.locator('#filter-btn-van').click();
    assert.deepEqual(await visibleRows(), [{plate:'MID-003',number:'2'},{plate:'ZZZ-001',number:'3'}]);
    await page.locator('#filter-btn-maintenance').click();
    assert.deepEqual(await visibleRows(), [{plate:'MAINTENANCE-006',number:'—'}]);
    await page.locator('#filter-btn-archived').click();
    assert.deepEqual(await visibleRows(), [{plate:'ARCHIVED-005',number:'—'}]);
    await routeInput.fill('No active');
    await page.waitForFunction(() => {
      const items = document.getElementById('vehicle-route-filter').parentElement.querySelectorAll('.autocomplete-item');
      return items.length === 1 && items[0].dataset.value === '__none';
    });
    await routeInput.press('ArrowDown');
    await routeInput.press('Enter');
    assert.equal(await route.inputValue(), '__none');
    assert.deepEqual(await visibleRows(), []);
    assert.deepEqual(await headings(), []);
    assert.equal(await page.locator('.no-filter-results:visible').count(), 1);
    await page.locator('#filter-btn-all').click();
    assert.deepEqual(await visibleRows(), [{plate:'UNASSIGNED-007',number:'—'}]);
    await routeWrapper.getByRole('button', {name:'Clear selection'}).click();
    assert.equal(await route.inputValue(), '');
    assert.equal((await visibleRows()).length, 6);
    if (width === 375 || width === 1280) {
      await page.locator('.vehicle-register-filter-row').scrollIntoViewIfNeeded();
      await screenshot(page, 'registered-route-order-' + width, false);
      await page.locator('[data-route-heading="1|ORMOC"]').scrollIntoViewIfNeeded();
      await screenshot(page, 'registered-route-heading-' + width, false);
    }
    assert.deepEqual(errors, [], 'register filters ' + width);
    await context.close();
  }
});

test('vehicle modals preserve native fields, validation, focus and dismissal at small sizes and motion modes', { timeout: 60000 }, async t => {
  for (const [width, height, mode] of [[320, 568, 'full'], [844, 390, 'full'], [1280, 800, 'full'], [320, 568, 'lite'], [320, 568, 'reduced']]) {
    const { page, context, errors } = await open(t, 'admin-vehicles', width, height, 'long=1', mode);
    const posts = []; page.on('request', request => { if (request.method() === 'POST') posts.push(request.url()); });
    const trigger = page.locator('[data-bs-target="#registerVehicleModal"]');
    await trigger.click();
    const modal = page.locator('#registerVehicleModal');
    await page.waitForFunction(() => document.querySelector('#registerVehicleModal').dataset.fixtureShown === '1');
    assert.equal(await modal.evaluate(el => el.contains(document.activeElement)), true);
    checkBounds(await bounds(page, ['#registerVehicleModal .modal-content', '#registerVehicleModal .modal-footer', '#registerVehicleModal input:not([type="hidden"]):not([type="file"])', '#registerVehicleModal select', '#registerVehicleModal button[type="submit"]']), `registration ${width} ${mode}`);
    const contract = await page.locator('#registerVehicleForm').evaluate(form => ({ action: form.getAttribute('action'), method: form.method, enctype: form.enctype, csrf: new FormData(form).get('csrf_fixture'), required: [...form.querySelectorAll('[required]')].map(el => el.name), valid: form.checkValidity() }));
    assert.equal(contract.action, '/admin/vehicles/store'); assert.equal(contract.method, 'post'); assert.equal(contract.enctype, 'multipart/form-data'); assert.equal(contract.csrf, 'unchanged');
    for (const field of ['plate_number', 'operator_name', 'type', 'route_id', 'capacity']) assert.ok(contract.required.includes(field));
    assert.equal(contract.valid, false);
    await page.locator('#registerVehicleModal button[type="submit"]').click();
    assert.deepEqual(posts, []); assert.equal(await page.locator('#registerVehicleModal button[type="submit"]').isEnabled(), true);
    await page.locator('#plate_number').fill('abc-1234'); assert.equal(await page.locator('#plate_number').inputValue(), 'ABC-1234');
    await page.locator('#registerVehicleModal .btn-close').click();
    await page.waitForFunction(() => !document.querySelector('#registerVehicleModal').classList.contains('show') && !document.querySelector('.modal-backdrop'));
    assert.equal(await trigger.evaluate(el => el === document.activeElement), true);
    await page.locator('[data-bs-target="#manageVehicleTypesModal"]').click();
    await page.waitForFunction(() => document.querySelector('#manageVehicleTypesModal').dataset.fixtureShown === '1');
    checkBounds(await bounds(page, ['#manageVehicleTypesModal .modal-content', '#manageVehicleTypesModal .modal-footer']), `types ${width} ${mode}`);
    await page.locator('#manageVehicleTypesModal .modal-footer [data-bs-dismiss="modal"]').click();
    await page.waitForFunction(() => !document.querySelector('.modal.show') && !document.querySelector('.modal-backdrop'));
    assert.deepEqual(errors, []); assert.deepEqual(posts, []);
    report.cases.push({ page: 'vehicle modal controls', width, height, mode, passed: true }); await context.close();
  }
});

test('vehicle autocomplete validation stays closed until the field is used and preserves registration payloads', { timeout: 60000 }, async t => {
  for (const [width, height, mode, touch] of [[320, 568, 'full'], [844, 390, 'full'], [1280, 800, 'full'], [320, 568, 'lite'], [320, 568, 'reduced'], [390, 844, 'full', true]]) {
    const { page, context, errors } = await open(t, 'admin-vehicles', width, height, '', mode, touch);
    await page.addScriptTag({ path: path.join(root, 'public/assets/js/autocomplete-search.js') });
    const posts = []; page.on('request', request => { if (request.method() === 'POST') posts.push(request.url()); });
    await page.locator('[data-bs-target="#registerVehicleModal"]').click();
    await page.waitForFunction(() => document.querySelector('#registerVehicleModal').dataset.fixtureShown === '1');
    const form = page.locator('#registerVehicleForm');
    const typeInput = page.locator('#type + input');
    const routeInput = page.locator('#route_id + input');
    const button = page.locator('#registerVehicleModal button[type="submit"]');
    const opened = page.locator('.autocomplete-wrapper.is-open');
    assert.equal(await opened.count(), 0, 'opening the registration modal must not open a select');
    await page.locator('#plate_number').focus();
    assert.equal(await form.evaluate(el => el.checkValidity()), false);
    assert.equal(await page.locator('#plate_number').evaluate(el => el === document.activeElement), true, 'checkValidity must not move focus');
    assert.equal(await opened.count(), 0, 'checking validity must not open the route placeholder');
    await button.click();
    assert.equal(await page.locator('#plate_number').evaluate(el => el === document.activeElement), true, 'native validation must focus the first empty field');
    assert.equal(await opened.count(), 0);
    assert.equal(await button.isEnabled(), true);
    assert.deepEqual(posts, []);

    await page.locator('#plate_number').fill('abc-1234');
    await page.locator('#operator_name').fill('fixture operator');
    await page.locator('#driver_name').fill('Fixture Driver');
    await page.locator('#capacity').fill('16');
    assert.equal(await form.evaluate(el => el.reportValidity()), false);
    await page.waitForFunction(() => document.activeElement === document.querySelector('#type + input'));
    assert.equal(await opened.count(), 0, 'validation focus must expose the field without opening its menu');
    await typeInput.press('ArrowDown');
    assert.equal(await opened.count(), 1, 'an explicit keyboard action opens the menu after validation');
    await typeInput.press('ArrowDown');
    await typeInput.press('Enter');
    assert.equal(await page.locator('#type').inputValue(), 'jeepney');
    assert.equal(await opened.count(), 0);
    assert.equal(await page.locator('#route_id option[value="1"]').evaluate(el => !el.disabled && el.style.display !== 'none'), true);
    assert.equal(await form.evaluate(el => el.reportValidity()), false);
    await page.waitForFunction(() => document.activeElement === document.querySelector('#route_id + input'));
    assert.equal(await opened.count(), 0, 'the remaining empty route must not pop open during validation');
    // Check a fresh pointer interaction after leaving validation focus.
    // Escape is not used here because it dismisses the Bootstrap modal.
    await page.locator('#capacity').focus();
    if (touch) await routeInput.tap();
    else await routeInput.click();
    await page.locator('#route_id').locator('..').locator('.autocomplete-dropdown').waitFor({ state: 'visible', timeout: 3000 });
    assert.equal(await opened.count(), 1, `route click ${width} ${height} ${mode}`);
    const routeOption = page.locator('#route_id').locator('..').locator('.autocomplete-item[data-value="1"]');
    if (touch) await routeOption.tap();
    else await routeOption.click();
    assert.equal(await page.locator('#route_id').inputValue(), '1', `route selection ${width} ${height} ${mode}${touch ? ' touch' : ''}`);
    assert.equal(await opened.count(), 0);
    assert.equal(await form.evaluate(el => el.checkValidity()), true);
    await form.evaluate(el => {
      window.fixtureVehicleSubmits = [];
      el.addEventListener('submit', event => {
        event.preventDefault();
        const values = new FormData(el);
        const fields = ['csrf_fixture', 'plate_number', 'operator_name', 'driver_name', 'type', 'route_id', 'capacity', 'status'];
        window.fixtureVehicleSubmits.push(Object.fromEntries(fields.map(name => [name, values.get(name)])));
      });
    });
    await button.click();
    assert.deepEqual(await page.evaluate(() => window.fixtureVehicleSubmits), [{ csrf_fixture: 'unchanged', plate_number: 'ABC-1234', operator_name: 'FIXTURE OPERATOR', driver_name: 'Fixture Driver', type: 'jeepney', route_id: '1', capacity: '16', status: 'active' }]);
    const contract = await form.evaluate(el => ({ action: el.getAttribute('action'), method: el.method, enctype: el.enctype }));
    assert.deepEqual(contract, { action: '/admin/vehicles/store', method: 'post', enctype: 'multipart/form-data' });
    assert.deepEqual(errors, []); assert.deepEqual(posts, []);
    if (width === 320 && mode === 'full') await screenshot(page, 'vehicle-autocomplete-validation', false);
    report.cases.push({ page: 'vehicle autocomplete validation and payload', width, height, mode, passed: true });
    await context.close();
  }
});

test('shared role and terminal selects retain keyboard, click and change behavior without stale menus', async t => {
  for (const theme of ['admin-theme', 'staff-theme', 'guest-theme']) {
    const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
    t.after(() => context.close());
    const page = await context.newPage();
    const errors = []; page.on('pageerror', error => errors.push(error.message));
    await page.setContent(`<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{margin:20px}input{box-sizing:border-box;width:100%;padding:12px}.field{margin-bottom:20px}</style></head><body class="${theme}">
      <form id="sharedForm" action="/fixture-save" method="post">
        <input type="hidden" name="csrf_fixture" value="unchanged">
        <div class="field"><input id="first" name="first" value="Fixture" required></div>
        <div class="field"><select id="role" name="role" required><option value="">-- Select Role --</option><option value="staff">Staff</option></select></div>
        <div class="field"><select id="terminal" name="terminal" required><option value="">-- Select Terminal --</option><option value="1">Palompon</option><option value="2" disabled>Unavailable</option></select></div>
      </form></body></html>`);
    await page.addScriptTag({ path: path.join(root, 'public/assets/js/autocomplete-search.js') });
    await page.evaluate(() => {
      window.fixtureSelectEvents = [];
      for (const select of document.querySelectorAll('select')) {
        for (const type of ['input', 'change']) select.addEventListener(type, () => fixtureSelectEvents.push(select.id + ':' + type));
      }
      initLocationAutocomplete(); // Modal reinitialization must stay idempotent.
    });
    assert.equal(await page.locator('.autocomplete-wrapper').count(), 2);
    await page.locator('#first').focus();
    assert.equal(await page.locator('#sharedForm').evaluate(el => el.checkValidity()), false);
    assert.equal(await page.locator('#first').evaluate(el => el === document.activeElement), true);
    assert.equal(await page.locator('.autocomplete-wrapper.is-open').count(), 0);
    assert.equal(await page.locator('#sharedForm').evaluate(el => el.reportValidity()), false);
    await page.waitForFunction(() => document.activeElement === document.querySelector('#role + input'));
    const roleInput = page.locator('#role + input');
    assert.equal(await page.locator('.autocomplete-wrapper.is-open').count(), 0);
    await roleInput.press('ArrowDown'); await roleInput.press('ArrowDown'); await roleInput.press('Enter');
    assert.equal(await page.locator('#role').inputValue(), 'staff');
    await page.locator('#terminal + input').click();
    assert.equal(await page.locator('#terminal').locator('..').locator('.autocomplete-item[data-value="2"]').count(), 0);
    await page.locator('#terminal').locator('..').locator('.autocomplete-item[data-value="1"]').click();
    assert.equal(await page.locator('#terminal').inputValue(), '1');
    assert.deepEqual(await page.evaluate(() => fixtureSelectEvents), ['role:input', 'role:change', 'terminal:input', 'terminal:change']);
    await roleInput.fill('St');
    await page.locator('#first').focus();
    // Wait beyond the production debounce: an earlier input must not reopen
    // its panel after keyboard focus or native validation moves elsewhere.
    await page.evaluate(() => new Promise(resolve => setTimeout(resolve, 200)));
    assert.equal(await page.locator('.autocomplete-wrapper.is-open').count(), 0);
    assert.equal(await page.locator('#first').evaluate(el => el === document.activeElement), true);
    const values = await page.locator('#sharedForm').evaluate(el => ({ valid: el.checkValidity(), data: Object.fromEntries(new FormData(el)) }));
    assert.deepEqual(values, { valid: true, data: { csrf_fixture: 'unchanged', first: 'Fixture', role: 'staff', terminal: '1' } });
    assert.deepEqual(errors, []);
    report.cases.push({ page: 'shared autocomplete lifecycle', theme, passed: true });
    await context.close();
  }
});

test('settings tabs and terminal bulk selection retain their real state and keyboard controls', { timeout: 60000 }, async t => {
  for (const [width, height] of [[320, 568], [844, 390], [1280, 800]]) {
    const settings = await open(t, 'admin-settings', width, height);
    for (const tab of ['identity', 'media', 'themes', 'footer', 'operations']) {
      const button = settings.page.locator(`#tabBtn-${tab}`); await button.focus(); await button.press('Enter');
      assert.equal(await settings.page.locator(`#tab-${tab}`).isVisible(), true);
      assert.equal(await settings.page.evaluate(() => location.hash), '#' + tab);
      assert.equal(await settings.page.evaluate(() => localStorage.getItem('sb_active_tab')), tab);
      checkBounds(await bounds(settings.page, [`#tab-${tab}`, `#tab-${tab} input:not([type="hidden"]):not([type="checkbox"]):not([type="file"]):not([type="radio"])`, `#tab-${tab} select`, `#tab-${tab} button[type="submit"]`]), `settings ${tab} ${width}`);
      if (width === 320 && tab === 'themes') await screenshot(settings.page, 'settings-colors-phone', false);
    }
    assert.deepEqual(settings.errors, []); await settings.context.close();
    const terminals = await open(t, 'admin-terminals', width, height);
    await terminals.page.locator('#btn-toggle-select-terminals').click();
    const checkbox = terminals.page.locator('.terminal-row-checkbox'); await checkbox.check();
    assert.equal(await terminals.page.locator('#terminal-selected-count').textContent(), '1');
    assert.equal(await terminals.page.locator('#btn-bulk-delete-terminals').isEnabled(), true);
    await terminals.page.locator('#btn-bulk-delete-terminals').click();
    await terminals.page.waitForFunction(() => !!document.querySelector('.modal.show[data-fixture-shown="1"]'));
    checkBounds(await bounds(terminals.page, ['.modal.show .modal-content', '.modal.show .modal-footer']), `bulk selection ${width}`);
    const payload = await terminals.page.locator('.modal.show form').evaluate(form => Object.fromEntries(new FormData(form)));
    assert.equal(payload['ids[]'], '1'); assert.equal(payload.csrf_fixture, 'unchanged');
    await terminals.page.locator('.modal.show [data-bs-dismiss="modal"]').first().click();
    await terminals.page.waitForFunction(() => !document.querySelector('.modal.show') && !document.querySelector('.modal-backdrop'));
    assert.equal(await checkbox.isChecked(), true);
    assert.deepEqual(terminals.errors, []); await terminals.context.close();
    report.cases.push({ page: 'settings and bulk controls', width, height, passed: true });
  }
});

test('guest navigation has no missing breakpoint interval and its real drawer opens and closes', { timeout: 60000 }, async t => {
  for (const width of [320, 390, 768, 900, 901, 1200, 1201, 1280, 1281, 1440, 2560]) {
    const { page, context, errors } = await open(t, 'guest', width, 800);
    const toggle = page.locator('.guest-header .mobile-toggle');
    if (width <= 900) {
      assert.equal(await toggle.isVisible(), true, `toggle at ${width}`);
      await toggle.click();
      await page.locator('#navMenu').waitFor({ state: 'visible', timeout: 2000 });
      try {
        for (const href of ['/guest', '/schedules', '/fares', '/login']) {
          await page.locator(`#navMenu > a[href="${href}"]`).waitFor({ state: 'visible', timeout: 2000 });
        }
      } catch (error) {
        report.drawerFailure = await page.evaluate(() => {
          const elements = [...document.querySelectorAll('#navMenu, #navMenu > a, .mobile-toggle')];
          return elements.map(el => { const s = getComputedStyle(el), r = el.getBoundingClientRect(); return { tag: el.tagName, class: el.className, href: el.getAttribute('href'), display: s.display, visibility: s.visibility, transform: s.transform, width: r.width, height: r.height, x: r.x, y: r.y }; });
        });
        console.log('Drawer failure diagnostics:', JSON.stringify(report.drawerFailure));
        await screenshot(page, `drawer-failure-${width}`);
        throw error;
      }
      await page.locator('.guest-nav-close-btn').click();
      await page.waitForFunction(() => !document.querySelector('.mobile-toggle').classList.contains('is-closing'));
      assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
    } else {
      assert.equal(await toggle.isVisible(), false, `desktop toggle at ${width}`);
      for (const href of ['/guest', '/schedules', '/fares', '/login']) assert.equal(await page.locator(`#navMenu > a[href="${href}"]`).isVisible(), true, `${href} at ${width}`);
    }
    checkBounds(await bounds(page, ['.guest-header', '.guest-header .logo', '#headerClock']), `header ${width}`);
    report.cases.push({ page: 'guest navigation', width, height: 800, passed: true });
    if (width === 1280) await screenshot(page, 'guest-tablet');
    assert.deepEqual(errors, []); await context.close();
  }
});

test('authentication fields fit small and landscape screens while retaining their real controls', { timeout: 60000 }, async t => {
  for (const name of ['login', 'forgot', 'reset', 'verify']) {
    for (const [width, height] of [[320, 568], [390, 844], [768, 1024], [844, 390], [1440, 900]]) {
      const { page, context, errors } = await open(t, name, width, height);
      const entrance = await page.locator(name === 'login' ? '.login-card .card-head' : '.auth > .card').evaluate(el => {
        const style = getComputedStyle(el);
        return Math.max(...style.animationDuration.split(',').map((duration, i) => parseFloat(duration) + parseFloat(style.animationDelay.split(',')[i] || style.animationDelay.split(',')[0]))) * 1000;
      });
      assert.ok(entrance <= 300, `${name} entrance ${entrance}ms`);
      checkBounds(await bounds(page, ['.brand', '.brand-name', '.brand-title', '.brand-sub', '.login-card', '.auth > .card', 'form input:not([type="hidden"]):not([type="checkbox"])', 'form button[type="submit"]']), `${name} ${width}x${height}`);
      if (width === 320 || width === 1440) {
        const logo = await page.locator('.brand-mark img').evaluate(async img => {
          await new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = () => reject(new Error('Default WebP logo failed to load'));
            img.src = '/images/logo.webp';
          }).finally(() => { img.onload = null; img.onerror = null; });
          await img.decode();
          const style = getComputedStyle(img);
          return { width: img.naturalWidth, height: img.naturalHeight, fit: style.objectFit, radius: style.borderRadius, overflow: getComputedStyle(img.parentElement).overflow };
        });
        assert.deepEqual(logo, { width: 1536, height: 1393, fit: 'contain', radius: '0px', overflow: 'visible' }, `${name} default WebP logo ${width}`);
      }
      const controls = await page.locator('form input:not([type="hidden"]):not([type="checkbox"])').all();
      assert.ok(controls.length);
      for (const input of controls) { await input.focus(); assert.equal(await input.evaluate(el => document.activeElement === el), true); }
      for (const toggle of await page.locator('.field.password .toggle').all()) {
        const padding = await toggle.evaluate(el => {
          const input = el.parentElement.querySelector('input'), r = input.getBoundingClientRect(), b = el.getBoundingClientRect();
          return { actual: parseFloat(getComputedStyle(input).paddingRight), needed: r.right - b.left };
        });
        assert.ok(padding.actual >= padding.needed, JSON.stringify(padding));
        await toggle.click(); assert.equal(await toggle.getAttribute('aria-pressed'), 'true');
      }
      if (name === 'verify') {
        const otp = page.locator('.otp-input');
        assert.equal(await otp.count(), 6);
        for (let i = 0; i < 6; i++) await otp.nth(i).fill(String(i + 1));
        assert.equal(await page.locator('input[name="reset_code"]').inputValue(), '123456');
        assert.equal(await page.locator('#btnVerify').isEnabled(), true);
      }
      if (name === 'login') {
        await page.locator('#username').fill('fixture-user'); await page.locator('#password').fill('fixture-password');
        const payload = await page.evaluate(() => Object.fromEntries(new FormData(document.querySelector('#loginForm'))));
        assert.equal(payload.username, 'fixture-user'); assert.equal(payload.password, 'fixture-password'); assert.equal(payload.csrf_fixture, 'unchanged');
      }
      report.cases.push({ page: name, width, height, passed: true });
      if (name === 'login' && width === 320) await screenshot(page, 'login-phone');
      assert.deepEqual(errors, []); await context.close();
    }
  }
});

test('guest content and operational table values fit without hiding long data', { timeout: 60000 }, async t => {
  for (const name of ['guest', 'fares', 'schedules', 'search', 'staff-schedules', 'admin-schedules', 'staff-queue']) {
    for (const [width, height] of [[320, 568], [768, 1024], [844, 390], [1280, 800]]) {
      const { page, context, errors } = await open(t, name, width, height);
      checkBounds(await bounds(page, ['body > .container', '.main-content', '.queue-card', '.schedule-card', '.fare-card', '.q-card']), `${name} ${width}`);
      if (name === 'search') {
        const departure = await page.locator('.results-table td[data-label="Est. Departure"]').evaluate(td => {
          const time = td.querySelector('.time-display'), passengers = td.querySelector('.passenger-count-num').parentElement;
          const range = document.createRange(); range.selectNodeContents(time);
          const lines = new Set([...range.getClientRects()].filter(r => r.width > 1).map(r => Math.round(r.top)));
          const cell = td.getBoundingClientRect(), clock = time.getBoundingClientRect(), count = passengers.getBoundingClientRect();
          return {
            time: time.textContent.trim(), passengers: passengers.textContent.replace(/\s+/g, ' ').trim(),
            lines: lines.size, stacked: count.top >= clock.bottom - 1,
            fits: [clock, count].every(r => r.left >= cell.left - 1 && r.right <= cell.right + 1),
          };
        });
        assert.deepEqual(departure, { time: '9:00 AM', passengers: '8/20 passengers', lines: 1, stacked: true, fits: true }, `search departure ${width}: ${JSON.stringify(departure)}`);
        if (width === 320) {
          await page.locator('.results-table td[data-label="Est. Departure"]').scrollIntoViewIfNeeded();
          await screenshot(page, 'home-search-departure-phone', false);
        }
      }
      if (width <= 768 && name.endsWith('schedules') && name !== 'schedules') {
        const result = await page.locator('td[data-label]').evaluateAll(cells => cells.map(td => {
          const label = getComputedStyle(td, '::before');
          const ctx = document.createElement('canvas').getContext('2d');
          ctx.font = `${label.fontWeight} ${label.fontSize} ${label.fontFamily}`;
          const text = label.textTransform === 'uppercase' ? td.dataset.label.toUpperCase() : td.dataset.label;
          const textWidth = ctx.measureText(text).width + (parseFloat(label.letterSpacing) || 0) * Math.max(0, text.length - 1);
          const available = parseFloat(label.width);
          return {
            label: td.dataset.label,
            labelClipped: label.whiteSpace === 'nowrap' && Number.isFinite(available) && textWidth > available + 1,
            clipped: td.scrollWidth > td.clientWidth + 1 || [...td.children].some(el => {
              if (el.clientWidth > 0 && el.scrollWidth > el.clientWidth + 1) return true;
              if (!el.matches('.schedule-route-display')) return false;
              const outer = el.getBoundingClientRect();
              return [...el.children].some(child => { const r = child.getBoundingClientRect(); return r.left < outer.left - 1 || r.right > outer.right + 1; });
            }),
          };
        }));
        assert.ok(result.length); assert.ok(result.every(r => !r.clipped && !r.labelClipped), JSON.stringify(result));
      }
      const hiddenClear = page.locator('.guest-clear-search-btn');
      if (await hiddenClear.count()) for (const btn of await hiddenClear.all()) assert.equal(await btn.isVisible(), false);
      report.cases.push({ page: name, width, height, passed: true });
      if (name === 'staff-schedules' && width === 320) {
        await page.locator('td[data-label="Route"]').scrollIntoViewIfNeeded();
        await screenshot(page, 'staff-phone', false);
      }
      assert.deepEqual(errors, []); await context.close();
    }
  }
});

test('profile menus remain visible and usable on phone, landscape and desktop layouts', { timeout: 120000 }, async t => {
  for (const name of ['admin-users', 'admin-settings', 'staff-queue']) {
    for (const [width, height] of [[320, 568], [390, 844], [844, 390], [1280, 800]]) {
      for (const mode of ['full', 'lite', 'reduced']) {
        const { page, context, errors } = await open(t, name, width, height, '', mode);
        const trigger = page.locator('#userProfileBtn'), menu = page.locator('#userProfileMenu');
        await trigger.click();
        assert.equal(await trigger.getAttribute('aria-expanded'), 'true');
        await menu.waitFor({ state: 'visible' });
        await page.evaluate(() => Promise.all(document.getAnimations().filter(a => a.effect?.getTiming().iterations !== Infinity).map(a => a.finished.catch(() => {}))));
        const menuState = await menu.evaluate(el => {
          const rect = el.getBoundingClientRect(), style = getComputedStyle(el);
          const x = rect.left + rect.width / 2, y = Math.min(rect.bottom - 8, innerHeight - 8);
          const hit = document.elementFromPoint(x, y);
          return { rect: { left: rect.left, right: rect.right, top: rect.top, bottom: rect.bottom }, opacity: style.opacity, hit: !!hit && el.contains(hit), viewport: { client: document.documentElement.clientWidth, css: getComputedStyle(document.documentElement).getPropertyValue('--tq-viewport-width'), header: document.querySelector('#site-header').getBoundingClientRect().width } };
        });
        assert.equal(menuState.opacity, '1');
        assert.ok(menuState.rect.left >= 0 && menuState.rect.right <= width && menuState.rect.top >= 0 && menuState.rect.bottom <= height, JSON.stringify({ name, width, height, mode, menuState }));
        assert.ok(menuState.hit, 'profile menu is clipped or covered: ' + JSON.stringify({ name, width, height, mode, menuState }));
        const avatar = menu.locator('#btnHeaderAvatarClick');
        await avatar.click();
        await menu.locator('#avatarMiniMenu').waitFor({ state: 'visible' });
        await menu.locator('#miniMenuEditBtn').click({ trial: true });
        await avatar.click();
        assert.equal(await menu.locator('#avatarMiniMenu').isVisible(), false);
        if (width === 390 && mode === 'lite') await screenshot(page, name + '-profile-phone', false);
        const logout = menu.locator('[data-bs-target="#logoutModal"]');
        await logout.scrollIntoViewIfNeeded();
        await logout.click({ trial: true });
        await page.keyboard.press('Escape');
        assert.equal(await trigger.getAttribute('aria-expanded'), 'false');
        await menu.waitFor({ state:'hidden' });
        assert.equal(await menu.isVisible(), false);
        await trigger.focus(); await page.keyboard.press('Enter');
        assert.equal(await trigger.getAttribute('aria-expanded'), 'true');
        await page.mouse.click(1, Math.min(height - 1, 200));
        assert.equal(await trigger.getAttribute('aria-expanded'), 'false');
        assert.deepEqual(errors, []);
        report.cases.push({ page: name, control: 'profile', width, height, mode, passed: true });
        await context.close();
      }
    }
  }
});

test('low-hardware and reduced-motion policies work in each browser engine', async t => {
  for (const mode of ['lite', 'reduced']) {
    const { page, context, errors } = await open(t, 'login', 390, 844, '', mode);
    assert.equal(await page.evaluate(() => TerminalMotion.getMode()), mode);
    await page.locator('#username').fill('fixture-user'); await page.locator('#password').fill('fixture-password');
    const pending = await page.evaluate(() => {
      const btn = document.querySelector('#loginForm button[type="submit"]'); btn.focus();
      GlobalLoader.showButtonSpinner(btn, 'Signing in…', true);
      return { focus: document.activeElement === btn, nativeEnabled: !btn.disabled, spinnerAnimation: getComputedStyle(btn.querySelector('.gl-btn-spinner')).animationName };
    });
    assert.deepEqual(pending, { focus: true, nativeEnabled: true, spinnerAnimation: mode === 'lite' ? 'gl-spin' : 'none' });
    assert.deepEqual(errors, []); await context.close();
  }
});

test('before-after fixtures reproduce breakpoint and input-padding defects', async t => {
  for (const baseline of [true, false]) {
    const { page, context } = await open(t, 'guest', 1280, 800, baseline ? 'baseline=1' : '');
    report.beforeAfter[baseline ? 'beforeNavLinks' : 'afterNavLinks'] = await page.locator('#navMenu > a').evaluateAll(links => links.filter(a => getComputedStyle(a).display !== 'none').length);
    await context.close();
    const login = await open(t, 'login', 390, 844, baseline ? 'baseline=1' : '');
    report.beforeAfter[baseline ? 'beforePasswordPadding' : 'afterPasswordPadding'] = await login.page.locator('#password').evaluate(el => parseFloat(getComputedStyle(el).paddingRight));
    await login.context.close();
    const verify = await open(t, 'verify', 320, 568, baseline ? 'baseline=1' : '');
    report.beforeAfter[baseline ? 'beforeOtpPadding' : 'afterOtpPadding'] = await verify.page.locator('.otp-input').first().evaluate(el => parseFloat(getComputedStyle(el).paddingLeft) + parseFloat(getComputedStyle(el).paddingRight));
    await verify.context.close();
    const schedule = await open(t, 'staff-schedules', 320, 568, baseline ? 'long=1&baseline=1' : 'long=1');
    const route = schedule.page.locator('.schedule-route-display');
    await route.scrollIntoViewIfNeeded();
    report.beforeAfter[baseline ? 'beforeRouteClipped' : 'afterRouteClipped'] = await route.evaluate(el => {
      const outer = el.getBoundingClientRect();
      return el.scrollWidth > el.clientWidth + 1 || [...el.children].some(child => { const r = child.getBoundingClientRect(); return r.left < outer.left - 1 || r.right > outer.right + 1; });
    });
    await schedule.context.close();
  }
  console.log('Responsive before-after:', JSON.stringify(report.beforeAfter));
  assert.equal(report.beforeAfter.beforeNavLinks, 1); assert.equal(report.beforeAfter.afterNavLinks, 4);
  assert.ok(report.beforeAfter.beforePasswordPadding < 48); assert.equal(report.beforeAfter.afterPasswordPadding, 56);
  assert.ok(report.beforeAfter.beforeOtpPadding > 0); assert.equal(report.beforeAfter.afterOtpPadding, 0);
  assert.equal(report.beforeAfter.beforeRouteClipped, true); assert.equal(report.beforeAfter.afterRouteClipped, false);
});

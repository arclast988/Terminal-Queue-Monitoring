'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync } = require('node:child_process');
const playwright = require('playwright');
const { root, assetContentType } = require('./harness.cjs');
const engine = process.env.TQ_BROWSER || 'chromium';
const fixtures = new Map();
let browser, server, origin, savedMode = 'slideshow', saveRequests = 0;

function fixture(name, mode) {
  const key = name + ':' + mode;
  if (!fixtures.has(key)) {
    let html = execFileSync('php', [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme', '', mode], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
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
      const data = { category: 'background', app_bg_mode: savedMode, app_background_image: '/fixture.svg', app_bg_slideshow: ['/fixture.svg', '/fixture-second.svg', '/fixture-third.svg'] };
      res.setHeader('Content-Type', 'application/json');
      res.end(JSON.stringify({ success: true, data, ...data })); return;
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
    const base = url.pathname.startsWith('/bootstrap/') ? path.join(__dirname, 'node_modules/bootstrap/dist') : path.join(root, 'public');
    const relative = url.pathname.startsWith('/bootstrap/') ? url.pathname.slice('/bootstrap/'.length) : url.pathname.slice(1);
    const file = path.resolve(base, relative);
    if (!file.startsWith(base + path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.writeHead(404); res.end(); return; }
    res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
  browser = await playwright[engine].launch({ headless: true });
});

test.after(async () => {
  if (browser) await browser.close();
  if (server) { server.closeAllConnections(); await new Promise(resolve => server.close(resolve)); }
});

async function open(t, name, mode, width = 1280) {
  const context = await browser.newContext({ viewport: { width, height: 900 } });
  t.after(() => context.close());
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

test('a saved plain background can switch back to a rotating background through both live update paths', { timeout: 15000 }, async t => {
  const { page, errors } = await open(t, 'admin-settings', 'none');
  for (const method of ['applyLiveBgMode', 'applyLiveBranding']) {
    await page.evaluate(method => {
      if (method === 'applyLiveBgMode') window[method]('slideshow', '/fixture.svg', ['/fixture.svg', '/fixture-second.svg']);
      else window[method]({ category: 'background', app_bg_mode: 'slideshow', app_bg_slideshow: ['/fixture.svg', '/fixture-second.svg'] });
      const animation = document.getAnimations().find(item => /BgSlideshowLive$/.test(item.animationName));
      if (!animation) throw new Error('Slideshow did not resume');
      animation.pause(); animation.currentTime = 6000;
    }, method);
    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
    assert.match((await background(page))[0].image, /fixture-second\.svg/);
    await page.evaluate(method => method === 'applyLiveBgMode' ? window[method]('none') : window[method]({ category: 'background', app_bg_mode: 'none' }), method);
    for (const style of await background(page)) assert.equal(style.content, 'none');
  }
  assert.deepEqual(errors, []);
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

'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const { harness, flush } = require('./harness.cjs');
const loader = 'public/assets/js/global-loader.js';
const desktop = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/143.0.0.0 Safari/537.36';
const android = 'Mozilla/5.0 (Linux; Android 15; Mobile) AppleWebKit/537.36 Chrome/143.0.0.0 Mobile Safari/537.36';
const iphone = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 Version/18.0 Mobile/15E148 Safari/604.1';
function loaded(options = {}) {
  const h = harness({ standalone: options.standalone, mobile: options.narrow });
  Object.assign(h.window.navigator, {
    userAgent: options.ua || desktop,
    platform: options.platform || '',
    maxTouchPoints: options.touchPoints || 0,
    ...(options.mobileHint === undefined && !options.hintPlatform ? {} : { userAgentData: { mobile: options.mobileHint, platform: options.hintPlatform } }),
    ...(options.iosStandalone ? { standalone: true } : {})
  });
  h.window.top = options.embedded ? {} : h.window;
  h.window.location.pathname = '/queue'; h.window.location.search = '';
  h.load(loader);
  h.tick(500);
  return h;
}
async function navigate(h, before) {
  const link = h.document.body.appendChild(new h.Element('a'));
  link.href = 'https://terminal.test/schedules'; link.setAttribute('href', '/schedules');
  if (before) before(link);
  const event = h.document.emit('click', { target: link, button: 0 });
  await flush();
  return event;
}
function visible(h) {
  return h.window.GlobalLoader.isVisible()
    && h.document.getElementById('global-progress-bar').classList.contains('is-active');
}

test('known mobile browser profiles leave ordinary link navigation to browser feedback', async () => {
  for (const ua of [android, iphone,
    'Mozilla/5.0 (Android 15; Mobile; rv:145.0) Gecko/145.0 Firefox/145.0',
    android.replace('Chrome/143.0.0.0', 'SamsungBrowser/29.0 Chrome/143.0.0.0'),
    iphone.replace('Version/18.0', 'CriOS/143.0.0.0'),
    iphone.replace('Version/18.0', 'FxiOS/145.0')]) {
    const h = loaded({ ua });
    const event = await navigate(h);
    assert.equal(event.defaultPrevented, false);
    assert.equal(visible(h), false, ua);
    assert.equal(h.window.GlobalLoader.isRunning(), false);
    h.tick(2000);
    assert.equal(visible(h), false);
  }
});

test('client hints and iPad desktop profiles use the mobile navigation fallback', async () => {
  for (const options of [
    { ua: desktop, mobileHint: true },
    { ua: desktop.replace('Windows NT 10.0; Win64; x64','X11; Linux x86_64'), mobileHint: false, hintPlatform: 'Android', touchPoints: 5 },
    { ua: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 Version/18.0 Safari/605.1.15', platform: 'MacIntel', touchPoints: 5 }
  ]) {
    const h = loaded(options); await navigate(h); assert.equal(visible(h), false);
  }
});

test('desktop tabs show delayed site progress, including narrow and touchscreen layouts', async () => {
  for (const options of [{}, { narrow: true }, { touchPoints: 10, mobileHint: false }]) {
    const h = loaded(options); const event = await navigate(h);
    assert.equal(event.defaultPrevented, false); assert.equal(visible(h), false);
    assert.equal(h.window.GlobalLoader.isRunning(), true);
    h.tick(499); assert.equal(visible(h), false);
    h.tick(1); assert.equal(visible(h), true);
  }
});

test('installed apps, embedded pages, WebViews and unknown browsers keep navigation feedback', async () => {
  for (const options of [
    { ua: android, standalone: true }, { ua: iphone, iosStandalone: true },
    { ua: android, embedded: true },
    { ua: android.replace('Android 15;', 'Android 15; wv;') },
    { ua: iphone.replace(' Safari/604.1', '') },
    { ua: android + ' Instagram 999.0' },
    { ua: 'UnknownMobileBrowser/1.0 Mobile' }
  ]) {
    const h = loaded(options); await navigate(h); h.tick(500); assert.equal(visible(h), true, JSON.stringify(options));
  }
});

test('fullscreen and minimal-ui app windows retain their own loading feedback', async () => {
  for (const displayMode of ['fullscreen', 'minimal-ui', 'window-controls-overlay']) {
    const h = loaded({ ua: android });
    const original = h.window.matchMedia;
    h.window.matchMedia = query => query === '(display-mode: ' + displayMode + ')' ? { matches: true } : original(query);
    await navigate(h); h.tick(500); assert.equal(visible(h), true, displayMode);
  }
});

test('mobile native form submission preserves fields and button feedback without the site bar', async () => {
  const h = loaded({ ua: android });
  const form = h.document.body.appendChild(new h.Element('form'));
  form.setAttribute('method', 'post');
  const button = form.appendChild(new h.Element('button')); button.form = form;
  button.setAttribute('type', 'submit'); button.setAttribute('name', 'action');
  button.setAttribute('value', 'save-vehicle'); button.textContent = 'Save';
  const event = h.document.emit('submit', { target: form, submitter: button }); await flush();
  assert.equal(event.defaultPrevented, false); assert.equal(visible(h), false);
  assert.equal(button.getAttribute('value'), 'save-vehicle');
  assert.equal(button.getAttribute('name'), 'action');
  assert.equal(button.getAttribute('aria-busy'), 'true');
  assert.equal(button.querySelector('.gl-btn-label').textContent, 'Saving\u2026');
  const repeat = h.document.emit('submit', { target: form, submitter: button });
  assert.equal(repeat.defaultPrevented, true);
  h.window.emit('pagehide');
  assert.equal(button.getAttribute('aria-busy'), null);
});

test('mobile browser in-page requests retain local feedback without the rainbow bar', async () => {
  const h = loaded({ ua: android });
  const response = { ok: true };
  const button=h.document.body.appendChild(new h.Element('button'));
  h.window.GlobalLoader.showButtonSpinner(button,'Saving',true);
  const request = h.window.fetch('/api/vehicles'); h.tick(501);
  assert.equal(visible(h), false);
  assert.equal(button.getAttribute('aria-busy'),'true');
  h.requests[0].resolve(response); assert.equal(await request, response); h.tick(500);
  assert.equal(visible(h), false);
  const xhr = new h.window.XMLHttpRequest(); xhr.open('POST', '/api/vehicles'); xhr.send('original');
  h.tick(501); assert.equal(visible(h), false);
  assert.deepEqual(xhr.sendArgs, ['original']);
  xhr.emit('loadend'); h.tick(500); assert.equal(visible(h), false);
});

test('mobile navigation and concurrent requests do not introduce a redundant bar', async () => {
  const h = loaded({ ua: android });
  const response = { ok: true };
  const request = h.window.fetch('/api/vehicles'); h.tick(501); assert.equal(visible(h), false);
  await navigate(h); assert.equal(visible(h), false);
  h.requests[0].resolve(response); assert.equal(await request, response); h.tick(500);
  assert.equal(visible(h), false);
});

test('canceled links, downloads and excluded links remain untouched', async () => {
  for (const setup of [link => link.setAttribute('download', ''), link => link.setAttribute('data-no-loader', '')]) {
    const h = loaded(); const event = await navigate(h, setup);
    h.tick(1000); assert.equal(event.defaultPrevented, false); assert.equal(visible(h), false);
  }
  const h = loaded(); h.document.addEventListener('click', event => event.preventDefault());
  const event = await navigate(h); h.tick(1000); assert.equal(event.defaultPrevented, true); assert.equal(visible(h), false);
});

test('a fast desktop page change never flashes a bar after the page has left', async () => {
  const h = loaded();
  await navigate(h); h.tick(100); h.window.emit('pagehide'); h.tick(1000);
  assert.equal(visible(h), false); assert.equal(h.timers.size, 0);
});

test('a background response cannot finish a pending desktop navigation indicator', async () => {
  const h = loaded();
  const request = h.window.fetch('/api/vehicles');
  await navigate(h);
  h.requests[0].resolve({ ok: true }); await request; h.tick(600);
  assert.equal(visible(h), true);
  h.window.emit('pagehide'); assert.equal(visible(h), false);
});

test('slow tracked requests keep feedback until they actually finish', async () => {
  const h = loaded(); const request = h.window.fetch('/api/vehicles');
  h.tick(13000); assert.equal(visible(h),true);
  h.requests[0].resolve({ok:true}); await request; h.tick(500);
  assert.equal(visible(h),false); assert.equal(h.timers.size,0);
});

test('a visible desktop navigation resumes on arrival and fills completely before fading', async () => {
  const source = loaded(); await navigate(source); source.tick(500);
  source.window.emit('pagehide');
  const arrival = harness();
  arrival.window.navigator.userAgent = desktop;
  arrival.window.sessionStorage = source.window.sessionStorage;
  arrival.tick(source.window.Date.now() - arrival.window.Date.now());
  arrival.load(loader);
  const bar = arrival.document.getElementById('global-progress-bar');
  assert.equal(visible(arrival),true);
  arrival.tick(100);
  assert.equal(arrival.document.querySelector('.global-progress-bar-inner').style.transform,'scaleX(1)');
  assert.equal(bar.classList.contains('is-done'),false);
  arrival.tick(194); assert.equal(bar.classList.contains('is-done'),true);
  arrival.tick(160); assert.equal(visible(arrival),false);
  assert.equal(arrival.timers.size,0);
  assert.equal(arrival.window.sessionStorage.getItem('gl_navigation_progress'),null);
});

test('reinitialization and restored pages preserve one loader and usable feedback', async () => {
  const h = loaded({ ua: android }); h.load(loader);
  assert.equal(h.document.querySelectorAll('#global-progress-bar').length, 1);
  await navigate(h); h.window.emit('pageshow', { persisted: true });
  const request = h.window.fetch('/api/vehicles'); h.tick(501); assert.equal(visible(h), false);
  h.requests[0].resolve({ ok: true }); await request; h.tick(500); assert.equal(visible(h), false);
});

test('late in-page requests cannot restart a second bar during browser navigation', async () => {
  const h = loaded({ ua: android });
  await navigate(h);
  const request = h.window.fetch('/api/vehicles'); h.tick(501);
  assert.equal(visible(h), false);
  h.requests[0].resolve({ ok: true }); await request;
  h.window.emit('pageshow', { persisted: true });
  const next = h.window.fetch('/api/vehicles'); h.tick(501);
  assert.equal(visible(h), false);
  h.requests[1].resolve({ ok: true }); await next;
});

test('programmatic navigation uses the same browser policy and abort restores feedback', async () => {
  const h = harness();
  h.window.navigator.userAgent = android;
  h.window.top = h.window;
  const navigation = new h.Element('navigation'); h.window.navigation = navigation;
  h.load(loader);
  const signal = new h.Element('signal');
  const event = navigation.emit('navigate', { destination: { sameDocument: false }, signal });
  await flush(); assert.equal(event.defaultPrevented, false);
  const request = h.window.fetch('/api/vehicles'); h.tick(501); assert.equal(visible(h), false);
  signal.emit('abort'); h.tick(501); assert.equal(visible(h), false);
  h.requests[0].resolve({ ok: true }); await request;
});

function fill(h) { return Number(h.document.querySelector('.global-progress-bar-inner').style.transform.match(/scaleX\(([^)]+)\)/)[1]); }

test('slow loads advance repeatedly and complete only when every tracked request finishes', async () => {
  const h=loaded();
  const first=h.window.fetch('/save'), second=h.window.fetch('/search');
  h.tick(500);const start=fill(h);
  h.tick(160);const next=fill(h);
  h.tick(1000);const later=fill(h);
  assert.ok(start>0 && next>start && later>next && later<1);
  h.requests[0].resolve({ok:true});await first;
  h.tick(2000);assert.equal(visible(h),true);assert.ok(fill(h)>later && fill(h)<1);
  h.tick(60000);assert.ok(fill(h)<=0.94);assert.equal(h.window.GlobalLoader.isRunning(),true);
  h.requests[1].resolve({ok:true});await second;h.tick(500);
  assert.equal(visible(h),false);assert.equal(h.timers.size,0);
});

test('navigation fill advances before the destination responds and leaves no work after pagehide', async () => {
  const h=loaded();await navigate(h);h.tick(500);const start=fill(h);
  h.tick(1000);assert.ok(fill(h)>start && fill(h)<1);
  h.window.emit('pagehide');assert.equal(h.timers.size,0);
  const saved=JSON.parse(h.window.sessionStorage.getItem('gl_navigation_progress'));
  assert.ok(saved.progress>start*100 && saved.progress<100);
});

test('known XHR transfer totals advance the fill and stale events cannot affect a later operation', () => {
  const h=loaded(),xhr=new h.window.XMLHttpRequest();
  xhr.open('GET','/download');xhr.send();h.tick(500);
  const initial=fill(h);
  xhr.emit('progress',{lengthComputable:true,loaded:75,total:100});
  assert.ok(fill(h)>initial && fill(h)>=0.735 && fill(h)<1);
  xhr.emit('progress',{lengthComputable:false,loaded:0,total:0});
  assert.ok(fill(h)>=0.735);
  xhr.emit('loadend');h.tick(500);assert.equal(h.timers.size,0);
  h.window.GlobalLoader.start(true);const next=fill(h);
  xhr.emit('progress',{lengthComputable:true,loaded:100,total:100});
  assert.equal(fill(h),next);assert.equal(xhr.count('progress'),0);
  h.window.emit('pagehide');assert.equal(h.timers.size,0);
});

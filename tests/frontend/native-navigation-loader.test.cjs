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
    ...(options.mobileHint === undefined ? {} : { userAgentData: { mobile: options.mobileHint } }),
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
    { ua: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 Version/18.0 Safari/605.1.15', platform: 'MacIntel', touchPoints: 5 }
  ]) {
    const h = loaded(options); await navigate(h); assert.equal(visible(h), false);
  }
});

test('Chrome tabs use browser progress with desktop, narrow, and touch profiles', async () => {
  for (const options of [{}, { narrow: true }, { touchPoints: 10, mobileHint: false }]) {
    const h = loaded(options); const event = await navigate(h);
    assert.equal(event.defaultPrevented, false); assert.equal(visible(h), false);
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
    const h = loaded(options); await navigate(h); assert.equal(visible(h), true, JSON.stringify(options));
  }
});

test('fullscreen and minimal-ui app windows retain their own loading feedback', async () => {
  for (const displayMode of ['fullscreen', 'minimal-ui', 'window-controls-overlay']) {
    const h = loaded({ ua: android });
    const original = h.window.matchMedia;
    h.window.matchMedia = query => query === '(display-mode: ' + displayMode + ')' ? { matches: true } : original(query);
    await navigate(h); assert.equal(visible(h), true, displayMode);
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

test('in-page fetch and XHR requests still receive loading feedback on mobile', async () => {
  const h = loaded({ ua: android });
  const response = { ok: true };
  const request = h.window.fetch('/api/vehicles'); h.tick(251);
  assert.equal(visible(h), true);
  h.requests[0].resolve(response); assert.equal(await request, response); h.tick(500);
  assert.equal(visible(h), false);
  const xhr = new h.window.XMLHttpRequest(); xhr.open('POST', '/api/vehicles'); xhr.send('original');
  h.tick(251); assert.equal(visible(h), true);
  assert.deepEqual(xhr.sendArgs, ['original']);
  xhr.emit('loadend'); h.tick(500); assert.equal(visible(h), false);
});

test('a mobile navigation clears an existing site bar without changing the pending request', async () => {
  const h = loaded({ ua: android });
  const response = { ok: true };
  const request = h.window.fetch('/api/vehicles'); h.tick(251); assert.equal(visible(h), true);
  await navigate(h); assert.equal(visible(h), false);
  h.requests[0].resolve(response); assert.equal(await request, response); h.tick(500);
  assert.equal(visible(h), false);
});

test('canceled links, downloads and excluded links remain untouched', async () => {
  for (const setup of [link => link.setAttribute('download', ''), link => link.setAttribute('data-no-loader', '')]) {
    const h = loaded(); const event = await navigate(h, setup);
    assert.equal(event.defaultPrevented, false); assert.equal(visible(h), false);
  }
  const h = loaded(); h.document.addEventListener('click', event => event.preventDefault());
  const event = await navigate(h); assert.equal(event.defaultPrevented, true); assert.equal(visible(h), false);
});

test('reinitialization and restored pages preserve one loader and usable feedback', async () => {
  const h = loaded({ ua: android }); h.load(loader);
  assert.equal(h.document.querySelectorAll('#global-progress-bar').length, 1);
  await navigate(h); h.window.emit('pageshow', { persisted: true });
  const request = h.window.fetch('/api/vehicles'); h.tick(251); assert.equal(visible(h), true);
  h.requests[0].resolve({ ok: true }); await request; h.tick(500); assert.equal(visible(h), false);
});

test('late in-page requests cannot restart a second bar during browser navigation', async () => {
  const h = loaded({ ua: android });
  await navigate(h);
  const request = h.window.fetch('/api/vehicles'); h.tick(251);
  assert.equal(visible(h), false);
  h.requests[0].resolve({ ok: true }); await request;
  h.window.emit('pageshow', { persisted: true });
  const next = h.window.fetch('/api/vehicles'); h.tick(251);
  assert.equal(visible(h), true);
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
  const request = h.window.fetch('/api/vehicles'); h.tick(251); assert.equal(visible(h), false);
  signal.emit('abort'); h.tick(251); assert.equal(visible(h), true);
  h.requests[0].resolve({ ok: true }); await request;
});

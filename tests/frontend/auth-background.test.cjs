'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const { harness } = require('./harness.cjs');

function fixture(options = {}) {
  const h = harness(options);
  const root = h.document.body.appendChild(new h.Element()); root.id = 'authBgSlideshow';
  const layers = [0, 1].map(() => root.appendChild(new h.Element('img')));
  layers[0].classList.add('is-active'); layers[0].src = '/first.webp';
  if (options.raf) {
    h.window.requestAnimationFrame = fn => h.window.setTimeout(fn, 16);
    h.window.cancelAnimationFrame = id => h.window.clearTimeout(id);
  }
  if (options.legacy) {
    h.preference.addListener = fn => h.preference.addEventListener('legacy-change', fn);
    h.preference.removeListener = fn => h.preference.removeEventListener('legacy-change', fn);
    h.window.matchMedia = () => ({
      get matches() { return h.preference.matches; },
      addListener: h.preference.addListener, removeListener: h.preference.removeListener
    });
  }
  h.load('public/assets/js/interaction-motion.js');
  h.load('public/assets/js/auth-background.js');
  const config = { slides: ['/first.webp', '/second.webp', '/third.webp'], defaults: ['/default.webp'] };
  const dispose = h.window.TerminalAuthBackground.mount(config);
  return { ...h, root, layers, config, dispose };
}
function branding(h, data) { h.document.emit('pttm:branding-applied', { detail: data }); }

test('constrained and reduced modes retain the photo and live branding without slideshow wakeups', () => {
  for (const options of [{ cores: 2 }, { memory: 2 }, { reduced: true }]) {
    const h = fixture(options);
    assert.equal(h.timers.size, 0); h.tick(60000);
    assert.equal(h.layers[0].src, '/first.webp');
    branding(h, { category: 'background', app_bg_mode: 'single', app_background_image: '/custom.webp' });
    assert.equal(h.layers[0].src, '/custom.webp'); assert.equal(h.root.style.opacity, '0.28');
    assert.equal(h.timers.size, 0); h.dispose();
  }
});

test('hidden tabs cancel pending work and stale image callbacks cannot switch the visible photo', () => {
  const h = fixture(); assert.equal(h.timers.size, 1);
  h.tick(6000); const staleLoad = h.layers[1].onload;
  h.document.hidden = true; h.document.emit('visibilitychange');
  assert.equal(h.timers.size, 0); assert.equal(h.layers[1].onload, null);
  staleLoad(); h.tick(60000);
  assert.equal(h.layers[0].classList.contains('is-active'), true);
  h.document.hidden = false; h.document.emit('visibilitychange');
  assert.equal(h.timers.size, 1); h.tick(6000); h.layers[1].onload(); h.tick(0);
  assert.equal(h.layers[1].classList.contains('is-active'), true); h.dispose();
});

test('branding cancels a queued animation frame and keeps the new slides authoritative', () => {
  const h = fixture({ raf: true }); h.tick(6000); h.layers[1].onload();
  assert.equal(h.timers.size, 1);
  branding(h, { category: 'identity', app_bg_mode: 'single', app_background_image: '/ignored.webp' });
  assert.equal(h.layers[0].src, '/first.webp');
  branding(h, { category: 'all', app_bg_mode: 'slideshow', app_bg_slideshow: ['/new.webp', '/next.webp'] });
  h.tick(16);
  assert.equal(h.layers[0].src, '/new.webp');
  assert.equal(h.layers[0].classList.contains('is-active'), true);
  assert.equal(h.layers[1].classList.contains('is-active'), false);
  assert.equal(h.timers.size, 1); h.dispose();
});

test('BFCache suspends listeners and resumes once; normal navigation disposes all owned work', () => {
  const h = fixture();
  h.window.emit('pagehide', { persisted: true });
  assert.equal(h.timers.size, 0); assert.equal(h.document.count('visibilitychange'), 0);
  assert.equal(h.document.count('pttm:branding-applied'), 0);
  h.window.emit('pageshow', { persisted: true }); h.window.emit('pageshow', { persisted: true });
  assert.equal(h.timers.size, 1); assert.equal(h.document.count('visibilitychange'), 2); // background and shared motion policy
  assert.equal(h.document.count('pttm:branding-applied'), 1);
  h.window.emit('pagehide', { persisted: false });
  assert.equal(h.timers.size, 0); assert.equal(h.document.count('visibilitychange'), 0);
  assert.equal(h.document.count('pttm:branding-applied'), 0);
  // The policy module owns its own page lifecycle listener, so mount again to
  // detect a leaked controller through duplicate document subscriptions.
  const dispose = h.window.TerminalAuthBackground.mount(h.config);
  assert.equal(h.document.count('visibilitychange'), 1); dispose();
});

test('failed images retain the visible layer; successful images use the original six-second cadence', () => {
  const h = fixture(); h.tick(5999); assert.equal(h.layers[1].src, undefined);
  h.tick(1); h.layers[1].onerror();
  assert.equal(h.layers[0].classList.contains('is-active'), true);
  h.tick(6000); assert.equal(h.layers[1].src, '/third.webp');
  h.layers[1].onload(); h.tick(0);
  assert.equal(h.layers[1].classList.contains('is-active'), true);
  assert.equal(h.layers[0].classList.contains('is-active'), false);
  h.dispose(); assert.equal(h.timers.size, 0);
});

test('live reduced-motion changes cancel frames and legacy media-query listeners are cleaned up', () => {
  for (const legacy of [false, true]) {
    const h = fixture({ legacy, raf: true });
    h.tick(6000); h.layers[1].onload();
    h.preference.matches = true; h.preference.emit(legacy ? 'legacy-change' : 'change');
    assert.equal(h.timers.size, 0); h.tick(16);
    assert.equal(h.layers[0].classList.contains('is-active'), true);
    h.preference.matches = false; h.preference.emit(legacy ? 'legacy-change' : 'change');
    assert.equal(h.timers.size, 1);
    h.dispose();
    assert.equal(h.preference.count(legacy ? 'legacy-change' : 'change'), 1); // only the motion policy remains
  }
});

test('reinitialization replaces the existing controller instead of multiplying timers or listeners', () => {
  const h = fixture();
  const dispose = h.window.TerminalAuthBackground.mount(h.config);
  assert.equal(h.timers.size, 1); assert.equal(h.document.count('visibilitychange'), 2);
  assert.equal(h.document.count('pttm:branding-applied'), 1);
  h.dispose(); assert.equal(h.timers.size, 1);
  dispose(); assert.equal(h.timers.size, 0); assert.equal(h.document.count('visibilitychange'), 1); // only the shared motion policy remains
});

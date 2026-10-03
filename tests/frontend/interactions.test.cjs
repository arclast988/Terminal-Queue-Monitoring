'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const { harness, flush } = require('./harness.cjs');
const loader = 'public/assets/js/global-loader.js';
const sync = 'public/js/queue-sync.js';
function loaded(options) { const h = harness(options); h.load('public/assets/js/interaction-motion.js'); h.load(loader); return h; }

test('fetch starts immediately and preserves its arguments and response identity', async () => {
  const h = loaded(); const init = { method: 'POST', body: '{"count":3}', headers: { 'X-CSRF-TOKEN': 'fixture' } };
  const request = h.window.fetch('/passengers', init);
  assert.equal(h.requests.length, 1); assert.equal(h.requests[0].args[1], init);
  const response = { ok: true }; h.requests[0].resolve(response); assert.equal(await request, response);
  assert.equal(h.window.GlobalLoader.isVisible(), false);
});

test('overlapping requests cannot be cleared by an older completion animation', async () => {
  const h = loaded(); h.window.GlobalLoader.setDelay(0);
  const first = h.window.fetch('/first'); h.requests[0].resolve({ ok: true }); await first;
  h.tick(110);
  const second = h.window.fetch('/second'); h.tick(1000);
  assert.equal(h.window.GlobalLoader.isRunning(), true); assert.equal(h.window.GlobalLoader.isVisible(), true);
  h.requests[1].resolve({ ok: true }); await second; h.tick(300);
  assert.equal(h.window.GlobalLoader.isRunning(), false); assert.equal(h.window.GlobalLoader.isVisible(), false);
});

test('an unrelated completed request never unlocks a pending button', async () => {
  const h = loaded(); const btn = h.document.body.appendChild(new h.Element('button')); btn.textContent = 'Save';
  h.window.GlobalLoader.showButtonSpinner(btn); const request = h.window.fetch('/other');
  h.requests[0].resolve({ ok: true }); await request; h.tick(500);
  assert.equal(btn.getAttribute('aria-busy'), 'true');
  const event = h.document.emit('click', { target: btn }); assert.equal(event.defaultPrevented, true);
  h.window.GlobalLoader.hideButtonSpinner(btn); assert.equal(btn.getAttribute('aria-busy'), null);
});

test('pending feedback keeps button nodes, handlers and native values intact', () => {
  const h = loaded(); const btn = h.document.body.appendChild(new h.Element('button'));
  btn.name = 'action'; btn.value = 'save'; const child = btn.appendChild(new h.Element('span')); child.textContent = 'Save';
  btn.style.setProperty('color', '#123456', 'important');
  btn.style.setProperty('-webkit-text-fill-color', '#abcdef');
  let clicks = 0; child.addEventListener('custom', () => clicks++);
  h.window.GlobalLoader.showButtonSpinner(btn, '<Save>', true);
  assert.equal(btn.children[0], child); child.emit('custom'); assert.equal(clicks, 1);
  assert.equal(btn.value, 'save'); assert.equal(btn.disabled, undefined);
  assert.equal(btn.style.getPropertyValue('color'), 'transparent');
  assert.equal(btn.style.getPropertyValue('-webkit-text-fill-color'), 'transparent');
  assert.match(btn.querySelector('.gl-btn-feedback').textContent, /<Save>/);
  h.window.GlobalLoader.hideButtonSpinner(btn); assert.equal(btn.children[0], child); assert.equal(btn.children.length, 1);
  assert.equal(btn.style.getPropertyValue('color'), '#123456');
  assert.equal(btn.style.getPropertyPriority('color'), 'important');
  assert.equal(btn.style.getPropertyValue('-webkit-text-fill-color'), '#abcdef');
  assert.equal(btn.style.getPropertyPriority('-webkit-text-fill-color'), '');
});

test('canceled submissions stay usable; accepted native forms reject only repeats', async () => {
  const h = loaded(); const form = h.document.body.appendChild(new h.Element('form'));
  const btn = form.appendChild(new h.Element('button')); btn.textContent = 'Save'; btn.form = form;
  let cancel = true; h.document.addEventListener('submit', e => { if (cancel) e.preventDefault(); });
  h.document.emit('submit', { target: form, submitter: btn }); await flush(); assert.equal(btn.getAttribute('aria-busy'), null);
  cancel = false;
  const first = h.document.emit('submit', { target: form, submitter: btn }); await flush(); assert.equal(first.defaultPrevented, false);
  const repeated = h.document.emit('submit', { target: form, submitter: btn }); assert.equal(repeated.defaultPrevented, true);
  h.window.emit('pagehide'); assert.equal(h.timers.size, 0); assert.equal(btn.getAttribute('aria-busy'), null);
});

test('synchronous XHR failures release visual tracking and preserve the error', () => {
  const error = new Error('fixture XHR failure'); const h = loaded({ xhrError: error });
  const xhr = new h.window.XMLHttpRequest(); xhr.open('POST', '/save');
  assert.throws(() => xhr.send('original-body'), e => e === error);
  assert.equal(xhr.sendArgs[0], 'original-body'); assert.equal(h.window.GlobalLoader.isRunning(), false);
});

test('dialog and named-frame forms preserve native submission without stale locks', async () => {
  const h = loaded();
  for (const attributes of [{ method: 'dialog' }, { target: 'report-frame' }]) {
    const form = h.document.body.appendChild(new h.Element('form'));
    const btn = form.appendChild(new h.Element('button')); btn.form = form;
    for (const [key, value] of Object.entries(attributes)) form.setAttribute(key, value);
    const event = h.document.emit('submit', { target: form, submitter: btn }); await flush();
    assert.equal(event.defaultPrevented, false); assert.equal(btn.getAttribute('aria-busy'), null);
  }
});

test('show-hide-show reuses a table overlay without a stale removal timer', () => {
  const h = loaded(); const box = h.document.body.appendChild(new h.Element('div')); box.id = 'queue-list';
  h.window.GlobalLoader.showTableLoader(box, 'Updating queue', true);
  h.window.GlobalLoader.hideTableLoader(box); h.window.GlobalLoader.showTableLoader(box, 'New request', true); h.tick(250);
  assert.equal(box.querySelectorAll('.table-loader-overlay').length, 1); assert.match(box.textContent, /New request/);
  h.window.emit('pagehide'); assert.equal(h.timers.size, 0); assert.equal(box.querySelector('.table-loader-overlay'), null);
});

test('hardware and live reduced-motion preferences change presentation without timers', () => {
  const h = loaded({ cores: 2 }); assert.equal(h.window.TerminalMotion.getMode(), 'lite');
  h.window.GlobalLoader.start(true); assert.equal([...h.timers.values()].filter(t => t.interval).length, 0);
  h.preference.matches = true; h.preference.emit('change'); assert.equal(h.window.TerminalMotion.getMode(), 'reduced');
  assert.equal(h.document.documentElement.getAttribute('data-tq-motion'), 'reduced');
});

test('navigation feedback starts immediately without deferring or intercepting the link', async () => {
  const h = loaded({ cores: 2, mobile: true });
  const link = h.document.body.appendChild(new h.Element('a'));
  link.href = 'https://terminal.test/schedules'; link.setAttribute('href', '/schedules');
  const event = h.document.emit('click', { target: link, button: 0 });
  await flush();
  assert.equal(event.defaultPrevented, false);
  assert.equal(h.window.GlobalLoader.isVisible(), true);
  assert.equal([...h.timers.values()].filter(timer => timer.interval).length, 0);
  h.window.emit('pagehide'); assert.equal(h.timers.size, 0);
});

test('loading motion follows tab visibility and restores one listener after BFCache', () => {
  const h = loaded({ cores: 2 });
  h.document.hidden = true; h.document.emit('visibilitychange');
  assert.equal(h.document.documentElement.hasAttribute('data-tq-page-hidden'), true);
  h.window.emit('pagehide', { persisted: true });
  assert.equal(h.document.count('visibilitychange'), 0);
  h.document.hidden = false;
  h.window.emit('pageshow', { persisted: true }); h.window.emit('pageshow', { persisted: true });
  assert.equal(h.document.documentElement.hasAttribute('data-tq-page-hidden'), false);
  assert.equal(h.document.count('visibilitychange'), 1);
  h.window.emit('pagehide', { persisted: false });
  assert.equal(h.document.count('visibilitychange'), 0);
});

test('queue polling coalesces overlap and retains one trailing reconciliation', async () => {
  const h = harness(); h.load(sync); h.window.QueueSync.init({ apiUrl: '/api/queue-status', pollInterval: 100 });
  for (let i = 0; i < 4; i++) h.window.QueueSync.refresh();
  assert.equal(h.requests.length, 1);
  h.requests[0].resolve({ json: async () => ({ success: true, queue: [] }) }); await flush();
  assert.equal(h.requests.length, 2);
  h.requests[1].resolve({ json: async () => ({ success: true, queue: [] }) }); await flush();
  h.window.QueueSync.destroy(); assert.equal(h.timers.size, 0);
});

test('destroy/reinit removes listeners and ignores late responses from a previous lifecycle', async () => {
  const h = harness({ noAbort: true }); h.load(sync);
  h.window.QueueSync.init({ apiUrl: '/old' }); h.window.QueueSync.refresh(); h.window.QueueSync.destroy();
  assert.equal(h.document.count('visibilitychange'), 0); assert.equal(h.document.count('passenger-optimistic-update'), 0); assert.equal(h.window.count('storage'), 0);
  const span = h.document.body.appendChild(new h.Element('strong')); span.id = 'passenger-count-1'; span.className = 'passenger-count-num'; span.textContent = '2';
  h.window.QueueSync.init({ apiUrl: '/new' });
  h.requests[0].resolve({ json: async () => ({ success: true, queue: [{ id: 1, current_passengers: 99, capacity: 100 }] }) }); await flush();
  assert.equal(span.textContent, '2'); assert.equal(h.document.count('passenger-optimistic-update'), 1);
  h.window.QueueSync.destroy(); assert.equal(h.timers.size, 0);
});

test('a stale poll cannot overwrite a recent optimistic passenger update', async () => {
  const h = harness(); h.load(sync);
  const span = h.document.body.appendChild(new h.Element('strong')); span.id = 'passenger-count-1'; span.className = 'passenger-count-num'; span.textContent = '7';
  h.window.QueueSync.init({ apiUrl: '/api/queue-status' }); h.window.QueueSync.refresh();
  h.document.emit('passenger-optimistic-update', { detail: { id: 1 } });
  h.requests[0].resolve({ json: async () => ({ success: true, queue: [{ id: 1, current_passengers: 4, capacity: 10 }] }) }); await flush();
  assert.equal(span.textContent, '7'); h.window.QueueSync.destroy();
});

test('hidden queue pages stop poll wakeups and refresh immediately on return', () => {
  const h = harness(); h.load(sync); h.window.QueueSync.init({ apiUrl: '/api/queue-status', pollInterval: 100 });
  h.document.hidden = true; h.document.emit('visibilitychange'); h.tick(10000); assert.equal(h.requests.length, 0); assert.equal(h.timers.size, 0);
  h.document.hidden = false; h.document.emit('visibilitychange'); assert.equal(h.requests.length, 1);
  h.window.QueueSync.destroy(); assert.equal(h.requests[0].args[1].signal.aborted, true);
});

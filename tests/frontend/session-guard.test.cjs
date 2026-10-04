'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const { harness, flush } = require('./harness.cjs');

function loaded(hidden = false) {
  const h = harness();
  h.document.hidden = hidden;
  h.document.currentScript = {dataset:{statusUrl:'/auth/session-status',loginUrl:'/login'}};
  h.redirects = [];
  h.window.location.replace = url => h.redirects.push(url);
  h.load('public/assets/js/session-guard.js');
  return h;
}

test('session checks redirect an open page to login after password revocation', async () => {
  const h = loaded();
  assert.equal(h.requests.length, 1);
  const [url, options] = h.requests[0].args;
  assert.equal(url, '/auth/session-status');
  assert.equal(options.cache, 'no-store');
  assert.equal(options.credentials, 'same-origin');
  assert.equal(options.headers['X-Requested-With'], 'XMLHttpRequest');
  assert.equal(options.headers['X-Silent'], 'true');
  h.requests[0].resolve({status:200}); await flush();
  h.tick(10000);
  h.requests[1].resolve({status:401}); await flush();
  assert.deepEqual(h.redirects, ['/login']);
  h.tick(60000); h.window.emit('pageshow');
  assert.equal(h.requests.length, 2);
});

test('a temporary outage retries without signing out the user', async () => {
  const h = loaded();
  h.requests[0].reject(new Error('Offline')); await flush();
  h.tick(10000); h.requests[1].resolve({status:503}); await flush();
  assert.deepEqual(h.redirects, []);
  h.tick(10000); h.requests[2].resolve({status:200}); await flush();
  assert.equal(h.requests.length, 3);
  assert.deepEqual(h.redirects, []);
});

test('returning to a hidden tab checks its session immediately', async () => {
  const h = loaded(true);
  h.tick(30000);
  assert.equal(h.requests.length, 0);
  h.document.hidden = false;
  h.document.emit('visibilitychange');
  h.requests[0].resolve({status:401}); await flush();
  assert.deepEqual(h.redirects, ['/login']);
});

test('pending checks do not overlap and have a bounded timeout', async () => {
  const h = loaded();
  h.document.emit('visibilitychange'); h.window.emit('pageshow');
  assert.equal(h.requests.length, 1);
  h.tick(8000);
  assert.equal(h.requests[0].args[1].signal.aborted, true);
  h.requests[0].reject(new Error('Aborted')); await flush();
  h.tick(2000);
  assert.equal(h.requests.length, 2);
});

test('pages restored from browser history resume checks without duplicate timers', async () => {
  const h = loaded();
  h.requests[0].resolve({status:200}); await flush();
  h.window.emit('pagehide'); h.tick(30000);
  assert.equal(h.requests.length, 1);
  h.window.emit('pageshow');
  h.requests[1].resolve({status:200}); await flush();
  h.load('public/assets/js/session-guard.js');
  h.tick(10000);
  assert.equal(h.requests.length, 3);
});

'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const { harness, flush } = require('./harness.cjs');

function fixture(options = {}) {
  const h = harness(options);
  if (options.phase) {
    const fingerprint=JSON.stringify(['/first.webp','/second.webp','/third.webp']);
    h.window.sessionStorage.setItem('tq-background-timeline:'+fingerprint,JSON.stringify({fingerprint,epoch:h.window.Date.now()-options.phase}));
  }
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
  const config = { mode: options.mode || 'slideshow', image:options.image, slides: ['/first.webp', '/second.webp', '/third.webp'], defaults: ['/default.webp'] };
  const dispose = h.window.TerminalAuthBackground.mount(config);
  return { ...h, root, layers, config, dispose };
}
function branding(h, data) { h.document.emit('pttm:branding-applied', { detail: data }); }

test('plain mode starts without images or slideshow work and stays disabled across lifecycle events', () => {
  const h = fixture({ mode: 'none' });
  assert.equal(h.root.style.display, 'none');
  for (const layer of h.layers) assert.equal(layer.getAttribute('src'), null);
  assert.equal(h.timers.size, 0);
  h.document.emit('visibilitychange');
  h.window.emit('pagehide', { persisted: true });
  h.window.emit('pageshow', { persisted: true });
  branding(h, { category: 'identity', app_bg_mode: 'single', app_background_image: '/ignored.webp' });
  h.tick(60000);
  assert.equal(h.root.style.display, 'none');
  assert.equal(h.timers.size, 0);
  h.dispose();
});

test('plain mode cancels in-flight photos and supports switching back to single and slideshow modes', () => {
  const h = fixture();
  h.tick(6000);
  const staleLoad = h.layers[1].onload;
  branding(h, { category: 'background', app_bg_mode: 'none' });
  staleLoad();
  h.tick(60000);
  assert.equal(h.root.style.display, 'none');
  for (const layer of h.layers) assert.equal(layer.getAttribute('src'), null);
  assert.equal(h.timers.size, 0);
  branding(h, { category: 'background', app_bg_mode: 'single', app_background_image: '/custom.webp' });
  assert.equal(h.root.style.display, '');
  assert.equal(h.layers[0].src, '/custom.webp');
  h.layers[0].emit('load');
  assert.equal(h.root.getAttribute('data-bg-ready'),'','The poster is retired after photos are restored from plain mode.');
  assert.equal(h.root.style.opacity, '0.28');
  assert.equal(h.timers.size, 0);
  branding(h, { category: 'all', app_bg_mode: 'none' });
  branding(h, { category: 'all', app_bg_mode: 'slideshow', app_bg_slideshow: ['/new.webp', '/next.webp'] });
  assert.equal(h.root.style.display, '');
  assert.equal(h.layers[0].src, '/new.webp');
  assert.equal(h.root.style.opacity, '0.22');
  assert.equal(h.timers.size, 1);
  h.tick(6000);
  assert.equal(h.layers[1].src, '/next.webp');
  h.dispose();
});

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
  assert.equal(h.layers[1].src, '/third.webp'); // Rejoin the shared clock after the hidden minute.
  h.layers[1].onload(); h.tick(0);
  assert.equal(h.timers.size, 1);
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
  assert.equal(h.document.count('pttm:branding-applied'), 2); // background plus shared phase policy
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
    h.layers[1].onload(); h.tick(16);
    assert.equal(h.timers.size, 1);
    h.dispose();
    assert.equal(h.preference.count(legacy ? 'legacy-change' : 'change'), 1); // only the motion policy remains
  }
});

test('reinitialization replaces the existing controller instead of multiplying timers or listeners', () => {
  const h = fixture();
  const dispose = h.window.TerminalAuthBackground.mount(h.config);
  assert.equal(h.timers.size, 1); assert.equal(h.document.count('visibilitychange'), 2);
  assert.equal(h.document.count('pttm:branding-applied'), 2);
  h.dispose(); assert.equal(h.timers.size, 1);
  dispose(); assert.equal(h.timers.size, 0); assert.equal(h.document.count('visibilitychange'), 1); // only the shared motion policy remains
});

test('mode-only live changes retain the single hero image independently from slideshow pictures',()=>{
  const h=fixture({image:'/single-hero.webp'});
  branding(h,{category:'background',app_bg_mode:'single'});
  assert.equal(h.layers[0].src,'/single-hero.webp');
  branding(h,{category:'background',app_bg_mode:'slideshow',app_bg_slideshow:['/updated-slide.webp','/next.webp']});
  branding(h,{category:'background',app_bg_mode:'single'});
  assert.equal(h.layers[0].src,'/single-hero.webp');
  assert.equal(h.timers.size,0);h.dispose();
});

test('auth navigation starts at the shared picture and advances at the remaining six-second boundary',()=>{
  const h=fixture({phase:7500});
  assert.equal(h.layers[0].src,'/second.webp');
  assert.equal([...h.timers.values()][0].due-h.window.Date.now(),4500);
  branding(h,{category:'background',app_bg_mode:'slideshow',app_bg_slideshow:h.config.slides});
  assert.equal(h.layers[0].src,'/second.webp','An unchanged branding snapshot must retain the phase.');
  h.tick(4499);assert.equal(h.layers[1].src,undefined);
  h.tick(1);assert.equal(h.layers[1].src,'/third.webp');h.layers[1].onload();h.tick(0);
  assert.equal(h.layers[1].classList.contains('is-active'),true);
  branding(h,{category:'background',app_bg_mode:'slideshow',app_bg_slideshow:['/replaced.webp','/other.webp']});
  assert.equal(h.layers[0].src,'/replaced.webp');
  assert.equal([...h.timers.values()][0].due-h.window.Date.now(),6000);
  h.dispose();
});

test('slideshow epochs are shared between tabs and isolated by their picture list',()=>{
  const storage=new Map(),shared={getItem:key=>storage.get(key)||null,setItem:(key,value)=>storage.set(key,value)};
  const first=harness();first.window.localStorage=shared;first.load('public/assets/js/interaction-motion.js');
  first.window.TerminalMotion.syncBackground('single',['/a.webp','/b.webp']);assert.equal(storage.size,0);
  first.window.TerminalMotion.syncBackground('slideshow',['/a.webp','/b.webp']);
  const second=harness();second.tick(7500);second.window.localStorage=shared;second.load('public/assets/js/interaction-motion.js');
  assert.equal(second.window.TerminalMotion.getBackgroundPhase(['/a.webp','/b.webp']).index,1);
  second.window.TerminalMotion.getBackgroundPhase(['/new.webp','/other.webp']);
  assert.equal(second.window.TerminalMotion.getBackgroundPhase(['/a.webp','/b.webp']).index,1,'Another list must not reset an existing slideshow.');
  first.window.emit('pagehide');second.window.emit('pagehide');
});

test('paused normal-page backgrounds rejoin elapsed time after gestures and modals without a recurring timer',()=>{
  const h=harness();h.load('public/assets/js/interaction-motion.js');
  const animation={animationName:'terminalBgSlideshow',currentTime:0,effect:{getTiming:()=>({delay:0})}};
  h.document.getAnimations=()=>[animation];
  h.window.TerminalMotion.syncBackground('slideshow',['/first.webp','/second.webp']);
  h.tick(7500);h.document.emit('hidden.bs.modal');assert.equal(animation.currentTime,7500);
  h.window.emit('scroll');h.tick(250);assert.equal(animation.currentTime,7750);
  assert.equal(h.timers.size,0);
  h.document.hidden=true;h.document.emit('visibilitychange');h.tick(6000);
  h.document.hidden=false;h.document.emit('visibilitychange');assert.equal(animation.currentTime,13750);
  h.window.emit('pagehide');
});

function syncFixture(){
  const h=harness(),applied=[];
  const data={category:'background',app_bg_mode:'single',app_background_image:'/hero.webp',app_bg_slideshow:['/slide.webp']};
  const node=h.document.body.appendChild(new h.Element('script'));node.id='backgroundSyncConfig';
  node.textContent=JSON.stringify({url:'/api/system-branding',background:data});
  let connected=false,initializations=0;
  h.window.QueueWS={init(){initializations++;},isConnected(){return connected;}};
  h.window.applyLiveBranding=update=>{applied.push(update);branding(h,update);};
  h.load('public/assets/js/background-sync.js');
  return {...h,data,applied,setConnected(value){connected=value;},initializations:()=>initializations};
}
function respond(request,data){request.resolve({ok:true,json:async()=>({success:true,data})});}

test('background fallback polls without overlapping or restarting unchanged artwork and pauses when hidden',async()=>{
  const h=syncFixture();assert.equal(h.initializations(),1);assert.equal(h.requests.length,1);
  respond(h.requests[0],h.data);await flush();assert.equal(h.applied.length,0);
  h.tick(20000);assert.equal(h.requests.length,2);
  h.tick(40000);assert.equal(h.requests.length,2,'A pending read must not create overlapping polls.');
  respond(h.requests[1],{...h.data,app_background_image:'/new.webp'});await flush();assert.equal(h.applied.length,1);
  h.setConnected(true);h.tick(20000);assert.equal(h.requests.length,2,'The socket replaces regular polling.');
  h.document.emit('pttm:ws-connected');assert.equal(h.requests.length,3,'Reconnect catches settings changed during a socket outage.');
  respond(h.requests[2],{...h.data,app_background_image:'/new.webp'});await flush();
  h.document.hidden=true;h.document.emit('visibilitychange');h.tick(60000);assert.equal(h.timers.size,0);
  h.document.hidden=false;h.document.emit('visibilitychange');assert.equal(h.requests.length,4);
  respond(h.requests[3],{...h.data,app_background_image:'/new.webp'});await flush();assert.equal(h.applied.length,1);
  h.window.emit('pagehide',{persisted:false});assert.equal(h.timers.size,0);
});

test('background fallback ignores stale reads after live saves and resumes once after BFCache',async()=>{
  const h=syncFixture();
  branding(h,{...h.data,app_background_image:'/live-save.webp'});
  respond(h.requests[0],h.data);await flush();assert.equal(h.applied.length,0);
  h.window.TerminalBackgroundSync.refresh();assert.equal(h.requests.length,2);
  h.window.emit('pagehide',{persisted:true});
  assert.equal(h.requests[1].args[1].signal.aborted,true);
  respond(h.requests[1],h.data);await flush();assert.equal(h.applied.length,0);
  h.window.emit('pageshow',{persisted:true});h.window.emit('pageshow',{persisted:true});
  assert.equal(h.requests.length,3);assert.equal(h.timers.size,1);
  h.load('public/assets/js/background-sync.js');assert.equal(h.requests.length,3);assert.equal(h.initializations(),1);
  respond(h.requests[2],{...h.data,app_background_image:'/live-save.webp'});await flush();assert.equal(h.applied.length,0);
  h.window.emit('pagehide',{persisted:false});assert.equal(h.timers.size,0);
  assert.equal(h.document.count('pttm:branding-applied'),0);assert.equal(h.document.count('visibilitychange'),0);
  assert.equal(h.document.count('pttm:ws-connected'),0);
});

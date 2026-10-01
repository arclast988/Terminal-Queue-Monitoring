'use strict';
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '../..');
class Events {
  constructor() { this.listeners = new Map(); }
  addEventListener(name, fn, options) {
    const set = this.listeners.get(name) || new Set();
    set.add({ fn, capture: options === true, once: !!options?.once }); this.listeners.set(name, set);
  }
  removeEventListener(name, fn) { for (const entry of this.listeners.get(name) || []) if (entry.fn === fn) this.listeners.get(name).delete(entry); }
  emit(name, properties = {}) {
    const event = { type: name, defaultPrevented: false, preventDefault() { this.defaultPrevented = true; }, stopImmediatePropagation() { this.stopped = true; }, ...properties };
    const entries = [...(this.listeners.get(name) || [])].sort((a, b) => Number(b.capture) - Number(a.capture));
    for (const entry of entries) { entry.fn(event); if (entry.once) this.listeners.get(name).delete(entry); if (event.stopped) break; }
    return event;
  }
  dispatchEvent(event) { this.emit(event.type, event); return true; }
  count(name) { return this.listeners.get(name)?.size || 0; }
}
class Style {
  constructor() { this.position = ''; this.priorities = new Map(); }
  getPropertyValue(name) { return this[name] || ''; }
  getPropertyPriority(name) { return this.priorities.get(name) || ''; }
  setProperty(name, value, priority = '') { this[name] = String(value); this.priorities.set(name, priority); }
  removeProperty(name) { const value = this.getPropertyValue(name); delete this[name]; this.priorities.delete(name); return value; }
}
class Element extends Events {
  constructor(tag = 'div') {
    super(); this.tagName = tag.toUpperCase(); this.attributes = new Map(); this.children = []; this.style = new Style(); this.parentNode = null; this._text = ''; this.form = null;
    this.classList = { contains: n => this.classes.has(n), add: (...ns) => ns.forEach(n => this.classes.add(n)), remove: (...ns) => ns.forEach(n => this.classes.delete(n)), toggle: (n, force) => force ? this.classes.add(n) : this.classes.delete(n) };
    this.classes = new Set();
  }
  set className(value) { this.classes = new Set(value.split(/\s+/).filter(Boolean)); }
  get className() { return [...this.classes].join(' '); }
  set id(value) { this.attributes.set('id', value); }
  get id() { return this.attributes.get('id') || ''; }
  setAttribute(n, v) { this.attributes.set(n, String(v)); }
  getAttribute(n) { return this.attributes.get(n) ?? null; }
  hasAttribute(n) { return this.attributes.has(n); }
  removeAttribute(n) { this.attributes.delete(n); }
  appendChild(el) { el.parentNode = this; this.children.push(el); return el; }
  remove() { if (this.parentNode) this.parentNode.children = this.parentNode.children.filter(el => el !== this); this.parentNode = null; }
  contains(el) { return this === el || this.children.some(c => c.contains(el)); }
  get isConnected() { return this.connected || !!this.parentNode?.isConnected; }
  get parentElement() { return this.parentNode; }
  get firstChild() { return this.children[0] || null; }
  set textContent(v) { this._text = String(v); this.children = []; }
  get textContent() { return this._text + this.children.map(c => c.textContent).join(''); }
  get innerText() { return this.textContent; }
  matches(selector) { return selector.startsWith('.') ? this.classList.contains(selector.slice(1)) : selector.startsWith('#') ? this.id === selector.slice(1) : this.tagName.toLowerCase() === selector; }
  querySelectorAll(selector) { return this.children.flatMap(c => [...(selector.split(',').some(s => c.matches(s.trim())) ? [c] : []), ...c.querySelectorAll(selector)]); }
  querySelector(selector) { return this.querySelectorAll(selector)[0] || null; }
  closest(selector) { return this.matches(selector) ? this : this.parentNode?.closest(selector); }
}
function harness(options = {}) {
  let now = 10000, id = 0;
  const timers = new Map(), requests = [];
  const document = new Events();
  Object.assign(document, { nodeType: 0, readyState: 'complete', hidden: false, documentElement: new Element('html'), createElement: tag => new Element(tag), createTextNode: text => { const el = new Element('#text'); el.textContent = text; return el; } });
  document.documentElement.connected = true;
  document.head = document.documentElement.appendChild(new Element('head')); document.body = document.documentElement.appendChild(new Element('body'));
  document.getElementById = id => document.documentElement.querySelector('#' + id);
  document.querySelector = selector => document.documentElement.querySelector(selector);
  document.querySelectorAll = selector => document.documentElement.querySelectorAll(selector);
  const preference = new Events(); preference.matches = !!options.reduced;
  class ClockDate extends Date { static now() { return now; } }
  class XHR extends Events { open(...args) { this.openArgs = args; } setRequestHeader(...args) { this.headerArgs = args; } send(...args) { this.sendArgs = args; if (options.xhrError) throw options.xhrError; } }
  const window = new Events();
  const storage = new Map();
  Object.assign(window, {
    document, navigator: { hardwareConcurrency: options.cores ?? 8, deviceMemory: options.memory ?? 8 }, Date: ClockDate,
    Promise, URL, console, XMLHttpRequest: XHR, AbortController: options.noAbort ? undefined : AbortController,
    location: { href: 'https://terminal.test/queue', origin: 'https://terminal.test' },
    sessionStorage: { getItem: k => storage.get(k) ?? null, setItem: (k, v) => storage.set(k, v), removeItem: k => storage.delete(k) },
    getComputedStyle: el => ({ position: el.style.position || 'static', color: '#ffffff' }),
    matchMedia: query => query.includes('prefers-reduced-motion') ? preference : { matches: query.includes('display-mode') ? !!options.standalone : !!options.mobile },
    setTimeout: (fn, delay = 0) => { const timerId = ++id; timers.set(timerId, { fn, due: now + delay }); return timerId; },
    clearTimeout: timerId => timers.delete(timerId),
    setInterval: (fn, delay) => { const timerId = ++id; timers.set(timerId, { fn, due: now + delay, interval: delay }); return timerId; },
    clearInterval: timerId => timers.delete(timerId),
    CustomEvent: class { constructor(type, init) { this.type = type; this.detail = init.detail; } },
    fetch: (...args) => new Promise((resolve, reject) => requests.push({ args, resolve, reject }))
  });
  window.window = window;
  const context = vm.createContext(window);
  function load(file) { vm.runInContext(fs.readFileSync(path.join(root, file), 'utf8'), context, { filename: file }); }
  function tick(ms) {
    const end = now + ms; let steps = 0;
    while (true) {
      const next = [...timers].filter(([, t]) => t.due <= end).sort((a, b) => a[1].due - b[1].due)[0];
      if (!next) break; if (++steps > 5000) throw new Error('Timer did not settle');
      const [timerId, timer] = next; now = timer.due;
      if (timer.interval) timer.due += timer.interval; else timers.delete(timerId);
      timer.fn();
    }
    now = end;
  }
  return { window, document, timers, requests, load, tick, Element, preference };
}
async function flush() { for (let i = 0; i < 10; i++) await Promise.resolve(); }
module.exports = { harness, flush, root };

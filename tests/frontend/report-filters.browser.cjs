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
const fixtures = new Map();
const reports = [
  { page: 'admin-history', path: '/admin/history', form: '#reportForm', modal: '#reportFilterModal', button: '#btn-pdf' },
  { page: 'admin-logs', path: '/admin/logs', form: '#logReportForm', modal: '#logReportFilterModal', button: '#log-btn-pdf' },
];
let browser, server, origin;

function fixture(name) {
  if (!fixtures.has(name)) {
    const html = execFileSync(phpBinary(), [path.join(__dirname, 'render-responsive-fixture.php'), name, 'normal', 'theme'], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
    // Keep real filter/report handlers and Bootstrap, isolating unrelated polling.
    fixtures.set(name, html.replace(/<script\b([^>]*)>([\s\S]*?)<\/script>/g, (tag, attrs, code) => {
      if (/\bsrc\s*=/.test(attrs)) return /bootstrap\.bundle\.min\.js/.test(attrs) ? tag : '';
      return /function syncPresetButtons|function syncDateHints/.test(code) ? tag : '';
    }));
  }
  return fixtures.get(name);
}

test.before(async () => {
  for (const report of reports) fixture(report.page);
  server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://fixture');
    const report = reports.find(item => url.pathname === '/fixture/' + item.page || url.pathname === item.path);
    if (report) { res.setHeader('Content-Type', 'text/html'); return res.end(fixture(report.page)); }
    if (url.pathname === '/fixture.svg') { res.setHeader('Content-Type', 'image/svg+xml'); return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"/>'); }
    const file = path.resolve(root, 'public', '.' + url.pathname);
    if (!file.startsWith(path.join(root, 'public') + path.sep) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.statusCode = 404; return res.end(); }
    res.setHeader('Content-Type', assetContentType(file)); res.end(fs.readFileSync(file));
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  origin = 'http://127.0.0.1:' + server.address().port;
  browser = await playwright[engine].launch({ headless: true });
});
test.after(async () => {
  await browser?.close();
  if (server) { server.closeAllConnections(); await new Promise(resolve => server.close(resolve)); }
});

async function reportDates(page, report) {
  return page.locator(report.form).evaluate(form => {
    const data = new FormData(form);
    return { from: data.get('from_date'), to: data.get('to_date') };
  });
}

test('report presets use local calendar dates across year boundaries and preserve ISO form values', { timeout: 90000 }, async () => {
  const cases = [
    { zone: 'Asia/Manila', instant: '2026-10-06T00:30:00+08:00', today: '2026-10-06', week: '2026-09-29', month: '2026-10-01', year: '2026-01-01' },
    { zone: 'Asia/Manila', instant: '2026-01-01T00:30:00+08:00', today: '2026-01-01', week: '2025-12-25', month: '2026-01-01', year: '2026-01-01' },
    { zone: 'America/Los_Angeles', instant: '2026-12-31T23:30:00-08:00', today: '2026-12-31', week: '2026-12-24', month: '2026-12-01', year: '2026-01-01' },
  ];
  for (const sample of cases) {
    for (const report of reports) {
      const context = await browser.newContext({ timezoneId: sample.zone, viewport: { width: 1280, height: 900 } });
      try {
        const page = await context.newPage(), errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.clock.install({ time: new Date(sample.instant) });
        await page.clock.setFixedTime(new Date(sample.instant));
        await page.goto(origin + '/fixture/' + report.page);
        await page.locator(`[data-bs-target="${report.modal}"]`).click();
        await page.locator(report.modal + '.show').waitFor({ state: 'visible' });
        for (const range of ['today', 'week', 'month', 'year']) {
          const preset = page.locator(`${report.modal} [data-range="${range}"]`);
          await preset.click();
          assert.deepEqual(await reportDates(page, report), { from: sample[range], to: sample.today }, `${report.page} ${sample.zone} ${range}`);
          assert.match(await preset.getAttribute('class'), /\bbtn-secondary\b/);
          assert.equal(await page.locator(report.button).isDisabled(), false);
        }
        await page.locator(`${report.form} [name="from_date"]`).fill('2024-02-29');
        assert.deepEqual(await reportDates(page, report), { from: '2024-02-29', to: sample.today });
        assert.doesNotMatch(await page.locator(`${report.modal} [data-range="year"]`).getAttribute('class'), /\bbtn-secondary\b/);
        await page.locator(`${report.modal} [data-range="all"]`).click();
        assert.deepEqual(await reportDates(page, report), { from: '', to: '' });
        assert.equal(await page.locator(report.button).isDisabled(), true);
        assert.deepEqual(errors, [], report.page);
      } finally { await context.close(); }
    }
  }
});

test('date filter hints include the month and follow manual input, quick filters, and browser history', { timeout: 90000 }, async () => {
  for (const report of reports) {
    const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
    try {
      const page = await context.newPage(), errors = [];
      page.on('pageerror', error => errors.push(error.message));
      await page.goto(origin + '/fixture/' + report.page);
      for (const field of ['fromDate', 'toDate']) {
        const input = page.locator('#' + field), hint = page.locator('#' + field + 'Hint');
        assert.equal(await hint.textContent(), 'dd/mm/yyyy');
        assert.equal(await hint.isVisible(), true);
        await input.fill('2026-02-03');
        await input.evaluate(el => el.blur());
        assert.equal(await hint.isVisible(), false);
        assert.equal(await input.inputValue(), '2026-02-03');
        await input.fill('');
        await input.evaluate(el => el.blur());
        assert.equal(await hint.isVisible(), true);
      }
      const chips = page.locator('.quick-chip-btn');
      assert.equal(await chips.first().textContent(), 'All');
      const expected = await chips.nth(1).getAttribute('data-from');
      await chips.nth(1).click();
      await page.waitForFunction(() => document.querySelector('#fromDate').value !== '' && document.querySelector('.table-modern').closest('.modern-card').style.opacity !== '0.5');
      assert.equal(await page.locator('#fromDate').inputValue(), expected);
      assert.equal(await page.locator('#fromDateHint').isVisible(), false);
      await chips.first().click();
      await page.waitForFunction(() => document.querySelector('#fromDate').value === '' && location.search === '' && document.querySelector('.table-modern').closest('.modern-card').style.opacity === '1');
      assert.equal(await page.locator('#fromDateHint').isVisible(), true);
      assert.equal(await page.locator('#toDateHint').isVisible(), true);
      await page.goBack();
      await page.waitForFunction(() => document.querySelector('#fromDate').value !== '');
      assert.equal(await page.locator('#fromDate').inputValue(), expected);
      assert.equal(await page.locator('#fromDateHint').isVisible(), false);
      assert.deepEqual(errors, [], report.page);
    } finally { await context.close(); }
  }
});

test('all five date presets fit on phone and desktop report dialogs', { timeout: 60000 }, async () => {
  for (const report of reports) {
    for (const width of [320, 1280]) {
      const page = await browser.newPage({ viewport: { width, height: 900 } });
      try {
        await page.goto(origin + '/fixture/' + report.page);
        await page.locator(`[data-bs-target="${report.modal}"]`).click();
        await page.locator(report.modal + '.show').waitFor({ state: 'visible' });
        const geometry = await page.locator(report.modal + ' .report-filter-presets').evaluate(row => {
          const outer = row.getBoundingClientRect();
          return [...row.querySelectorAll('button')].map(button => {
            const box = button.getBoundingClientRect();
            return box.left >= outer.left - 1 && box.right <= outer.right + 1 && box.left >= 0 && box.right <= innerWidth && button.scrollWidth <= button.clientWidth + 1;
          });
        });
        assert.equal(geometry.length, 5);
        assert.ok(geometry.every(Boolean), `${report.page} ${width}: ${JSON.stringify(geometry)}`);
      } finally { await page.close(); }
    }
  }
});

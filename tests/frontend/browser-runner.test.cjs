'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const { mkdtempSync, mkdirSync, writeFileSync, readFileSync, rmSync } = require('node:fs');
const { tmpdir } = require('node:os');
const { join } = require('node:path');
const { execFileSync } = require('node:child_process');

test('browser runner repairs Ubuntu mirrors while retaining package signatures and other repositories', () => {
  const root = mkdtempSync(join(tmpdir(), 'browser-runner-'));
  try {
    const sources = join(root, 'etc/apt/sources.list.d');
    mkdirSync(sources, { recursive: true });
    const legacy = join(root, 'etc/apt/sources.list');
    const deb822 = join(sources, 'ubuntu.sources');
    const microsoft = join(sources, 'microsoft-prod.list');
    writeFileSync(legacy, 'deb [signed-by=/usr/share/keyrings/ubuntu-archive-keyring.gpg] http://us-east-2.ec2.archive.ubuntu.com/ubuntu/ noble main universe\n');
    const signing = 'Components: main universe\nSigned-By: /usr/share/keyrings/ubuntu-archive-keyring.gpg\n';
    writeFileSync(deb822, 'Types: deb\nURIs: http://azure.archive.ubuntu.com/ubuntu\nSuites: noble noble-updates\n' + signing + '\nTypes: deb\nURIs: mirror+file:/etc/apt/apt-mirrors.txt\nSuites: noble-security\n' + signing);
    writeFileSync(microsoft, 'deb https://packages.microsoft.com/ubuntu/24.04/prod noble main\n');
    const run = () => execFileSync(process.platform === 'win32' ? 'python' : 'python3', [join(__dirname, 'configure-browser-runner.py'), '--root', root]);
    run();
    assert.equal(readFileSync(legacy, 'utf8'), 'deb [signed-by=/usr/share/keyrings/ubuntu-archive-keyring.gpg] https://archive.ubuntu.com/ubuntu/ noble main universe\n');
    const expected = 'Types: deb\nURIs: https://archive.ubuntu.com/ubuntu\nSuites: noble noble-updates\n' + signing + '\nTypes: deb\nURIs: https://archive.ubuntu.com/ubuntu\nSuites: noble-security\n' + signing;
    assert.equal(readFileSync(deb822, 'utf8'), expected);
    assert.equal(readFileSync(microsoft, 'utf8'), 'deb https://packages.microsoft.com/ubuntu/24.04/prod noble main\n');
    const config = join(root, 'etc/apt/apt.conf.d/zzzz-browser-test-downloads');
    assert.equal(readFileSync(config, 'utf8'), 'Acquire::Retries "1";\nAcquire::http::Timeout "15";\nAcquire::https::Timeout "15";\n');
    run();
    assert.equal(readFileSync(deb822, 'utf8'), expected);
  } finally {
    rmSync(root, { recursive: true, force: true });
  }
});

import assert from 'node:assert/strict';
import { mkdtemp, mkdir, rm, writeFile } from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import test from 'node:test';
import { validateViteBuild } from '../../scripts/verify-vite-build.mjs';

async function withBuild(manifest, assets, callback) {
  const directory = await mkdtemp(path.join(os.tmpdir(), 'mfa-vite-build-'));

  try {
    await mkdir(path.join(directory, 'assets'), { recursive: true });
    const manifestContent = typeof manifest === 'string' ? manifest : JSON.stringify(manifest);
    await writeFile(path.join(directory, 'manifest.json'), manifestContent);

    for (const [relativePath, content] of Object.entries(assets)) {
      const targetPath = path.join(directory, relativePath);
      await mkdir(path.dirname(targetPath), { recursive: true });
      await writeFile(targetPath, content);
    }

    await callback(directory);
  } finally {
    await rm(directory, { recursive: true, force: true });
  }
}

test('accepts a complete Vite build', async () => {
  await withBuild({
    'resources/css/app.css': {
      file: 'assets/app-123.css',
      css: ['assets/chunk-456.css'],
      assets: ['assets/font-789.woff2'],
    },
  }, {
    'assets/app-123.css': 'body{}',
    'assets/chunk-456.css': '.card{}',
    'assets/font-789.woff2': 'font',
  }, async (directory) => {
    const result = await validateViteBuild(directory);
    assert.equal(result.assetCount, 3);
  });
});

test('rejects a missing declared asset', async () => {
  await withBuild({ entry: { file: 'assets/missing.js' } }, {}, async (directory) => {
    await assert.rejects(validateViteBuild(directory), /Missing Vite build asset: assets\/missing\.js/);
  });
});

test('rejects invalid manifest JSON', async () => {
  await withBuild('{not-json', {}, async (directory) => {
    await assert.rejects(validateViteBuild(directory), /Invalid Vite manifest JSON/);
  });
});

test('rejects asset paths outside public build', async () => {
  await withBuild({ entry: { file: '../secret.txt' } }, {}, async (directory) => {
    await assert.rejects(validateViteBuild(directory), /Asset path escapes public\/build/);
  });
});

test('rejects Windows absolute paths on every platform', async () => {
  await withBuild({ entry: { file: 'C:\\secret.txt' } }, {}, async (directory) => {
    await assert.rejects(validateViteBuild(directory), /Unsafe absolute asset path/);
  });
});

import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawnSync } from 'node:child_process';

if (!fs.existsSync('.clasp.json')) {
  console.error('Falta .clasp.json. Ejecutá primero: npm run apps-script:link -- <SCRIPT_ID>');
  process.exit(1);
}

const codePath = path.resolve('automation/apps-script/Code.gs');
if (!fs.existsSync(codePath)) {
  console.error('No existe automation/apps-script/Code.gs');
  process.exit(1);
}

const backup = path.join(os.tmpdir(), 'mfa-Code.gs.backup');
fs.copyFileSync(codePath, backup);

const isWindows = process.platform === 'win32';
const command = isWindows ? 'npx.cmd' : 'npx';
const args = ['--yes', '@google/clasp', 'pull'];

const result = spawnSync(command, args, {
  stdio: 'inherit',
  shell: isWindows,
});

fs.copyFileSync(backup, codePath);
fs.rmSync(backup, { force: true });

if (result.error) {
  console.error(`No se pudo ejecutar clasp: ${result.error.message}`);
}

if (result.status !== 0) {
  console.error(`clasp pull falló (exit=${result.status ?? 'sin código'}). Code.gs fue restaurado; no se perdió el trabajo local.`);
  process.exit(result.status ?? 1);
}

console.log('Bootstrap completo: manifiesto/archivos remotos importados y Code.gs preservado.');
console.log('Revisá git status antes de hacer cualquier push.');

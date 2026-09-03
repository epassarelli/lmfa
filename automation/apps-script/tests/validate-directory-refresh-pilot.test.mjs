import assert from 'node:assert/strict';
import { mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const tool = fileURLToPath(new URL('../tools/validate-refresh-pilot.mjs', import.meta.url));
const directory = mkdtempSync(join(tmpdir(), 'mfa-directory-refresh-'));

function body(subject) {
  return `<p>${Array.from({ length: 305 }, (_, index) => `${subject}-${index + 1}`).join(' ')}</p>`;
}

function csvCell(value) {
  const text = String(value ?? '');
  return /[",\r\n]/.test(text) ? `"${text.replaceAll('"', '""')}"` : text;
}

function writeCsv(name, rows) {
  const headers = [
    'ID_CONTENIDO', 'TIPO', 'ENVIAR_API', 'ACCION_API', 'ID_WEB', 'TITULO', 'SLUG',
    'CUERPO', 'PROVINCE_ID', 'VENUE_TYPE', 'SOURCE_URLS', 'EDITORIAL_FOCUS',
    'TRANSMISSION_MODES', 'COVERAGE_SCOPE', 'IS_FOLKLORE', 'RADIO_SIGNAL_ID',
    'META_TITLE',
  ];
  const path = join(directory, name);
  writeFileSync(path, [
    headers.join(','),
    ...rows.map((row) => headers.map((header) => csvCell(row[header])).join(',')),
  ].join('\n'));
  return path;
}

function run(path) {
  return spawnSync(process.execPath, [tool, path, '--scope=directories'], { encoding: 'utf8' });
}

const validRows = [
  { ID_CONTENIDO: 'PEN-C', TIPO: 'Peña', ENVIAR_API: 'S', ACCION_API: 'CREAR', TITULO: 'Peña piloto', SLUG: 'penia-piloto', CUERPO: body('penia'), PROVINCE_ID: 7, VENUE_TYPE: 'penia', SOURCE_URLS: 'https://example.test/penia' },
  { ID_CONTENIDO: 'PEN-U', TIPO: 'Peña', ENVIAR_API: 'S', ACCION_API: 'ACTUALIZAR', ID_WEB: 11, META_TITLE: 'Peña actualizada' },
  { ID_CONTENIDO: 'RAD-C', TIPO: 'Radio', ENVIAR_API: 'S', ACCION_API: 'CREAR', TITULO: 'Radio piloto', SLUG: 'radio-piloto', CUERPO: body('radio'), EDITORIAL_FOCUS: 'folklore', TRANSMISSION_MODES: 'streaming', COVERAGE_SCOPE: 'national', SOURCE_URLS: 'https://example.test/radio' },
  { ID_CONTENIDO: 'RAD-U', TIPO: 'Radio', ENVIAR_API: 'S', ACCION_API: 'ACTUALIZAR', ID_WEB: 22, META_TITLE: 'Radio actualizada' },
  { ID_CONTENIDO: 'PRG-C', TIPO: 'ProgramaRadio', ENVIAR_API: 'S', ACCION_API: 'CREAR', TITULO: 'Programa piloto', SLUG: 'programa-piloto', CUERPO: body('programa'), IS_FOLKLORE: 'S', RADIO_SIGNAL_ID: 22, SOURCE_URLS: 'https://example.test/programa' },
  { ID_CONTENIDO: 'PRG-U', TIPO: 'ProgramaRadio', ENVIAR_API: 'S', ACCION_API: 'ACTUALIZAR', ID_WEB: 33, META_TITLE: 'Programa actualizado' },
];

try {
  const valid = run(writeCsv('valid.csv', validRows));
  assert.equal(valid.status, 0, valid.stderr);
  assert.match(valid.stdout, /PREFLIGHT OK \(directories\)/);
  assert.match(valid.stdout, /Peña CREAR -> POST \/penia-profiles/);
  assert.match(valid.stdout, /ProgramaRadio ACTUALIZAR -> PUT \/radio-programs\/33/);

  const missingUpdate = validRows.map((row) => ({ ...row }));
  missingUpdate[3].META_TITLE = '';
  const invalid = run(writeCsv('invalid.csv', missingUpdate));
  assert.equal(invalid.status, 1);
  assert.match(invalid.stderr, /no contiene campos para modificar/);

  const extra = run(writeCsv('extra.csv', [
    ...validRows,
    { ID_CONTENIDO: 'MIT-EXTRA', TIPO: 'Mito', ENVIAR_API: 'S', ACCION_API: 'CREAR' },
  ]));
  assert.equal(extra.status, 1);
  assert.match(extra.stderr, /ENVIAR_API=S no permitido para TIPO=Mito/);
  assert.match(extra.stderr, /exactamente 6 filas con ENVIAR_API=S; encontradas=7/);
} finally {
  rmSync(directory, { recursive: true, force: true });
}

console.log('Directory refresh pilot preflight tests passed.');

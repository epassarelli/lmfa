import assert from 'node:assert/strict';
import { mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const tool = fileURLToPath(new URL('../tools/validate-refresh-pilot.mjs', import.meta.url));
const directory = mkdtempSync(join(tmpdir(), 'mfa-refresh-pilot-'));

function body(subject) {
  return `<p>${Array.from({ length: 305 }, (_, index) => `${subject}-${index + 1}`).join(' ')}</p>`;
}

function csvCell(value) {
  const text = String(value ?? '');
  return /[",\r\n]/.test(text) ? `"${text.replaceAll('"', '""')}"` : text;
}

function writeCsv(name, rows) {
  const headers = [
    'ID_CONTENIDO', 'TIPO', 'ENVIAR_API', 'ACCION_API', 'ID_WEB',
    'ARTISTA', 'TITULO', 'SLUG', 'CUERPO', 'REGION',
  ];
  const path = join(directory, name);
  writeFileSync(path, [
    headers.join(','),
    ...rows.map((row) => headers.map((header) => csvCell(row[header])).join(',')),
  ].join('\n'));
  return path;
}

function run(path) {
  return spawnSync(process.execPath, [tool, path], { encoding: 'utf8' });
}

const validRows = [
  { ID_CONTENIDO: 'ART-C', TIPO: 'Artista', ENVIAR_API: 'S', ACCION_API: 'CREAR', ARTISTA: 'Artista piloto', SLUG: 'artista-piloto', CUERPO: body('artista') },
  { ID_CONTENIDO: 'ART-U', TIPO: 'Artista', ENVIAR_API: 'S', ACCION_API: 'ACTUALIZAR', ID_WEB: 11, TITULO: 'Artista actualizado' },
  { ID_CONTENIDO: 'REC-C', TIPO: 'Receta', ENVIAR_API: 'S', ACCION_API: 'CREAR', TITULO: 'Receta piloto', SLUG: 'receta-piloto', CUERPO: body('receta') },
  { ID_CONTENIDO: 'REC-U', TIPO: 'Receta', ENVIAR_API: 'S', ACCION_API: 'ACTUALIZAR', ID_WEB: 22, REGION: 'Cuyo' },
  { ID_CONTENIDO: 'MIT-C', TIPO: 'Mito', ENVIAR_API: 'S', ACCION_API: 'CREAR', TITULO: 'Mito piloto', SLUG: 'mito-piloto', CUERPO: body('mito') },
  { ID_CONTENIDO: 'MIT-U', TIPO: 'Mito', ENVIAR_API: 'S', ACCION_API: 'ACTUALIZAR', ID_WEB: 33, REGION: 'Litoral' },
];

try {
  const valid = run(writeCsv('valid.csv', validRows));
  assert.equal(valid.status, 0, valid.stderr);
  assert.match(valid.stdout, /PREFLIGHT OK/);
  assert.match(valid.stdout, /Artista CREAR -> POST \/artists/);
  assert.match(valid.stdout, /Mito ACTUALIZAR -> PUT \/myths\/33/);

  const invalidRows = validRows.map((row) => ({ ...row }));
  invalidRows[1].TITULO = '';
  const invalid = run(writeCsv('invalid.csv', invalidRows));
  assert.equal(invalid.status, 1);
  assert.match(invalid.stderr, /no contiene campos para modificar/);
  assert.match(invalid.stderr, /PREFLIGHT BLOQUEADO/);

  const unknownEnabled = run(writeCsv('unknown-enabled.csv', [
    ...validRows,
    { ID_CONTENIDO: 'NOT-EXTRA', TIPO: 'Noticia', ENVIAR_API: 'S', ACCION_API: 'CREAR' },
  ]));
  assert.equal(unknownEnabled.status, 1);
  assert.match(unknownEnabled.stderr, /ENVIAR_API=S no permitido para TIPO=Noticia/);
  assert.match(unknownEnabled.stderr, /exactamente 6 filas con ENVIAR_API=S; encontradas=7/);

  const typoEnabled = run(writeCsv('typo-enabled.csv', [
    ...validRows,
    { ID_CONTENIDO: 'ART-TYPO', TIPO: 'Artitsa', ENVIAR_API: 'S', ACCION_API: 'CREAR' },
  ]));
  assert.equal(typoEnabled.status, 1);
  assert.match(typoEnabled.stderr, /ENVIAR_API=S no permitido para TIPO=Artitsa/);
} finally {
  rmSync(directory, { recursive: true, force: true });
}

console.log('Refresh pilot preflight tests passed.');

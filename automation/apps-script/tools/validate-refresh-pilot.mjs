import fs from 'node:fs';
import vm from 'node:vm';

const args = process.argv.slice(2);
const csvPath = args.find((argument) => !argument.startsWith('--'));
const scope = (args.find((argument) => argument.startsWith('--scope=')) || '--scope=core').split('=')[1];

if (!csvPath) {
  console.error('Uso: npm run apps-script:pilot:check -- <contenidos.csv> [--scope=core|directories]');
  process.exit(2);
}

function parseCsv(text) {
  const rows = [];
  let row = [];
  let cell = '';
  let quoted = false;

  for (let index = 0; index < text.length; index++) {
    const character = text[index];
    const next = text[index + 1];

    if (character === '"' && quoted && next === '"') {
      cell += '"';
      index++;
    } else if (character === '"') {
      quoted = !quoted;
    } else if (character === ',' && !quoted) {
      row.push(cell);
      cell = '';
    } else if ((character === '\n' || character === '\r') && !quoted) {
      if (character === '\r' && next === '\n') index++;
      row.push(cell);
      if (row.some((value) => value !== '')) rows.push(row);
      row = [];
      cell = '';
    } else {
      cell += character;
    }
  }

  if (quoted) throw new Error('CSV inválido: comillas sin cerrar.');
  if (cell !== '' || row.length) {
    row.push(cell);
    rows.push(row);
  }

  return rows;
}

function recordsFromCsv(text) {
  const rows = parseCsv(text.replace(/^\uFEFF/, ''));
  if (rows.length < 2) throw new Error('El CSV no contiene filas de contenido.');

  const headers = rows[0].map((header) => header.trim());
  const duplicates = headers.filter((header, index) => header && headers.indexOf(header) !== index);
  if (duplicates.length) throw new Error(`Encabezados duplicados: ${[...new Set(duplicates)].join(', ')}`);

  return rows.slice(1).map((values, rowIndex) => ({
    csvRow: rowIndex + 2,
    values: Object.fromEntries(headers.map((header, column) => [header, values[column] ?? ''])),
  }));
}

const source = fs.readFileSync(new URL('../Código.js', import.meta.url), 'utf8');
const context = vm.createContext({ console });
vm.runInContext(source, context, { filename: 'Código.js' });

function normalize(value) {
  return String(value || '').trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

const scopes = {
  core: [
    { key: 'artista', types: ['artista'], label: 'Artista', processor: 'procesarArtistaMFA_' },
    { key: 'receta', types: ['receta'], label: 'Receta', processor: 'procesarRecetaMFA_' },
    { key: 'mito', types: ['mito'], label: 'Mito', processor: 'procesarMitoMFA_' },
  ],
  directories: [
    { key: 'penia', types: ['pena', 'penia'], label: 'Peña', processor: 'procesarPeniaMFA_' },
    { key: 'radio', types: ['radio'], label: 'Radio', processor: 'procesarRadioMFA_' },
    { key: 'programaradio', types: ['programaradio', 'programa radio', 'programa de radio'], label: 'ProgramaRadio', processor: 'procesarProgramaRadioMFA_' },
  ],
};

if (!scopes[scope]) {
  console.error(`PREFLIGHT ERROR: scope inválido: ${scope}. Use core o directories.`);
  process.exit(2);
}

const definitions = scopes[scope];
const entities = Object.fromEntries(definitions.flatMap((definition) =>
  definition.types.map((type) => [type, definition])
));

let records;
try {
  records = recordsFromCsv(fs.readFileSync(csvPath, 'utf8'));
} catch (error) {
  console.error(`PREFLIGHT ERROR: ${error.message}`);
  process.exit(1);
}

const errors = [];
const observed = new Map();
const enabled = records.filter(({ values }) =>
  normalize(values.ENVIAR_API) === 's'
);
const unsupported = enabled.filter(({ values }) =>
  !entities[normalize(values.TIPO)]
);
const candidates = enabled.filter(({ values }) =>
  entities[normalize(values.TIPO)]
);

for (const { csvRow, values } of unsupported) {
  errors.push(
    `fila ${csvRow}: ENVIAR_API=S no permitido para TIPO=${String(values.TIPO || '').trim() || 'VACIO'}`
  );
}

for (const { csvRow, values } of candidates) {
  const type = normalize(values.TIPO);
  const action = normalize(values.ACCION_API);
  const definition = entities[type];
  const key = `${definition.key}:${action}`;
  observed.set(key, (observed.get(key) || 0) + 1);

  let request = null;
  Object.assign(context, {
    __row: values,
    __capture: (method, path, payload) => { request = { method, path, payload }; },
  });

  try {
    vm.runInContext(`
      apiMFA_ = (method, path, token, payload) => {
        __capture(method, path, payload);
        const id = Number(__row.ID_WEB || 9000 + ${csvRow});
        return { status: method === 'post' ? 201 : 200, json: { data: { id } } };
      };
      ${definition.processor}(__row, 'TOKEN_SIMULADO', () => {});
    `, context);
    console.log(`OK fila ${csvRow}: ${definition.label} ${action.toUpperCase()} -> ${request.method.toUpperCase()} ${request.path}`);
  } catch (error) {
    errors.push(`fila ${csvRow} (${definition.label} ${action || 'SIN_ACCION'}): ${error.message}`);
  }
}

for (const definition of definitions) {
  for (const action of ['crear', 'actualizar']) {
    const count = observed.get(`${definition.key}:${action}`) || 0;
    if (count !== 1) {
      errors.push(`se esperaba exactamente 1 fila ${definition.label} ${action.toUpperCase()} con ENVIAR_API=S; encontradas=${count}`);
    }
  }
}

if (enabled.length !== 6) {
  errors.push(`la exportación completa debe contener exactamente 6 filas con ENVIAR_API=S; encontradas=${enabled.length}`);
}

if (errors.length) {
  console.error('\nPREFLIGHT BLOQUEADO');
  errors.forEach((error) => console.error(`- ${error}`));
  process.exit(1);
}

console.log(`\nPREFLIGHT OK (${scope}): 6 contratos CREAR/ACTUALIZAR validados sin credenciales ni llamadas HTTP.`);

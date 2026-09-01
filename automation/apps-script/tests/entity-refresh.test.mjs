import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../Código.js', import.meta.url), 'utf8');

function createContext(globals = {}) {
  const context = vm.createContext({ console, ...globals });
  vm.runInContext(source, context, { filename: 'Código.js' });
  return context;
}

function plain(value) {
  return JSON.parse(JSON.stringify(value));
}

function longBody(subject) {
  return `<p>${Array.from({ length: 305 }, (_, i) => `${subject}-${i + 1}`).join(' ')}</p>`;
}

function executeProcessor({ processor, row, status, responseId }) {
  const context = createContext();
  let request = null;
  const stages = [];
  Object.assign(context, {
    __row: row,
    __status: status,
    __responseId: responseId,
    __capture: (method, path, payload) => { request = plain({ method, path, payload }); },
    __stage: (stage) => stages.push(stage),
  });

  const result = vm.runInContext(`
    apiMFA_ = (method, path, token, payload) => {
      __capture(method, path, payload);
      return { status: __status, json: { data: { id: __responseId } } };
    };
    ${processor}(__row, 'token-prueba', __stage);
  `, context);

  return { request, stages, result: plain(result) };
}

const artistCreate = executeProcessor({
  processor: 'procesarArtistaMFA_', status: 201, responseId: 101,
  row: {
    ID_CONTENIDO: 'MFA-ART-CREATE', ACCION_API: 'CREAR',
    ARTISTA: 'Mercedes Sosa', ARTIST_TYPE: 'SOLOIST', SLUG: 'mercedes-sosa',
    CUERPO: longBody('artista'), META_TITLE: 'Mercedes Sosa: biografía',
    INSTAGRAM_URL: 'https://instagram.com/mercedessosa',
  },
});
assert.deepEqual(artistCreate.request, {
  method: 'post', path: '/artists',
  payload: {
    interprete: 'Mercedes Sosa', artist_type: 'soloist', slug: 'mercedes-sosa',
    biografia: artistCreate.request.payload.biografia,
    seo_title: 'Mercedes Sosa: biografía', instagram: 'https://instagram.com/mercedessosa',
  },
});
assert.deepEqual(artistCreate.stages, ['PROCESANDO_POST']);
assert.equal(artistCreate.result.resultado, 'CREADO_DRAFT');
assert.equal(artistCreate.result.id, 101);

const artistUpdate = executeProcessor({
  processor: 'procesarArtistaMFA_', status: 200, responseId: 101,
  row: { ID_CONTENIDO: 'MFA-ART-UPDATE', ACCION_API: 'ACTUALIZAR', ID_WEB: 101, META_TITLE: 'SEO actualizado' },
});
assert.deepEqual(artistUpdate.request, { method: 'put', path: '/artists/101', payload: { seo_title: 'SEO actualizado' } });
assert.deepEqual(artistUpdate.stages, ['PROCESANDO_UPDATE']);
assert.equal(artistUpdate.result.resultado, 'ACTUALIZADO_API');

const recipeCreate = executeProcessor({
  processor: 'procesarRecetaMFA_', status: 201, responseId: 202,
  row: {
    ID_CONTENIDO: 'MFA-REC-CREATE', ACCION_API: 'CREAR', TITULO: 'Locro criollo',
    SLUG: 'locro-criollo', CUERPO: longBody('receta'),
    RECIPE_INGREDIENTS_JSON: '["maíz blanco", "zapallo"]',
    RECIPE_INSTRUCTIONS_JSON: 'Remojar; Cocinar', PREP_TIME_MINUTES: '30',
    COOK_TIME_MINUTES: 180, SERVINGS: '8 porciones', REGION: 'Noroeste argentino',
  },
});
assert.deepEqual(recipeCreate.request, {
  method: 'post', path: '/foods',
  payload: {
    titulo: 'Locro criollo', slug: 'locro-criollo', receta: recipeCreate.request.payload.receta,
    ingredients: ['maíz blanco', 'zapallo'], instructions: ['Remojar', 'Cocinar'],
    prep_time_minutes: 30, cook_time_minutes: 180, servings: '8 porciones', region: 'Noroeste argentino',
  },
});
assert.deepEqual(recipeCreate.stages, ['PROCESANDO_POST']);
assert.equal(recipeCreate.result.resultado, 'CREADO_DRAFT');
assert.equal(recipeCreate.result.id, 202);

const recipeUpdate = executeProcessor({
  processor: 'procesarRecetaMFA_', status: 200, responseId: 202,
  row: { ID_CONTENIDO: 'MFA-REC-UPDATE', ACCION_API: 'ACTUALIZAR', ID_WEB: 202, REGION: 'Cuyo' },
});
assert.deepEqual(recipeUpdate.request, { method: 'put', path: '/foods/202', payload: { region: 'Cuyo' } });
assert.deepEqual(recipeUpdate.stages, ['PROCESANDO_UPDATE']);
assert.equal(recipeUpdate.result.resultado, 'ACTUALIZADO_API');

const mythCreate = executeProcessor({
  processor: 'procesarMitoMFA_', status: 201, responseId: 303,
  row: {
    ID_CONTENIDO: 'MFA-MIT-CREATE', ACCION_API: 'CREAR', TITULO: 'La Salamanca',
    CONTENT_TYPE: 'LEGEND', SLUG: 'la-salamanca', CUERPO: longBody('mito'),
    REGION: 'Santiago del Estero', META_DESCRIPTION: 'Relato tradicional sobre la Salamanca.',
  },
});
assert.deepEqual(mythCreate.request, {
  method: 'post', path: '/myths',
  payload: {
    titulo: 'La Salamanca', content_type: 'legend', slug: 'la-salamanca',
    mito: mythCreate.request.payload.mito, region: 'Santiago del Estero',
    meta_description: 'Relato tradicional sobre la Salamanca.',
  },
});
assert.deepEqual(mythCreate.stages, ['PROCESANDO_POST']);
assert.equal(mythCreate.result.resultado, 'CREADO_DRAFT');
assert.equal(mythCreate.result.id, 303);

const mythUpdate = executeProcessor({
  processor: 'procesarMitoMFA_', status: 200, responseId: 303,
  row: { ID_CONTENIDO: 'MFA-MIT-UPDATE', ACCION_API: 'ACTUALIZAR', ID_WEB: 303, CONTENT_TYPE: 'urban_legend' },
});
assert.deepEqual(mythUpdate.request, { method: 'put', path: '/myths/303', payload: { content_type: 'urban_legend' } });
assert.deepEqual(mythUpdate.stages, ['PROCESANDO_UPDATE']);
assert.equal(mythUpdate.result.resultado, 'ACTUALIZADO_API');

const festivalUpdate = executeProcessor({
  processor: 'procesarFestivalMFA_', status: 200,
  row: {
    ID_CONTENIDO: 'MFA-FES-UPDATE', ACCION_API: 'ACTUALIZAR', ID_WEB: 404,
    TITULO: 'Festival Nacional del Folklore', SLUG: 'festival-nacional-del-folklore',
    CUERPO: longBody('festival'), PROVINCE_ID: 7, MES_ID: 1,
    META_TITLE: 'Festival Nacional del Folklore actualizado',
  },
});
assert.deepEqual(festivalUpdate.request, {
  method: 'put', path: '/festivals/404',
  payload: {
    title: 'Festival Nacional del Folklore', slug: 'festival-nacional-del-folklore',
    body: festivalUpdate.request.payload.body, province_id: 7, mes_id: 1,
    seo_title: 'Festival Nacional del Folklore actualizado',
  },
});
assert.equal('status' in festivalUpdate.request.payload, false, 'ACTUALIZAR debe preservar el estado existente');
for (const relation of ['news_ids', 'event_ids', 'interprete_ids', 'knowledge_article_ids']) {
  assert.equal(relation in festivalUpdate.request.payload, false, `ACTUALIZAR debe preservar ${relation} si la celda está vacía`);
}
assert.deepEqual(festivalUpdate.stages, ['PROCESANDO_UPDATE']);
assert.equal(festivalUpdate.result.resultado, 'ACTUALIZADO_API');
assert.equal(festivalUpdate.result.id, 404, 'ACTUALIZAR conserva ID_WEB cuando la respuesta no repite el ID');

const validationContext = createContext();
assert.throws(
  () => vm.runInContext("resolverAccionEntidadMFA_({ ACCION_API: 'ACTUALIZAR' }, 'Mito')", validationContext),
  /requiere ID_WEB/,
);
assert.throws(
  () => vm.runInContext("resolverAccionEntidadMFA_({ ACCION_API: 'CREAR', ID_WEB: 99 }, 'Receta')", validationContext),
  /no debe tener ID_WEB/,
);

function selectCandidate(rows, types) {
  const context = createContext();
  Object.assign(context, { __rows: rows, __types: types });
  return vm.runInContext('seleccionarCandidatoMFA_(__rows, __types)', context);
}

for (const type of ['Artista', 'Receta', 'Mito', 'Festival']) {
  const common = { TIPO: type, ESTADO: 'BORRADOR', ENVIAR_API: 'S', PRIORIDAD: 'ALTA' };
  const candidate = selectCandidate([
    { rowNumber: 2, values: { ...common, ACCION_API: 'CREAR', RESULTADO_API: 'CREADO_DRAFT' } },
    { rowNumber: 3, values: { ...common, ACCION_API: 'ACTUALIZAR', ID_WEB: 50, RESULTADO_API: 'ACTUALIZADO_API' } },
    { rowNumber: 4, values: { ...common, ESTADO: 'PUBLICADO', ACCION_API: 'CREAR' } },
    { rowNumber: 5, values: { ...common, ACCION_API: 'ACTUALIZAR' } },
    { rowNumber: 6, values: { ...common, PRIORIDAD: 'MEDIA', ACCION_API: 'CREAR', SCORE_CALIDAD: 10 } },
    { rowNumber: 7, values: { ...common, ACCION_API: 'CREAR', SCORE_CALIDAD: 80 } },
  ], [type.toLowerCase()]);
  assert.equal(candidate.rowNumber, 7, `${type} debe omitir filas ya enviadas o inválidas`);
}

function createFakeSheet(headers, row) {
  const values = [headers.slice(), headers.map((header) => row[header] ?? '')];
  return {
    values,
    getName: () => 'Contenidos',
    getDataRange: () => ({ getValues: () => values.map((item) => item.slice()) }),
    getRange(rowNumber, columnNumber) {
      return { setValue(value) { values[rowNumber - 1][columnNumber - 1] = value; return this; } };
    },
  };
}

function executeQueue({ row, apiResponse, apiError }) {
  const headers = [
    'ID_CONTENIDO', 'TIPO', 'ESTADO', 'ENVIAR_API', 'ACCION_API', 'ID_WEB',
    'ARTISTA', 'SLUG', 'CUERPO', 'RESULTADO_API', 'ERROR_API', 'FECHA_ENVIO_API',
  ];
  const sheet = createFakeSheet(headers, row);
  const spreadsheet = { getSheetByName: (name) => (name === 'Contenidos' ? sheet : null) };
  let released = false;
  let audit = null;
  const context = createContext({
    LockService: { getScriptLock: () => ({ tryLock: () => true, releaseLock: () => { released = true; } }) },
    SpreadsheetApp: { flush: () => {} },
  });
  Object.assign(context, {
    __spreadsheet: spreadsheet, __apiResponse: apiResponse, __apiError: apiError,
    __audit: (...args) => { audit = args; },
  });
  vm.runInContext(`
    obtenerTokenApi_ = () => 'token-prueba';
    abrirPlanilla_ = () => __spreadsheet;
    registrarEjecucionCargaMFA_ = (...args) => __audit(...args);
    apiMFA_ = () => {
      if (__apiError) throw crearErrorMFA_(__apiError.resultado, __apiError.http, __apiError.mensaje);
      return __apiResponse;
    };
    cargarContenidosMFA();
  `, context);
  return {
    audit, released,
    persisted: Object.fromEntries(headers.map((header, index) => [header, sheet.values[1][index]])),
  };
}

const queueSuccess = executeQueue({
  row: {
    ID_CONTENIDO: 'MFA-ART-QUEUE-OK', TIPO: 'Artista', ESTADO: 'BORRADOR',
    ENVIAR_API: 'S', ACCION_API: 'CREAR', ARTISTA: 'Artista de cola',
    SLUG: 'artista-de-cola', CUERPO: longBody('cola'),
  },
  apiResponse: { status: 201, json: { id: 404 } },
});
assert.equal(queueSuccess.persisted.ESTADO, 'PUBLICADO');
assert.equal(queueSuccess.persisted.ENVIAR_API, 'N');
assert.equal(queueSuccess.persisted.ID_WEB, 404);
assert.equal(queueSuccess.persisted.RESULTADO_API, 'CREADO_DRAFT');
assert.equal(queueSuccess.persisted.ERROR_API, '');
assert.equal(
  Object.prototype.toString.call(queueSuccess.persisted.FECHA_ENVIO_API),
  '[object Date]',
);
assert.equal(queueSuccess.audit[3], 'OK');
assert.equal(queueSuccess.audit[4], 0);
assert.equal(queueSuccess.released, true);

const queueError = executeQueue({
  row: {
    ID_CONTENIDO: 'MFA-ART-QUEUE-ERROR', TIPO: 'Artista', ESTADO: 'BORRADOR',
    ENVIAR_API: 'S', ACCION_API: 'CREAR', ARTISTA: 'Artista rechazado',
    SLUG: 'artista-rechazado', CUERPO: longBody('rechazo'),
  },
  apiError: { resultado: 'ERROR_VALIDACION', http: 422, mensaje: 'El slug ya fue utilizado.' },
});
assert.equal(queueError.persisted.ESTADO, 'BORRADOR');
assert.equal(queueError.persisted.ENVIAR_API, 'N');
assert.equal(queueError.persisted.RESULTADO_API, 'ERROR_VALIDACION');
assert.equal(queueError.persisted.ERROR_API, 'El slug ya fue utilizado.');
assert.equal(queueError.persisted.FECHA_ENVIO_API, '');
assert.equal(queueError.audit[3], 'ERROR');
assert.equal(queueError.audit[4], 1);
assert.equal(queueError.released, true);

console.log('Entity refresh Apps Script tests passed.');

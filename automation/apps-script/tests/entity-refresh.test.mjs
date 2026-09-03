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

const peniaCreate = executeProcessor({
  processor: 'procesarPeniaMFA_', status: 201, responseId: 401,
  row: {
    ID_CONTENIDO: 'MFA-PEN-CREATE', ACCION_API: 'CREAR', TITULO: 'Peña La Amistad',
    SLUG: 'penia-la-amistad', CUERPO: longBody('penia'), PROVINCE_ID: 7,
    CITY: 'Salta', VENUE_TYPE: 'PENIA', SOURCE_URLS: 'https://example.test/penia;https://example.test/agenda',
    EVENT_IDS: '10, 11', META_TITLE: 'Peña La Amistad en Salta',
  },
});
assert.deepEqual(peniaCreate.request, {
  method: 'post', path: '/penia-profiles',
  payload: {
    title: 'Peña La Amistad', slug: 'penia-la-amistad', body: peniaCreate.request.payload.body,
    province_id: 7, city: 'Salta', venue_type: 'penia',
    source_urls: ['https://example.test/penia', 'https://example.test/agenda'],
    seo_title: 'Peña La Amistad en Salta', event_ids: [10, 11],
    verification_status: 'pending', editorial_status: 'draft',
  },
});
assert.deepEqual(peniaCreate.stages, ['PROCESANDO_POST']);
assert.equal(peniaCreate.result.resultado, 'CREADO_DRAFT');
assert.equal(peniaCreate.result.id, 401);

const peniaUpdate = executeProcessor({
  processor: 'procesarPeniaMFA_', status: 200, responseId: 401,
  row: { ID_CONTENIDO: 'MFA-PEN-UPDATE', ACCION_API: 'ACTUALIZAR', ID_WEB: 401, META_DESCRIPTION: 'Ficha actualizada de la Peña.' },
});
assert.deepEqual(peniaUpdate.request, { method: 'put', path: '/penia-profiles/401', payload: { meta_description: 'Ficha actualizada de la Peña.' } });
assert.deepEqual(peniaUpdate.stages, ['PROCESANDO_UPDATE']);

const radioCreate = executeProcessor({
  processor: 'procesarRadioMFA_', status: 201, responseId: 501,
  row: {
    ID_CONTENIDO: 'MFA-RAD-CREATE', ACCION_API: 'CREAR', TITULO: 'Radio Nacional Folklórica',
    SLUG: 'radio-nacional-folklorica', CUERPO: longBody('radio'), EDITORIAL_FOCUS: 'FOLKLORE',
    TRANSMISSION_MODES: 'AIR;STREAMING', COVERAGE_SCOPE: 'NATIONAL', PROVINCE_ID: 2,
    SOURCE_URLS: 'https://example.test/radio',
    RADIO_CHANNELS_JSON: '[{"label":"FM 98.7","channel_type":"frequency","frequency_band":"FM","frequency":"98.7","is_primary":true,"is_active":true}]',
  },
});
assert.deepEqual(radioCreate.request, {
  method: 'post', path: '/radio-signals',
  payload: {
    title: 'Radio Nacional Folklórica', slug: 'radio-nacional-folklorica', body: radioCreate.request.payload.body,
    editorial_focus: 'folklore', transmission_modes: ['air', 'streaming'], province_id: 2,
    coverage_scope: 'national', source_urls: ['https://example.test/radio'],
    channels: [{ label: 'FM 98.7', channel_type: 'frequency', frequency_band: 'FM', frequency: '98.7', is_primary: true, is_active: true }],
    verification_status: 'pending', editorial_status: 'draft',
  },
});
assert.equal(radioCreate.result.id, 501);

const radioUpdate = executeProcessor({
  processor: 'procesarRadioMFA_', status: 200, responseId: 501,
  row: { ID_CONTENIDO: 'MFA-RAD-UPDATE', ACCION_API: 'ACTUALIZAR', ID_WEB: 501, COVERAGE_NOTES: 'Cobertura actualizada.' },
});
assert.deepEqual(radioUpdate.request, { method: 'put', path: '/radio-signals/501', payload: { coverage_notes: 'Cobertura actualizada.' } });

const programCreate = executeProcessor({
  processor: 'procesarProgramaRadioMFA_', status: 201, responseId: 601,
  row: {
    ID_CONTENIDO: 'MFA-PRG-CREATE', ACCION_API: 'CREAR', TITULO: 'La noche folklórica',
    SLUG: 'la-noche-folklorica', CUERPO: longBody('programa'), IS_FOLKLORE: 'S', RADIO_SIGNAL_ID: 501,
    SOURCE_URLS: 'https://example.test/programa',
    RADIO_SLOTS_JSON: '[{"weekday":1,"starts_at":"20:00","ends_at":"22:00","timezone":"America/Argentina/Buenos_Aires","is_active":true}]',
  },
});
assert.deepEqual(programCreate.request, {
  method: 'post', path: '/radio-programs',
  payload: {
    radio_signal_id: 501, title: 'La noche folklórica', slug: 'la-noche-folklorica',
    body: programCreate.request.payload.body, is_folklore: true,
    source_urls: ['https://example.test/programa'],
    slots: [{ weekday: 1, starts_at: '20:00', ends_at: '22:00', timezone: 'America/Argentina/Buenos_Aires', is_active: true }],
    verification_status: 'pending', editorial_status: 'draft',
  },
});
assert.equal(programCreate.result.id, 601);

const programUpdate = executeProcessor({
  processor: 'procesarProgramaRadioMFA_', status: 200, responseId: 601,
  row: { ID_CONTENIDO: 'MFA-PRG-UPDATE', ACCION_API: 'ACTUALIZAR', ID_WEB: 601, META_TITLE: 'La noche folklórica | Radio' },
});
assert.deepEqual(programUpdate.request, { method: 'put', path: '/radio-programs/601', payload: { seo_title: 'La noche folklórica | Radio' } });

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
assert.throws(
  () => vm.runInContext("resolverAccionEntidadMFA_({}, 'Artista')", validationContext),
  /debe ser CREAR o ACTUALIZAR/,
  'Las entidades de refresh no deben inferir CREAR cuando falta ACCION_API',
);

for (const [processor, type] of [
  ['procesarArtistaMFA_', 'Artista'],
  ['procesarRecetaMFA_', 'Receta'],
  ['procesarMitoMFA_', 'Mito'],
  ['procesarPeniaMFA_', 'Peña'],
  ['procesarRadioMFA_', 'Radio'],
  ['procesarProgramaRadioMFA_', 'ProgramaRadio'],
]) {
  assert.throws(
    () => executeProcessor({
      processor,
      status: 200,
      responseId: 99,
      row: { ID_CONTENIDO: `MFA-${type}-NOOP`, ACCION_API: 'ACTUALIZAR', ID_WEB: 99 },
    }),
    /no contiene campos para modificar/,
    `${type} ACTUALIZAR debe rechazar un payload vacío`,
  );
}

function selectCandidate(rows, types) {
  const context = createContext();
  Object.assign(context, { __rows: rows, __types: types });
  return vm.runInContext('seleccionarCandidatoMFA_(__rows, __types)', context);
}

for (const type of ['Artista', 'Receta', 'Mito', 'Festival', 'Peña', 'Radio', 'ProgramaRadio']) {
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

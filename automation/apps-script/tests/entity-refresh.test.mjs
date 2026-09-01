import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(
  new URL('../Código.js', import.meta.url),
  'utf8',
);
const context = vm.createContext({ console });

vm.runInContext(source, context, { filename: 'Código.js' });

function executeProcessor(processor, row) {
  let request = null;
  context.__row = row;
  context.__capture = (method, path, payload) => {
    request = JSON.parse(JSON.stringify({ method, path, payload }));
  };

  const result = vm.runInContext(`
    apiMFA_ = (method, path, token, payload) => {
      __capture(method, path, payload);
      return { status: 200, json: { id: __row.ID_WEB } };
    };
    ${processor}(__row, 'token-prueba', () => {});
  `, context);

  return { request, result };
}

const artist = executeProcessor('procesarArtistaMFA_', {
  ID_CONTENIDO: 'MFA-ART-TEST',
  ACCION_API: 'ACTUALIZAR',
  ID_WEB: 101,
  META_TITLE: 'SEO actualizado',
});
assert.deepEqual(artist.request, {
  method: 'put',
  path: '/artists/101',
  payload: { seo_title: 'SEO actualizado' },
});
assert.equal(artist.result.resultado, 'ACTUALIZADO_API');

const recipe = executeProcessor('procesarRecetaMFA_', {
  ID_CONTENIDO: 'MFA-REC-TEST',
  ACCION_API: 'ACTUALIZAR',
  ID_WEB: 202,
  REGION: 'Cuyo',
});
assert.deepEqual(recipe.request, {
  method: 'put',
  path: '/foods/202',
  payload: { region: 'Cuyo' },
});

const myth = executeProcessor('procesarMitoMFA_', {
  ID_CONTENIDO: 'MFA-MIT-TEST',
  ACCION_API: 'ACTUALIZAR',
  ID_WEB: 303,
  CONTENT_TYPE: 'legend',
});
assert.deepEqual(myth.request, {
  method: 'put',
  path: '/myths/303',
  payload: { content_type: 'legend' },
});

assert.throws(
  () => vm.runInContext(
    "resolverAccionEntidadMFA_({ ACCION_API: 'ACTUALIZAR' }, 'Mito')",
    context,
  ),
  /requiere ID_WEB/,
);

console.log('Entity refresh Apps Script tests passed.');

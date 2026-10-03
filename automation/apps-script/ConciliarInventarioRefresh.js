/**
 * Concilia ID_WEB de Inventario_Refresh contra la API autenticada de MFA.
 *
 * Seguridad:
 * - sólo consulta endpoints GET;
 * - sólo escribe la columna ID_WEB;
 * - nunca sobrescribe un ID existente;
 * - cruza exclusivamente por slug normalizado;
 * - deja sin completar slugs faltantes o duplicados.
 *
 * Ejecutar conciliarInventarioRefreshMFA() para las cuatro entidades o usar
 * las funciones por entidad para revisar los resultados en etapas.
 */

const MFA_INVENTARIO_REFRESH = Object.freeze({
  SHEET: 'Inventario_Refresh',
  ENTITY_HEADER: 'ENTIDAD',
  ID_HEADER: 'ID_WEB',
  SLUG_HEADER: 'SLUG',
  ENDPOINTS: Object.freeze({
    biografia: '/artists',
    receta: '/foods',
    mito: '/myths',
    festival: '/festivals',
  }),
  MAX_PAGES: 500,
});

function conciliarInventarioRefreshMFA() {
  return conciliarInventarioRefreshEntidadesMFA_([
    'Biografia',
    'Receta',
    'Mito',
    'Festival',
  ]);
}

function conciliarIdsBiografiasMFA() {
  return conciliarInventarioRefreshEntidadesMFA_(['Biografia']);
}

function conciliarIdsRecetasMFA() {
  return conciliarInventarioRefreshEntidadesMFA_(['Receta']);
}

function conciliarIdsMitosMFA() {
  return conciliarInventarioRefreshEntidadesMFA_(['Mito']);
}

function conciliarIdsFestivalesMFA() {
  return conciliarInventarioRefreshEntidadesMFA_(['Festival']);
}

function conciliarInventarioRefreshEntidadesMFA_(entidades) {
  const lock = LockService.getScriptLock();
  if (!lock.tryLock(30000)) {
    throw new Error('Ya existe otra conciliación de Inventario_Refresh en ejecución.');
  }

  try {
    const token = obtenerTokenApi_();
    const ss = abrirPlanilla_();
    const sheet = ss.getSheetByName(MFA_INVENTARIO_REFRESH.SHEET);
    if (!sheet) {
      throw new Error('No existe la pestaña Inventario_Refresh.');
    }

    const lastRow = sheet.getLastRow();
    const lastColumn = sheet.getLastColumn();
    if (lastRow < 2) {
      return { actualizados: 0, faltantes: 0, duplicados: 0, existentes: 0, porEntidad: {} };
    }

    const values = sheet.getRange(1, 1, lastRow, lastColumn).getValues();
    const headers = values[0].map((value) => String(value || '').trim().toUpperCase());
    const entityIndex = headers.indexOf(MFA_INVENTARIO_REFRESH.ENTITY_HEADER);
    const idIndex = headers.indexOf(MFA_INVENTARIO_REFRESH.ID_HEADER);
    const slugIndex = headers.indexOf(MFA_INVENTARIO_REFRESH.SLUG_HEADER);

    if (entityIndex < 0 || idIndex < 0 || slugIndex < 0) {
      throw new Error('Inventario_Refresh debe contener ENTIDAD, ID_WEB y SLUG.');
    }

    const resumen = {
      actualizados: 0,
      faltantes: 0,
      duplicados: 0,
      existentes: 0,
      porEntidad: {},
    };

    entidades.forEach((entidadSolicitada) => {
      const key = normalizarTextoMFA_(entidadSolicitada);
      const endpoint = MFA_INVENTARIO_REFRESH.ENDPOINTS[key];
      if (!endpoint) {
        throw new Error('Entidad no soportada para conciliación: ' + entidadSolicitada);
      }

      const catalogo = obtenerCatalogoCompletoMFA_(endpoint, token);
      const porSlug = indexarCatalogoPorSlugMFA_(catalogo);
      const detalle = {
        endpoint,
        catalogo: catalogo.length,
        actualizados: 0,
        faltantes: [],
        duplicados: [],
        existentes: 0,
      };

      for (let rowIndex = 1; rowIndex < values.length; rowIndex += 1) {
        const row = values[rowIndex];
        if (normalizarTextoMFA_(row[entityIndex]) !== key) continue;

        if (enteroOpcionalMFA_(row[idIndex])) {
          detalle.existentes += 1;
          resumen.existentes += 1;
          continue;
        }

        const slug = normalizarTextoMFA_(row[slugIndex]);
        if (!slug) {
          detalle.faltantes.push({ fila: rowIndex + 1, slug: '', motivo: 'SLUG_VACIO' });
          resumen.faltantes += 1;
          continue;
        }

        const matches = porSlug[slug] || [];
        if (matches.length === 1) {
          row[idIndex] = matches[0];
          detalle.actualizados += 1;
          resumen.actualizados += 1;
        } else if (matches.length > 1) {
          detalle.duplicados.push({ fila: rowIndex + 1, slug, ids: matches });
          resumen.duplicados += 1;
        } else {
          detalle.faltantes.push({ fila: rowIndex + 1, slug, motivo: 'NO_ENCONTRADO_API' });
          resumen.faltantes += 1;
        }
      }

      resumen.porEntidad[entidadSolicitada] = detalle;
      console.log(
        'Conciliación ' + entidadSolicitada +
        ': catálogo=' + detalle.catalogo +
        ', actualizados=' + detalle.actualizados +
        ', existentes=' + detalle.existentes +
        ', faltantes=' + detalle.faltantes.length +
        ', duplicados=' + detalle.duplicados.length
      );
    });

    // Escritura única y acotada: sólo ID_WEB, sin alterar otras columnas.
    const idValues = values.slice(1).map((row) => [row[idIndex]]);
    sheet.getRange(2, idIndex + 1, idValues.length, 1).setValues(idValues);
    SpreadsheetApp.flush();

    console.log('Resumen conciliación MFA: ' + JSON.stringify(resumen));
    return resumen;
  } finally {
    lock.releaseLock();
  }
}

function obtenerCatalogoCompletoMFA_(endpoint, token) {
  const items = [];
  let page = 1;

  while (page <= MFA_INVENTARIO_REFRESH.MAX_PAGES) {
    const response = apiMFA_('get', endpoint, token, null, { page });
    const json = response.json || {};
    const current = extraerColeccionMFA_(json);
    Array.prototype.push.apply(items, current);

    const paginator = json.data && !Array.isArray(json.data) ? json.data : json;
    const currentPage = Number(paginator.current_page || page);
    const lastPage = Number(paginator.last_page || 0);
    const nextPageUrl = paginator.next_page_url || null;

    if (!current.length) break;
    if (lastPage && currentPage >= lastPage) break;
    if (!lastPage && !nextPageUrl) break;

    page += 1;
  }

  if (page > MFA_INVENTARIO_REFRESH.MAX_PAGES) {
    throw new Error('La paginación de ' + endpoint + ' superó el límite de seguridad.');
  }

  return items;
}

function indexarCatalogoPorSlugMFA_(items) {
  return items.reduce((index, item) => {
    const slug = normalizarTextoMFA_(item && item.slug);
    const id = enteroOpcionalMFA_(item && item.id);
    if (!slug || !id) return index;
    if (!index[slug]) index[slug] = [];
    if (!index[slug].includes(id)) index[slug].push(id);
    return index;
  }, {});
}

/**
 * Mi Folklore Argentino - IntegraciÃ³n editorial de mÃ©tricas
 * Fuentes: GA4 y Google Search Console (solo lectura).
 */

const MFA_CONFIG = Object.freeze({
  SPREADSHEET_ID: '1S3_Ukj9oV66SPJw-l0KPlWBg79rO14GBEcPxQl1sZzA',
  GA4_PROPERTY_ID: '339893486',
  SEARCH_CONSOLE_SITE: 'https://mifolkloreargentino.com/',
  DOMINIO_PRINCIPAL: 'https://mifolkloreargentino.com',
  TIME_ZONE: 'America/Argentina/Buenos_Aires',
  TRIGGER_HOUR: 6,
  CONTENT_TRIGGER_HOUR: 11,
  API_BASE_URL: 'https://mifolkloreargentino.com/api/v1',
  API_TOKEN_PROPERTY: 'MFA_API_TOKEN',
  SHEETS: {
    CONTENT: 'Contenidos',
    EXECUTIONS: 'Ejecuciones',
    CONFIG: 'Configuracion_Metricas',
    PAGES: 'Metricas_Paginas',
    SEO: 'Consultas_SEO',
    OPPORTUNITIES: 'Oportunidades',
    MONTHLY: 'Resumen_Mensual',
  },
});

/**
 * CatÃ¡logo de categorÃ­as admitidas por la API de Noticias.
 *
 * CATEGORIA_CONTENIDO es el nombre legible que guardamos en el Excel.
 * CATEGORIA_ID es el valor numÃ©rico que Laravel exige en categoria_id.
 *
 * Se incluyen variantes en singular para tolerar pequeÃ±as diferencias de carga,
 * aunque en la hoja conviene usar siempre los nombres canÃ³nicos en plural.
 */
const MFA_CATEGORIAS_NOTICIA = Object.freeze({
  actualidad: 1,
  festival: 2,
  festivales: 2,
  lanzamiento: 3,
  lanzamientos: 3,
  entrevista: 4,
  entrevistas: 4,
  cartelera: 5,
});

function onOpen() {
  SpreadsheetApp.getUi()
    .createMenu('MFA MÃ©tricas')
    .addItem('Configurar integraciÃ³n', 'configurarIntegracionMFA')
    .addItem('Actualizar ahora', 'actualizarMetricasMFA')
    .addSeparator()
    .addItem('Configurar carga diaria', 'configurarCargaContenidosMFA')
    .addItem('Probar conexiÃ³n API', 'probarConexionApiMFA')
    .addItem('Probar conexiÃ³n Noticias', 'probarConexionNoticiasMFA')
    .addItem('Probar carga ahora', 'cargarContenidosMFA')
    .addToUi();
}

/**
 * Ejecutar una sola vez. Crea las pestaÃ±as, instala el disparador diario
 * y realiza la primera actualizaciÃ³n.
 */
function configurarIntegracionMFA() {
  const ss = abrirPlanilla_();
  asegurarPestanas_(ss);
  instalarTriggerDiario_();
  actualizarMetricasMFA();
}

/** Ejecuta la sincronizaciÃ³n completa de GA4 y Search Console. */
function actualizarMetricasMFA() {
  const lock = LockService.getScriptLock();
  if (!lock.tryLock(30000)) {
    throw new Error('Ya existe otra actualizaciÃ³n de mÃ©tricas en ejecuciÃ³n.');
  }

  const inicio = new Date();
  const ss = abrirPlanilla_();
  asegurarPestanas_(ss);

  try {
    const paginas = construirMetricasPaginas_();
    const seo = obtenerSearchConsole_(['query', 'page'], 28);
    const mensual = construirResumenMensual_();
    const oportunidades = construirOportunidades_(paginas, seo);

    escribirMetricasPaginas_(ss, paginas);
    escribirConsultasSeo_(ss, seo);
    escribirOportunidades_(ss, oportunidades);
    escribirResumenMensual_(ss, mensual);
    escribirConfiguracion_(ss, 'OK', '', inicio, new Date());
  } catch (error) {
    escribirConfiguracion_(ss, 'ERROR', String(error && error.message ? error.message : error), inicio, new Date());
    throw error;
  } finally {
    lock.releaseLock();
  }
}

function construirMetricasPaginas_() {
  const r7 = obtenerGa4Paginas_('7daysAgo', 'yesterday');
  const r28 = obtenerGa4Paginas_('28daysAgo', 'yesterday');
  const r90 = obtenerGa4Paginas_('90daysAgo', 'yesterday');
  const rPrev28 = obtenerGa4Paginas_('56daysAgo', '29daysAgo');
  const paths = new Set([...Object.keys(r7), ...Object.keys(r28), ...Object.keys(r90), ...Object.keys(rPrev28)]);

  return [...paths].map((path) => {
    const d7 = r7[path] || vacioGa4Pagina_(path);
    const d28 = r28[path] || vacioGa4Pagina_(path);
    const d90 = r90[path] || vacioGa4Pagina_(path);
    const prev = rPrev28[path] || vacioGa4Pagina_(path);
    const variacion = prev.views > 0 ? (d28.views - prev.views) / prev.views : (d28.views > 0 ? 1 : 0);
    return {
      path,
      url: urlAbsoluta_(path),
      title: d28.title || d7.title || d90.title || prev.title || '',
      views7: d7.views,
      users7: d7.users,
      views28: d28.views,
      users28: d28.users,
      engagement28: d28.engagementRate,
      views90: d90.views,
      users90: d90.users,
      previousViews28: prev.views,
      variation28: variacion,
    };
  }).sort((a, b) => b.views28 - a.views28);
}

function obtenerGa4Paginas_(startDate, endDate) {
  const response = ejecutarReporteGa4_(
    ['pagePath', 'pageTitle'],
    ['screenPageViews', 'activeUsers', 'engagementRate'],
    startDate,
    endDate,
    100000,
  );
  const result = {};
  (response.rows || []).forEach((row) => {
    const path = row.dimensionValues[0].value || '/';
    const title = row.dimensionValues[1].value || '';
    const current = result[path] || vacioGa4Pagina_(path);
    current.title = current.title || title;
    current.views += numero_(row.metricValues[0].value);
    current.users += numero_(row.metricValues[1].value);
    current.engagementWeighted += numero_(row.metricValues[2].value) * numero_(row.metricValues[0].value);
    current.engagementWeight += numero_(row.metricValues[0].value);
    current.engagementRate = current.engagementWeight > 0
      ? current.engagementWeighted / current.engagementWeight
      : 0;
    result[path] = current;
  });
  return result;
}

function vacioGa4Pagina_(path) {
  return { path, title: '', views: 0, users: 0, engagementRate: 0, engagementWeighted: 0, engagementWeight: 0 };
}

function ejecutarReporteGa4_(dimensions, metrics, startDate, endDate, limit) {
  const request = AnalyticsData.newRunReportRequest();
  request.dimensions = dimensions.map((name) => {
    const dimension = AnalyticsData.newDimension();
    dimension.name = name;
    return dimension;
  });
  request.metrics = metrics.map((name) => {
    const metric = AnalyticsData.newMetric();
    metric.name = name;
    return metric;
  });
  const dateRange = AnalyticsData.newDateRange();
  dateRange.startDate = startDate;
  dateRange.endDate = endDate;
  request.dateRanges = [dateRange];
  request.limit = String(limit || 100000);
  return AnalyticsData.Properties.runReport(request, `properties/${MFA_CONFIG.GA4_PROPERTY_ID}`);
}

function obtenerSearchConsole_(dimensions, days) {
  const endDate = fechaDiasAtras_(2);
  const startDate = fechaDiasAtras_(days + 1);
  const body = {
    startDate,
    endDate,
    dimensions,
    rowLimit: 25000,
    dataState: 'final',
  };
  const site = encodeURIComponent(MFA_CONFIG.SEARCH_CONSOLE_SITE);
  const endpoint = `https://www.googleapis.com/webmasters/v3/sites/${site}/searchAnalytics/query`;
  const response = UrlFetchApp.fetch(endpoint, {
    method: 'post',
    contentType: 'application/json',
    headers: { Authorization: `Bearer ${ScriptApp.getOAuthToken()}` },
    payload: JSON.stringify(body),
    muteHttpExceptions: true,
  });
  const status = response.getResponseCode();
  const text = response.getContentText();
  if (status < 200 || status >= 300) {
    throw new Error(`Search Console respondiÃ³ ${status}: ${text.slice(0, 500)}`);
  }
  return JSON.parse(text).rows || [];
}

function construirResumenMensual_() {
  const ga = ejecutarReporteGa4_(
    ['yearMonth'],
    ['screenPageViews', 'activeUsers', 'sessions', 'engagementRate'],
    '365daysAgo',
    'yesterday',
    1000,
  );
  const porMes = {};
  (ga.rows || []).forEach((row) => {
    const raw = row.dimensionValues[0].value;
    const month = `${raw.slice(0, 4)}-${raw.slice(4, 6)}`;
    porMes[month] = {
      month,
      views: numero_(row.metricValues[0].value),
      users: numero_(row.metricValues[1].value),
      sessions: numero_(row.metricValues[2].value),
      engagementRate: numero_(row.metricValues[3].value),
      clicks: 0,
      impressions: 0,
      ctr: 0,
      position: 0,
      positionWeight: 0,
    };
  });

  const seoDiario = obtenerSearchConsole_(['date'], 365);
  seoDiario.forEach((row) => {
    const month = row.keys[0].slice(0, 7);
    const item = porMes[month] || {
      month, views: 0, users: 0, sessions: 0, engagementRate: 0,
      clicks: 0, impressions: 0, ctr: 0, position: 0, positionWeight: 0,
    };
    item.clicks += numero_(row.clicks);
    item.impressions += numero_(row.impressions);
    item.positionWeight += numero_(row.position) * numero_(row.impressions);
    porMes[month] = item;
  });

  return Object.values(porMes).map((item) => {
    item.ctr = item.impressions > 0 ? item.clicks / item.impressions : 0;
    item.position = item.impressions > 0 ? item.positionWeight / item.impressions : 0;
    return item;
  }).sort((a, b) => a.month.localeCompare(b.month));
}

function construirOportunidades_(paginas, seoRows) {
  const fecha = new Date();
  const oportunidades = [];

  seoRows.forEach((row) => {
    const query = row.keys[0] || '';
    const page = row.keys[1] || '';
    const clicks = numero_(row.clicks);
    const impressions = numero_(row.impressions);
    const ctr = numero_(row.ctr);
    const position = numero_(row.position);

    if (impressions >= 500 && position <= 10 && ctr < 0.015) {
      oportunidades.push({
        priority: 'Alta', type: 'Mejorar CTR', entity: query, url: page,
        detail: `${impressions} impresiones, ${clicks} clics, posiciÃ³n ${position.toFixed(1)}`,
        metric: 'CTR', value: ctr,
        recommendation: 'Revisar tÃ­tulo SEO y meta description sin cambiar la intenciÃ³n de bÃºsqueda.', date: fecha,
        score: impressions * (0.02 - ctr),
      });
    } else if (impressions >= 300 && position >= 4 && position <= 20) {
      oportunidades.push({
        priority: position <= 10 ? 'Media' : 'Baja', type: 'Potencial SEO', entity: query, url: page,
        detail: `${impressions} impresiones, ${clicks} clics, posiciÃ³n ${position.toFixed(1)}`,
        metric: 'PosiciÃ³n', value: position,
        recommendation: 'Actualizar o ampliar el contenido y reforzar enlaces internos relacionados.', date: fecha,
        score: impressions / Math.max(position, 1),
      });
    }
  });

  paginas.forEach((page) => {
    if (page.previousViews28 >= 50 && page.variation28 <= -0.30) {
      oportunidades.push({
        priority: 'Alta', type: 'PÃ¡gina en caÃ­da', entity: page.title, url: page.url,
        detail: `${page.previousViews28} â†’ ${page.views28} vistas en perÃ­odos comparables`,
        metric: 'VariaciÃ³n 28d', value: page.variation28,
        recommendation: 'Revisar actualizaciÃ³n, vigencia, competencia, enlaces internos y snippet.', date: fecha,
        score: Math.abs(page.variation28) * page.previousViews28,
      });
    } else if (page.views28 >= 50 && page.variation28 >= 0.30) {
      oportunidades.push({
        priority: 'Media', type: 'PÃ¡gina en crecimiento', entity: page.title, url: page.url,
        detail: `${page.previousViews28} â†’ ${page.views28} vistas en perÃ­odos comparables`,
        metric: 'VariaciÃ³n 28d', value: page.variation28,
        recommendation: 'Crear contenidos relacionados y reforzar el silo antes de que pierda impulso.', date: fecha,
        score: page.variation28 * page.views28,
      });
    }
  });

  const rank = { Alta: 3, Media: 2, Baja: 1 };
  return oportunidades
    .sort((a, b) => (rank[b.priority] - rank[a.priority]) || (b.score - a.score))
    .slice(0, 500);
}

function escribirMetricasPaginas_(ss, rows) {
  const headers = ['FECHA_ACTUALIZACION', 'URL', 'TITULO', 'VISTAS_7D', 'USUARIOS_7D', 'VISTAS_28D', 'USUARIOS_28D', 'ENGAGEMENT_28D', 'VISTAS_90D', 'USUARIOS_90D', 'VISTAS_28D_ANTERIOR', 'VARIACION_28D'];
  const now = new Date();
  const values = rows.map((r) => [now, r.url, r.title, r.views7, r.users7, r.views28, r.users28, r.engagement28, r.views90, r.users90, r.previousViews28, r.variation28]);
  escribirTabla_(ss.getSheetByName(MFA_CONFIG.SHEETS.PAGES), headers, values);
}

function escribirConsultasSeo_(ss, rows) {
  const headers = ['FECHA_ACTUALIZACION', 'CONSULTA', 'PAGINA', 'CLICS_28D', 'IMPRESIONES_28D', 'CTR_28D', 'POSICION_28D'];
  const now = new Date();
  const values = rows.map((r) => [now, r.keys[0] || '', r.keys[1] || '', numero_(r.clicks), numero_(r.impressions), numero_(r.ctr), numero_(r.position)]);
  escribirTabla_(ss.getSheetByName(MFA_CONFIG.SHEETS.SEO), headers, values);
}

function escribirOportunidades_(ss, rows) {
  const headers = ['PRIORIDAD', 'TIPO', 'ENTIDAD_CONSULTA', 'URL', 'DETALLE', 'METRICA', 'VALOR', 'RECOMENDACION', 'FECHA_DETECCION'];
  const values = rows.map((r) => [r.priority, r.type, r.entity, r.url, r.detail, r.metric, r.value, r.recommendation, r.date]);
  escribirTabla_(ss.getSheetByName(MFA_CONFIG.SHEETS.OPPORTUNITIES), headers, values);
}

function escribirResumenMensual_(ss, rows) {
  const headers = ['MES', 'VISTAS_GA4', 'USUARIOS_GA4', 'SESIONES_GA4', 'ENGAGEMENT_GA4', 'CLICS_GOOGLE', 'IMPRESIONES_GOOGLE', 'CTR_GOOGLE', 'POSICION_GOOGLE'];
  const values = rows.map((r) => [r.month, r.views, r.users, r.sessions, r.engagementRate, r.clicks, r.impressions, r.ctr, r.position]);
  escribirTabla_(ss.getSheetByName(MFA_CONFIG.SHEETS.MONTHLY), headers, values);
}

function escribirTabla_(sheet, headers, rows) {
  if (sheet.getFilter()) sheet.getFilter().remove();
  sheet.clear();
  sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
  if (rows.length) sheet.getRange(2, 1, rows.length, headers.length).setValues(rows);

  const header = sheet.getRange(1, 1, 1, headers.length);
  header.setBackground('#7A3E22').setFontColor('#FFFFFF').setFontWeight('bold');
  header.setHorizontalAlignment('center');
  sheet.setFrozenRows(1);
  if (rows.length) sheet.getRange(1, 1, rows.length + 1, headers.length).createFilter();
  sheet.getDataRange().setVerticalAlignment('top');
  sheet.autoResizeColumns(1, headers.length);

  headers.forEach((name, index) => {
    const col = index + 1;
    const range = rows.length ? sheet.getRange(2, col, rows.length, 1) : null;
    if (!range) return;
    if (/FECHA/.test(name)) range.setNumberFormat('yyyy-mm-dd hh:mm');
    if (/CTR|ENGAGEMENT|VARIACION/.test(name)) range.setNumberFormat('0.00%');
    if (/VISTAS|USUARIOS|SESIONES|CLICS|IMPRESIONES/.test(name)) range.setNumberFormat('#,##0');
    if (/POSICION/.test(name)) range.setNumberFormat('0.00');
  });

  for (let c = 1; c <= headers.length; c++) {
    if (sheet.getColumnWidth(c) > 420) sheet.setColumnWidth(c, 420);
  }
}

function escribirConfiguracion_(ss, status, detail, start, end) {
  const sheet = ss.getSheetByName(MFA_CONFIG.SHEETS.CONFIG);
  const trigger = ScriptApp.getProjectTriggers().some((t) => t.getHandlerFunction() === 'actualizarMetricasMFA') ? 'ACTIVO' : 'NO CONFIGURADO';
  const values = [
    ['PARAMETRO', 'VALOR'],
    ['GA4_PROPERTY_ID', MFA_CONFIG.GA4_PROPERTY_ID],
    ['SEARCH_CONSOLE_SITE', MFA_CONFIG.SEARCH_CONSOLE_SITE],
    ['DOMINIO_PRINCIPAL', MFA_CONFIG.DOMINIO_PRINCIPAL],
    ['ZONA_HORARIA', MFA_CONFIG.TIME_ZONE],
    ['ACTUALIZACION_DIARIA', `Entre ${MFA_CONFIG.TRIGGER_HOUR}:00 y ${MFA_CONFIG.TRIGGER_HOUR + 1}:00`],
    ['TRIGGER', trigger],
    ['ULTIMO_INICIO', start],
    ['ULTIMO_FIN', end],
    ['ESTADO', status],
    ['DETALLE_ERROR', detail || ''],
  ];
  sheet.clear();
  sheet.getRange(1, 1, values.length, 2).setValues(values);
  sheet.getRange(1, 1, 1, 2).setBackground('#7A3E22').setFontColor('#FFFFFF').setFontWeight('bold');
  sheet.getRange(8, 2, 2, 1).setNumberFormat('yyyy-mm-dd hh:mm:ss');
  sheet.setFrozenRows(1);
  sheet.autoResizeColumns(1, 2);
  sheet.setColumnWidth(2, Math.min(Math.max(sheet.getColumnWidth(2), 240), 500));
}

function asegurarPestanas_(ss) {
  Object.values(MFA_CONFIG.SHEETS).forEach((name) => {
    if (!ss.getSheetByName(name)) ss.insertSheet(name);
  });
}

function instalarTriggerDiario_() {
  ScriptApp.getProjectTriggers()
    .filter((t) => t.getHandlerFunction() === 'actualizarMetricasMFA')
    .forEach((t) => ScriptApp.deleteTrigger(t));
  ScriptApp.newTrigger('actualizarMetricasMFA')
    .timeBased()
    .atHour(MFA_CONFIG.TRIGGER_HOUR)
    .everyDays(1)
    .inTimezone(MFA_CONFIG.TIME_ZONE)
    .create();
}

/**
 * Configura una sola vez el disparador de carga de contenidos. El token debe
 * existir previamente en Script Properties con la clave MFA_API_TOKEN.
 * No ejecuta la carga: permite instalar primero y probar por separado.
 */
function configurarCargaContenidosMFA() {
  obtenerTokenApi_();
  instalarTriggerCargaContenidos_();
  SpreadsheetApp.getUi().alert(
    'Carga diaria configurada',
    `El cargador se ejecutarÃ¡ diariamente entre ${MFA_CONFIG.CONTENT_TRIGGER_HOUR}:00 y ${MFA_CONFIG.CONTENT_TRIGGER_HOUR + 1}:00.`,
    SpreadsheetApp.getUi().ButtonSet.OK,
  );
}

/**
 * Procesa la cola editorial de Mi Folklore Argentino.
 *
 * En cada ejecuciÃ³n intenta cargar, como mÃ¡ximo:
 *   1. un artÃ­culo Evergreen;
 *   2. un Evento;
 *   3. una Noticia;
 *   4. un Festival.
 *
 * IMPORTANTE SOBRE LOS TIPOS:
 * - TIPO puede ser: Noticia, Evento, Evergreen o Festival.
 * - "Festivales" sigue siendo una categorÃ­a vÃ¡lida de Noticia cuando la pieza
 *   es cobertura de actualidad. TIPO=Festival se reserva para la ficha evergreen
 *   y estable del festival.
 * - Para Festival, ACCION_API define CREAR (POST) o ACTUALIZAR (PUT).
 *
 * IMPORTANTE SOBRE LOS ESTADOS DEL SHEET:
 * - BORRADOR: todavÃ­a no fue recibido correctamente por la API.
 * - PUBLICADO: la API respondiÃ³ HTTP 201 y devolviÃ³ el contenido creado.
 * - DESCARTADO: no debe volver a procesarse, por ejemplo por ser duplicado.
 */
function cargarContenidosMFA() {
  // Evita que dos triggers o ejecuciones manuales procesen la misma fila al mismo tiempo.
  const lock = LockService.getScriptLock();
  if (!lock.tryLock(30000)) {
    throw new Error('Ya existe una carga de contenidos en ejecuciÃ³n.');
  }

  const inicio = new Date();
  const resumen = [];
  let exitos = 0;
  let errores = 0;
  let duplicados = 0;
  let estadoGlobal = 'SIN_PENDIENTES';

  try {
    // La credencial se obtiene desde Script Properties; nunca debe escribirse en el cÃ³digo.
    const token = obtenerTokenApi_();

    // Abre la bandeja editorial y valida que exista la pestaÃ±a de contenidos.
    const ss = abrirPlanilla_();
    const sheet = ss.getSheetByName(MFA_CONFIG.SHEETS.CONTENT);
    if (!sheet) {
      throw new Error(`No existe la pestaÃ±a ${MFA_CONFIG.SHEETS.CONTENT}.`);
    }

    // Convierte la hoja en una estructura de encabezados + filas para trabajar por nombre de campo.
    const tabla = leerTablaConEncabezados_(sheet);

    // Localiza una sola vez la columna ESTADO.
    // Se usa para actualizar el estado operativo del contenido despuÃ©s de procesar la API.
    const columnaEstado = tabla.headers.findIndex(
      (encabezado) => String(encabezado).trim().toUpperCase() === 'ESTADO'
    ) + 1;

    if (columnaEstado === 0) {
      throw new Error('No existe la columna ESTADO en la pestaÃ±a de contenidos.');
    }

    /**
     * Cada circuito selecciona como mÃ¡ximo una fila.
     * Festival es un tipo propio cuando representa la ficha evergreen estable.
     * Una noticia sobre un festival sigue siendo TIPO=Noticia + categorÃ­a Festivales.
     */
    const circuitos = [
      {
        nombre: 'Evergreen',
        tipos: ['evergreen'],
        procesar: procesarEvergreenMFA_,
      },
      {
        nombre: 'Evento',
        tipos: ['evento'],
        procesar: procesarEventoMFA_,
      },
      {
        nombre: 'Noticia',
        tipos: ['noticia'],
        procesar: procesarNoticiaMFA_,
      },
      {
        nombre: 'Festival',
        tipos: ['festival'],
        procesar: procesarFestivalMFA_,
      },
      {
        nombre: 'Artista',
        tipos: ['artista'],
        procesar: procesarArtistaMFA_,
      },
      {
        nombre: 'Receta',
        tipos: ['receta'],
        procesar: procesarRecetaMFA_,
      },
      {
        nombre: 'Mito',
        tipos: ['mito'],
        procesar: procesarMitoMFA_,
      },
    ];

    circuitos.forEach((circuito) => {
      // Busca la primera fila elegible del tipo correspondiente.
      const candidato = seleccionarCandidatoMFA_(tabla.rows, circuito.tipos);

      if (!candidato) {
        resumen.push(`${circuito.nombre}: SIN_PENDIENTES`);
        return;
      }

      try {
        // Permite que cada procesador registre en quÃ© etapa se encuentra la fila.
        const etapa = (nombre) =>
          marcarEtapaFilaMFA_(
            sheet,
            tabla.headers,
            candidato.rowNumber,
            nombre
          );

        // Ejecuta el procesador específico del tipo editorial seleccionado.
        const resultado = circuito.procesar(
          candidato.values,
          token,
          etapa
        );

        // Guarda los datos tÃ©cnicos devueltos por el procesador:
        // ENVIAR_API, ID_WEB, RESULTADO_API, ERROR_API y FECHA_ENVIO_API.
        aplicarResultadoFilaMFA_(
          sheet,
          tabla.headers,
          candidato.rowNumber,
          resultado
        );

        /**
         * PUBLICADO en esta bandeja significa "envÃ­o API completado".
         * Puede corresponder a una creaciÃ³n HTTP 201 o a una actualizaciÃ³n HTTP 200.
         * El estado editorial real dentro de Laravel se administra por separado.
         */
        const enviadoCorrectamente =
          ['CREADO_DRAFT', 'CREADO_PUBLICADO', 'ACTUALIZADO_API'].includes(resultado.resultado) &&
          Number(resultado.http) >= 200 &&
          Number(resultado.http) < 300;

        if (enviadoCorrectamente) {
          sheet
            .getRange(candidato.rowNumber, columnaEstado)
            .setValue('PUBLICADO');
          exitos++;
        }

        // Un duplicado confirmado queda cerrado y no debe volver a seleccionarse.
        if (resultado.resultado === 'DUPLICADO_API') {
          sheet
            .getRange(candidato.rowNumber, columnaEstado)
            .setValue('DESCARTADO');
          duplicados++;
        }

        resumen.push(
          `${circuito.nombre}: ${candidato.values.ID_CONTENIDO} â€” ` +
          `${resultado.resultado}` +
          `${resultado.http ? ` (HTTP ${resultado.http})` : ''}`
        );
      } catch (error) {
        errores++;

        // Convierte cualquier excepciÃ³n en el formato comÃºn de errores de la integraciÃ³n.
        const normalizado = normalizarErrorApiMFA_(error);

        // Conserva ESTADO = BORRADOR y registra el detalle tÃ©cnico del rechazo.
        aplicarResultadoFilaMFA_(
          sheet,
          tabla.headers,
          candidato.rowNumber,
          normalizado
        );

        resumen.push(
          `${circuito.nombre}: ${candidato.values.ID_CONTENIDO} â€” ` +
          `${normalizado.resultado}` +
          `${normalizado.http ? ` (HTTP ${normalizado.http})` : ''}`
        );

        // Credenciales, permisos o conectividad general pueden bloquear todos los circuitos.
        if (normalizado.detenerTodo) {
          throw error;
        }
      }
    });

    // Calcula el estado global de la ejecuciÃ³n completa.
    estadoGlobal = errores
      ? (exitos || duplicados ? 'PARCIAL' : 'ERROR')
      : (exitos || duplicados ? 'OK' : 'SIN_PENDIENTES');

    // Deja una Ãºnica fila de auditorÃ­a con el resultado de los tres circuitos.
    registrarEjecucionCargaMFA_(
      ss,
      inicio,
      new Date(),
      estadoGlobal,
      errores,
      duplicados,
      resumen
    );
  } catch (error) {
    // Maneja errores generales que impiden completar la ejecuciÃ³n.
    const normalizado = normalizarErrorApiMFA_(error);
    estadoGlobal = normalizado.detenerTodo ? 'BLOQUEADO' : 'ERROR';

    resumen.push(
      `Bloqueo general: ${normalizado.resultado} â€” ${normalizado.mensaje}`
    );

    // Intenta registrar el bloqueo incluso si el proceso principal fallÃ³.
    try {
      registrarEjecucionCargaMFA_(
        abrirPlanilla_(),
        inicio,
        new Date(),
        estadoGlobal,
        Math.max(errores, 1),
        duplicados,
        resumen
      );
    } catch (logError) {
      console.error('No se pudo registrar la ejecuciÃ³n', logError);
    }

    // Mantiene el error visible en el historial de ejecuciones de Apps Script.
    throw error;
  } finally {
    // Libera siempre el bloqueo, tanto si la carga terminÃ³ bien como si fallÃ³.
    lock.releaseLock();
  }
}

function procesarEvergreenMFA_(row, token, etapa) {
  exigirCamposMFA_(row, ['ID_CONTENIDO', 'TITULO', 'SLUG', 'CUERPO']);

  if (!row.KNOWLEDGE_CATEGORY_ID && !row.KNOWLEDGE_CATEGORY_SLUG && !row.KNOWLEDGE_CATEGORY) {
    throw crearErrorMFA_(
      'BLOQUEADO_CATEGORIA',
      422,
      'Falta KNOWLEDGE_CATEGORY_ID, KNOWLEDGE_CATEGORY_SLUG o KNOWLEDGE_CATEGORY.'
    );
  }

  validarLongitudMFA_(row);
  validarContenidoVisibleMFA_(row.CUERPO, 900);

  etapa('PROCESANDO_CATEGORIAS');

  const categorias = apiMFA_('get', '/knowledge-categories', token);
  const categoria = buscarCategoriaMFA_(
    categorias.json,
    row.KNOWLEDGE_CATEGORY,
    row.KNOWLEDGE_CATEGORY_ID,
    row.KNOWLEDGE_CATEGORY_SLUG
  );

  if (!categoria) {
    const disponibles = extraerColeccionMFA_(categorias.json)
      .map((item) => `${item.id || '?'}:${item.slug || item.name || item.title || item.nombre || '?'}`)
      .slice(0, 30)
      .join(', ');

    throw crearErrorMFA_(
      'BLOQUEADO_CATEGORIA',
      422,
      `No se encontró la categoría evergreen (id=${row.KNOWLEDGE_CATEGORY_ID || '-'}, slug=${row.KNOWLEDGE_CATEGORY_SLUG || '-'}, nombre=${row.KNOWLEDGE_CATEGORY || '-'}). Disponibles: ${disponibles || 'ninguna'}`
    );
  }

  etapa(`CATEGORIA_RESUELTA_${categoria.id}`);

  /*
   * Desde ahora los Evergreen se publican automáticamente.
   * Por eso el control de duplicados debe contemplar tanto borradores
   * como contenidos ya publicados.
   */
  etapa('PROCESANDO_DUPLICADOS');

  const existentesDraft = apiMFA_(
    'get',
    '/knowledge-articles',
    token,
    null,
    { editorial_status: 'draft', per_page: 100 }
  );

  let duplicado = buscarPorSlugMFA_(existentesDraft.json, row.SLUG);

  if (!duplicado) {
    const existentesPublished = apiMFA_(
      'get',
      '/knowledge-articles',
      token,
      null,
      { editorial_status: 'published', per_page: 100 }
    );

    duplicado = buscarPorSlugMFA_(existentesPublished.json, row.SLUG);
  }

  if (duplicado) {
    return {
      resultado: 'DUPLICADO_API',
      http: 200,
      id: extraerIdMFA_(duplicado),
      error: '',
      desautorizar: true
    };
  }

  /*
   * Primero se crea el artículo usando el flujo estable que ya estaba validado.
   * Después se invoca explícitamente el endpoint /publish para que Laravel
   * ejecute toda su lógica de publicación y complete published_at.
   */
  const payload = limpiarObjetoMFA_({
    knowledge_category_id: Number(categoria.id),
    title: row.TITULO,
    slug: row.SLUG,
    body: row.CUERPO,
    excerpt: limitarTextoMFA_(row.BAJADA, 1000),
    editorial_status: 'draft',
    published_at: null,
    seo_title: row.META_TITLE,
    meta_description: limitarTextoMFA_(row.META_DESCRIPTION, 320),
    primary_keyword: primeraPalabraClaveMFA_(row.PALABRAS_CLAVE),
    secondary_keywords: limitarTextoMFA_(row.PALABRAS_CLAVE, 1000),
    featured_image_path: row.FEATURED_IMAGE_PATH,
    featured_image_url: urlOpcionalMFA_(row.FEATURED_IMAGE_URL),
    image_alt: row.IMAGE_ALT,
    last_verified_at: Utilities.formatDate(new Date(), MFA_CONFIG.TIME_ZONE, 'yyyy-MM-dd'),
  }, ['published_at']);

  etapa('PROCESANDO_POST');

  const response = apiMFA_('post', '/knowledge-articles', token, payload);

  if (response.status !== 201) {
    throw errorDesdeRespuestaMFA_(response);
  }

  const id = extraerIdMFA_(response.json);

  if (!id) {
    throw crearErrorMFA_(
      'ERROR_RESPUESTA',
      response.status,
      'La API respondió 201 pero no devolvió un ID reconocible.'
    );
  }

  etapa('PROCESANDO_PUBLICACION');

  const publicacion = apiMFA_(
    'post',
    `/knowledge-articles/${id}/publish`,
    token,
    {}
  );

  return {
    resultado: 'CREADO_PUBLICADO',
    http: publicacion.status,
    id,
    error: '',
    desautorizar: true,
    fechaEnvio: new Date(),
    knowledgeCategoryId: Number(categoria.id)
  };
}

function procesarFestivalMFA_(row, token, etapa) {
  exigirCamposMFA_(row, ['ID_CONTENIDO', 'TITULO', 'SLUG', 'CUERPO', 'PROVINCE_ID', 'MES_ID']);
  validarLongitudMFA_(row);
  validarContenidoVisibleMFA_(row.CUERPO, 300);

  const accion = normalizarTextoMFA_(row.ACCION_API) || 'crear';
  if (!['crear', 'actualizar'].includes(accion)) {
    throw crearErrorMFA_(
      'ERROR_VALIDACION',
      422,
      'ACCION_API debe ser CREAR o ACTUALIZAR para TIPO=Festival.'
    );
  }

  const festivalId = enteroOpcionalMFA_(row.ID_WEB);
  if (accion === 'actualizar' && !festivalId) {
    throw crearErrorMFA_(
      'ERROR_VALIDACION',
      422,
      'Un Festival con ACCION_API=ACTUALIZAR requiere ID_WEB.'
    );
  }
  if (accion === 'crear' && festivalId) {
    throw crearErrorMFA_(
      'ERROR_VALIDACION',
      422,
      'Un Festival con ACCION_API=CREAR no debe tener ID_WEB.'
    );
  }

  const payload = limpiarObjetoMFA_({
    title: String(row.TITULO || '').trim(),
    slug: String(row.SLUG || '').trim(),
    excerpt: limitarTextoMFA_(row.BAJADA, 1000),
    body: row.CUERPO,
    province_id: enteroOpcionalMFA_(row.PROVINCE_ID),
    locality_id: enteroOpcionalMFA_(row.LOCALITY_ID),
    mes_id: enteroOpcionalMFA_(row.MES_ID),
    seo_title: row.META_TITLE,
    meta_description: limitarTextoMFA_(row.META_DESCRIPTION, 320),
    image_alt: limitarTextoMFA_(row.IMAGE_ALT, 255),
    featured_image_path: row.FEATURED_IMAGE_PATH,
    featured_image_url: urlOpcionalMFA_(row.FEATURED_IMAGE_URL),
    news_ids: listaEnterosMFA_(row.NEWS_IDS),
    event_ids: listaEnterosMFA_(row.EVENT_IDS),
    interprete_ids: listaEnterosMFA_(row.INTERPRETE_IDS),
    knowledge_article_ids: listaEnterosMFA_(row.KNOWLEDGE_ARTICLE_IDS),
    status: accion === 'crear' ? 'draft' : null,
  });

  const method = accion === 'actualizar' ? 'put' : 'post';
  const apiPath = accion === 'actualizar'
    ? `/festivals/${festivalId}`
    : '/festivals';

  etapa(accion === 'actualizar' ? 'PROCESANDO_UPDATE' : 'PROCESANDO_POST');
  console.log(
    `MFA FESTIVAL DEBUG: fila=${row.ID_CONTENIDO || '-'} accion=${accion.toUpperCase()} payload=${JSON.stringify(payload)}`
  );

  const response = apiMFA_(method, apiPath, token, payload);
  const expected = accion === 'actualizar' ? 200 : 201;

  if (response.status !== expected) {
    throw errorDesdeRespuestaMFA_(response);
  }

  const id = extraerIdMFA_(response.json) || festivalId;
  if (!id) {
    throw crearErrorMFA_(
      'ERROR_RESPUESTA',
      response.status,
      'La API respondió correctamente pero no devolvió un ID reconocible.'
    );
  }

  return {
    resultado: accion === 'actualizar' ? 'ACTUALIZADO_API' : 'CREADO_DRAFT',
    http: response.status,
    id,
    error: '',
    desautorizar: true,
    fechaEnvio: new Date()
  };
}

function procesarEventoMFA_(row, token, etapa) {
  exigirCamposMFA_(row, ['ID_CONTENIDO', 'TITULO', 'SLUG', 'START_AT']);
  validarLongitudMFA_(row);

  /*
   * Las fechas pueden llegar desde Google Sheets como objetos Date.
   * Las normalizamos explícitamente al formato que consume Laravel
   * para evitar conversiones implícitas a UTC durante JSON.stringify().
   */
  const start = normalizarFechaEventoMFA_(row.START_AT);

  if (!start) {
    throw crearErrorMFA_(
      'ERROR_VALIDACION',
      422,
      'START_AT no es una fecha válida.'
    );
  }

  const end = row.END_AT
    ? normalizarFechaEventoMFA_(row.END_AT)
    : null;

  if (row.END_AT && !end) {
    throw crearErrorMFA_(
      'ERROR_VALIDACION',
      422,
      'END_AT no es una fecha válida.'
    );
  }

  if (end && new Date(end.replace(' ', 'T')) <= new Date(start.replace(' ', 'T'))) {
    throw crearErrorMFA_(
      'ERROR_VALIDACION',
      422,
      'END_AT debe ser posterior a START_AT.'
    );
  }

  etapa('PROCESANDO_DUPLICADOS');

  const existentes = apiMFA_(
    'get',
    '/events',
    token,
    null,
    { editorial_status: 'draft', per_page: 100 }
  );

  const duplicado = buscarPorSlugMFA_(existentes.json, row.SLUG);

  if (duplicado) {
    return {
      resultado: 'DUPLICADO_API',
      http: existentes.status,
      id: extraerIdMFA_(duplicado),
      error: '',
      desautorizar: true
    };
  }

  /*
   * Payload conservador para Events.
   *
   * Se mantienen los campos editoriales y de ubicación que la API reconoce.
   * EVENT_TYPE y MODALITY sólo se incluyen si existen en la hoja.
   *
   * FEATURED_IMAGE_URL se envía como featured_image_url cuando contiene una URL válida.
   * La API moderna se encarga de descargarla e incorporarla al sistema de media_assets.
   */
  const payload = limpiarObjetoMFA_({
    title: String(row.TITULO || '').trim(),
    slug: String(row.SLUG || '').trim(),
    excerpt: limitarTextoMFA_(row.BAJADA, 1000),
    body: row.CUERPO,

    start_at: start,
    end_at: end,

    event_type: row.EVENT_TYPE,
    modality: row.MODALITY,
    timezone: row.TIMEZONE || MFA_CONFIG.TIME_ZONE,

    province_id: enteroOpcionalMFA_(row.PROVINCE_ID),
    city: row.LOCALIDAD,
    address: row.DIRECCION,

    ticket_url: urlOpcionalMFA_(row.TICKET_URL),
    price_text: row.PRICE_TEXT,
    is_free: booleanoOpcionalMFA_(row.IS_FREE),

    interprete_id: enteroOpcionalMFA_(row.INTERPRETE_ID),

    editorial_status: 'draft',

    seo_title: row.META_TITLE,
    meta_description: limitarTextoMFA_(row.META_DESCRIPTION, 320),
    featured_image_url: urlOpcionalMFA_(row.FEATURED_IMAGE_URL),
  }, ['is_free']);

  /*
   * DEBUG TEMPORAL/OPERATIVO:
   * deja visible exactamente qué JSON se envía a Laravel.
   * No contiene el Bearer Token.
   */
  console.log(
    `MFA EVENT DEBUG: fila=${row.ID_CONTENIDO || '-'} payload=${JSON.stringify(payload)}`
  );

  etapa('PROCESANDO_POST');

  const response = apiMFA_('post', '/events', token, payload);

  if (response.status !== 201) {
    throw errorDesdeRespuestaMFA_(response);
  }

  const id = extraerIdMFA_(response.json);

  if (!id) {
    throw crearErrorMFA_(
      'ERROR_RESPUESTA',
      response.status,
      'La API respondió 201 pero no devolvió un ID reconocible.'
    );
  }

  return {
    resultado: 'CREADO_DRAFT',
    http: response.status,
    id,
    error: '',
    desautorizar: true,
    fechaEnvio: new Date()
  };
}

/**
 * Normaliza fechas provenientes del Sheet al formato local esperado por Laravel.
 * Devuelve null cuando el valor no puede interpretarse como fecha.
 */
function normalizarFechaEventoMFA_(valor) {
  if (valor === '' || valor === null || valor === undefined) {
    return null;
  }

  let fecha;

  if (Object.prototype.toString.call(valor) === '[object Date]') {
    fecha = valor;
  } else {
    fecha = new Date(valor);
  }

  if (Number.isNaN(fecha.getTime())) {
    return null;
  }

  return Utilities.formatDate(
    fecha,
    MFA_CONFIG.TIME_ZONE,
    'yyyy-MM-dd HH:mm:ss'
  );
}

/**
 * Obtiene el ID que la API necesita para crear una noticia.
 *
 * Orden de resoluciÃ³n:
 * 1. Si CATEGORIA_ID ya contiene uno de los cinco IDs vÃ¡lidos, lo conserva.
 * 2. Si el ID estÃ¡ vacÃ­o, lo deduce desde CATEGORIA_CONTENIDO.
 * 3. Si ninguno de los dos valores es vÃ¡lido, detiene la fila con un mensaje claro.
 */
function resolverCategoriaNoticiaMFA_(row) {
  const categoriaIdInformada = enteroOpcionalMFA_(row.CATEGORIA_ID);
  const idsValidos = [...new Set(Object.values(MFA_CATEGORIAS_NOTICIA))];

  if (categoriaIdInformada) {
    if (!idsValidos.includes(categoriaIdInformada)) {
      throw crearErrorMFA_(
        'BLOQUEADO_CATEGORIA',
        422,
        `CATEGORIA_ID=${categoriaIdInformada} no corresponde a una categorÃ­a de Noticias vÃ¡lida. IDs admitidos: ${idsValidos.join(', ')}.`
      );
    }
    return categoriaIdInformada;
  }

  const categoriaNombre = normalizarTextoMFA_(row.CATEGORIA_CONTENIDO);
  const categoriaIdResuelta = MFA_CATEGORIAS_NOTICIA[categoriaNombre] || null;

  if (!categoriaIdResuelta) {
    throw crearErrorMFA_(
      'BLOQUEADO_CATEGORIA',
      422,
      `No se pudo resolver la categorÃ­a de Noticias. CATEGORIA_CONTENIDO="${row.CATEGORIA_CONTENIDO || ''}" y CATEGORIA_ID="${row.CATEGORIA_ID || ''}". Valores admitidos: Actualidad, Festivales, Lanzamientos, Entrevistas o Cartelera.`
    );
  }

  return categoriaIdResuelta;
}

function procesarNoticiaMFA_(row, token, etapa) {
  // CATEGORIA_ID no se exige aquÃ­ porque puede calcularse desde CATEGORIA_CONTENIDO.
  exigirCamposMFA_(row, ['ID_CONTENIDO', 'TITULO', 'SLUG', 'CUERPO']);
  validarLongitudMFA_(row);

  // Festival y Lanzamiento ahora son categorÃ­as; todas las filas llegan como Noticia.
  validarContenidoVisibleMFA_(row.CUERPO, 400);

  // Usa el ID de la hoja o lo deduce automÃ¡ticamente desde el nombre de categorÃ­a.
  const categoriaId = resolverCategoriaNoticiaMFA_(row);

  etapa('PROCESANDO_DUPLICADOS');
  const existentes = apiMFA_('get', '/news', token, null, { categoria_id: categoriaId, editorial_status: 'draft', per_page: 100 });
  const duplicado = buscarPorSlugMFA_(existentes.json, row.SLUG);
  if (duplicado) return { resultado: 'DUPLICADO_API', http: existentes.status, id: extraerIdMFA_(duplicado), error: '', desautorizar: true, categoriaId };

  const payload = limpiarObjetoMFA_({
    title: row.TITULO,
    slug: row.SLUG,
    excerpt: limitarTextoMFA_(row.BAJADA, 1000),
    body: row.CUERPO,
    categoria_id: categoriaId,
    interprete_id: enteroOpcionalMFA_(row.INTERPRETE_ID),
    editorial_status: 'draft',
    published_at: null,
    seo_title: row.META_TITLE,
    meta_description: limitarTextoMFA_(row.META_DESCRIPTION, 320),
    featured_image_path: row.FEATURED_IMAGE_PATH,
    featured_image_url: urlOpcionalMFA_(row.FEATURED_IMAGE_URL),
  }, ['published_at']);
  etapa('PROCESANDO_POST');
  const response = apiMFA_('post', '/news', token, payload);
  if (response.status !== 201) throw errorDesdeRespuestaMFA_(response);
  const id = extraerIdMFA_(response.json);
  if (!id) throw crearErrorMFA_('ERROR_RESPUESTA', response.status, 'La API respondiÃ³ 201 pero no devolviÃ³ un ID reconocible.');
  return { resultado: 'CREADO_DRAFT', http: response.status, id, error: '', desautorizar: true, fechaEnvio: new Date(), categoriaId };
}

function instalarTriggerCargaContenidos_() {
  ScriptApp.getProjectTriggers()
    .filter((t) => t.getHandlerFunction() === 'cargarContenidosMFA')
    .forEach((t) => ScriptApp.deleteTrigger(t));
  ScriptApp.newTrigger('cargarContenidosMFA')
    .timeBased()
    .atHour(MFA_CONFIG.CONTENT_TRIGGER_HOUR)
    .everyDays(1)
    .inTimezone(MFA_CONFIG.TIME_ZONE)
    .create();
}

/** Prueba Ãºnicamente autenticaciÃ³n y lectura de categorÃ­as; nunca crea contenido. */
function probarConexionApiMFA() {
  const inicio = new Date();
  const token = obtenerTokenApi_();
  console.log('MFA API: iniciando GET /knowledge-categories');
  const response = apiMFA_('get', '/knowledge-categories', token);
  const cantidad = extraerColeccionMFA_(response.json).length;
  console.log(`MFA API: categorÃ­as recibidas=${cantidad}, HTTP=${response.status}`);
  registrarEjecucionCargaMFA_(abrirPlanilla_(), inicio, new Date(), 'OK', 0, 0, [`Prueba conexiÃ³n: HTTP ${response.status}; categorÃ­as=${cantidad}`]);
  return { http: response.status, categorias: cantidad };
}

/**
 * Prueba autenticaciÃ³n y lectura de Noticias sin crear ni modificar contenido.
 * Devuelve las categorÃ­as observables en la respuesta para ayudar a validar
 * CATEGORIA_ID antes de habilitar una fila de la bandeja.
 */
function probarConexionNoticiasMFA() {
  const inicio = new Date();
  const token = obtenerTokenApi_();
  const response = apiMFA_('get', '/news', token, null, { per_page: 100 });
  const categorias = {};

  extraerColeccionMFA_(response.json).forEach((noticia) => {
    const categoria = noticia.categoria || noticia.category || {};
    const id = enteroOpcionalMFA_(noticia.categoria_id || categoria.id);
    if (!id) return;
    const nombre = categoria.nombre || categoria.name || categoria.title || '';
    categorias[id] = nombre || categorias[id] || 'Nombre no incluido por la API';
  });

  const catalogo = Object.keys(categorias)
    .sort((a, b) => Number(a) - Number(b))
    .map((id) => `${id}:${categorias[id]}`)
    .join(', ');
  const detalle = catalogo || 'La API no incluyÃ³ categorÃ­as observables en las primeras 100 noticias.';

  console.log(`MFA API Noticias: HTTP=${response.status}; categorÃ­as=${detalle}`);
  registrarEjecucionCargaMFA_(
    abrirPlanilla_(), inicio, new Date(), 'OK', 0, 0,
    [`Prueba Noticias: HTTP ${response.status}; categorÃ­as=${detalle}`],
  );
  return { http: response.status, categorias };
}

function obtenerTokenApi_() {
  const token = String(PropertiesService.getScriptProperties().getProperty(MFA_CONFIG.API_TOKEN_PROPERTY) || '').trim();
  if (!token) throw crearErrorMFA_('BLOQUEADO_CREDENCIAL', 401, `Falta Script Property ${MFA_CONFIG.API_TOKEN_PROPERTY}.`, true);
  return token.replace(/^Bearer\s+/i, '');
}

function leerTablaConEncabezados_(sheet) {
  const values = sheet.getDataRange().getValues();
  if (!values.length) throw new Error(`La pestaÃ±a ${sheet.getName()} estÃ¡ vacÃ­a.`);
  const headers = values[0].map((h) => String(h || '').trim());
  const rows = values.slice(1).map((row, index) => {
    const object = {};
    headers.forEach((header, col) => { if (header) object[header] = row[col]; });
    return { rowNumber: index + 2, values: object };
  });
  return { headers, rows };
}

function seleccionarCandidatoMFA_(rows, tipos) {
  const prioridad = { alta: 3, media: 2, baja: 1 };
  const estadosExcluidos = ['publicado', 'descartado', 'duplicado'];

  return rows
    .filter((r) => tipos.includes(normalizarTextoMFA_(r.values.TIPO)))
    .filter((r) => !estadosExcluidos.includes(normalizarTextoMFA_(r.values.ESTADO)))
    .filter((r) => normalizarTextoMFA_(r.values.ENVIAR_API) === 's')
    .filter((r) => {
      const resultado = normalizarTextoMFA_(r.values.RESULTADO_API);
      const tipo = normalizarTextoMFA_(r.values.TIPO);
      const accion = normalizarTextoMFA_(r.values.ACCION_API) || 'crear';

      if (tipo === 'festival' && accion === 'actualizar') {
        return Boolean(enteroOpcionalMFA_(r.values.ID_WEB)) &&
          !['actualizado_api'].includes(resultado);
      }

      return !r.values.ID_WEB &&
        !r.values.URL_PUBLICADA &&
        !['creado_draft', 'creado_publicado'].includes(resultado);
    })
    .sort((a, b) =>
      (prioridad[normalizarTextoMFA_(b.values.PRIORIDAD)] || 0) -
      (prioridad[normalizarTextoMFA_(a.values.PRIORIDAD)] || 0) ||
      numero_(a.values.SCORE_CALIDAD || 999) - numero_(b.values.SCORE_CALIDAD || 999) ||
      a.rowNumber - b.rowNumber
    )[0] || null;
}

function apiMFA_(method, path, token, payload, query) {
  const qs = query ? '?' + Object.keys(query).filter((k) => query[k] !== '' && query[k] !== null && query[k] !== undefined).map((k) => `${encodeURIComponent(k)}=${encodeURIComponent(query[k])}`).join('&') : '';
  const url = `${MFA_CONFIG.API_BASE_URL}${path}${qs}`;
  console.log(`MFA API: ${String(method).toUpperCase()} ${path} iniciado`);
  const startedAt = Date.now();
  const response = UrlFetchApp.fetch(url, {
    method,
    headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
    contentType: 'application/json',
    payload: payload === null || payload === undefined ? undefined : JSON.stringify(payload),
    muteHttpExceptions: true,
    followRedirects: false,
  });
  const status = response.getResponseCode();
  console.log(`MFA API: ${String(method).toUpperCase()} ${path} finalizado HTTP=${status} ms=${Date.now() - startedAt}`);
  const text = response.getContentText();
  let json = null;
  try { json = text ? JSON.parse(text) : null; } catch (ignore) { json = null; }

  const result = { status, text, json };

  /*
   * DEBUG HTTP:
   * En cualquier respuesta no exitosa deja el body de Laravel en el log.
   * Esto es especialmente útil para los HTTP 500 de /events.
   * Se limita a 3000 caracteres para no ensuciar el historial de Apps Script.
   */
  if (status < 200 || status >= 300) {
    console.error(
      `MFA API ERROR: ${String(method).toUpperCase()} ${path} HTTP=${status} body=${String(text || '').slice(0, 3000)}`
    );
  }

  if (status === 401) throw crearErrorMFA_('BLOQUEADO_CREDENCIAL', status, mensajeRespuestaMFA_(result), true);
  if (status === 403) throw crearErrorMFA_('BLOQUEADO_PERMISOS', status, mensajeRespuestaMFA_(result), true);
  if (status >= 500) throw crearErrorMFA_('ERROR_TRANSITORIO', status, mensajeRespuestaMFA_(result));
  if (status < 200 || status >= 300) throw errorDesdeRespuestaMFA_(result);
  return result;
}

function aplicarResultadoFilaMFA_(sheet, headers, rowNumber, result) {
  const updates = {
    ID_WEB: result.id || '',
    RESULTADO_API: result.resultado || 'ERROR',
    ERROR_API: result.error || result.mensaje || '',
  };
  if (result.desautorizar) updates.ENVIAR_API = 'N';
  if (result.fechaEnvio) updates.FECHA_ENVIO_API = result.fechaEnvio;
  // Si una Noticia resolviÃ³ la categorÃ­a desde su nombre, persiste el ID en el Excel.
  if (result.categoriaId) updates.CATEGORIA_ID = result.categoriaId;
  if (result.knowledgeCategoryId) updates.KNOWLEDGE_CATEGORY_ID = result.knowledgeCategoryId;
  Object.keys(updates).forEach((name) => {
    const col = headers.indexOf(name);
    if (col >= 0) sheet.getRange(rowNumber, col + 1).setValue(updates[name]);
  });
}

function marcarEtapaFilaMFA_(sheet, headers, rowNumber, etapa) {
  const resultCol = headers.indexOf('RESULTADO_API');
  const errorCol = headers.indexOf('ERROR_API');
  if (resultCol >= 0) sheet.getRange(rowNumber, resultCol + 1).setValue(etapa);
  if (errorCol >= 0) sheet.getRange(rowNumber, errorCol + 1).setValue('');
  SpreadsheetApp.flush();
  console.log(`MFA carga: fila=${rowNumber}, etapa=${etapa}`);
}

function registrarEjecucionCargaMFA_(ss, inicio, fin, estado, errores, duplicados, resumen) {
  const sheet = ss.getSheetByName(MFA_CONFIG.SHEETS.EXECUTIONS);
  if (!sheet) return;
  const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0].map(String);
  const data = {
    ID_EJECUCION: `EJ-CARGA-${Utilities.formatDate(inicio, MFA_CONFIG.TIME_ZONE, 'yyyyMMdd-HHmmss')}`,
    FECHA_HORA_INICIO: inicio,
    FECHA_HORA_FIN: fin,
    ESTADO_EJECUCION: estado,
    FUENTES_REVISADAS: 0,
    HALLAZGOS: 0,
    CONTENIDOS_GENERADOS: resumen.filter(
      (x) =>
        x.indexOf('CREADO_DRAFT') >= 0 ||
        x.indexOf('CREADO_PUBLICADO') >= 0 ||
        x.indexOf('ACTUALIZADO_API') >= 0
    ).length,
    DUPLICADOS_DESCARTADOS: duplicados,
    ERRORES: errores,
    OBSERVACIONES: `Carga API: ${resumen.join(' | ')}`,
  };
  sheet.appendRow(headers.map((header) => Object.prototype.hasOwnProperty.call(data, header) ? data[header] : ''));
}

function buscarCategoriaMFA_(json, nombre, id, slugEstable) {
  const items = extraerColeccionMFA_(json);
  const idNumber = enteroOpcionalMFA_(id);
  if (idNumber) return items.find((x) => Number(x.id) === idNumber) || null;
  const stableSlug = normalizarTextoMFA_(slugEstable);
  if (stableSlug) {
    const bySlug = items.find((x) => normalizarTextoMFA_(x.slug) === stableSlug);
    if (bySlug) return bySlug;
  }
  const needle = normalizarTextoMFA_(nombre);
  const slug = slugMFA_(nombre);
  return items.find((x) => normalizarTextoMFA_(x.name || x.title || x.nombre) === needle || normalizarTextoMFA_(x.slug) === slug) || null;
}

function buscarPorSlugMFA_(json, slug) {
  const needle = normalizarTextoMFA_(slug);
  return extraerColeccionMFA_(json).find((x) => normalizarTextoMFA_(x.slug) === needle) || null;
}

function extraerColeccionMFA_(json) {
  if (Array.isArray(json)) return json;
  if (!json || typeof json !== 'object') return [];
  if (Array.isArray(json.data)) return json.data;
  if (json.data && Array.isArray(json.data.data)) return json.data.data;
  if (Array.isArray(json.items)) return json.items;
  if (Array.isArray(json.results)) return json.results;
  return [];
}

function extraerIdMFA_(json) {
  if (!json) return '';
  if (json.id !== undefined && json.id !== null) return json.id;
  if (json.data) return extraerIdMFA_(json.data);
  if (json.article) return extraerIdMFA_(json.article);
  if (json.event) return extraerIdMFA_(json.event);
  if (json.news) return extraerIdMFA_(json.news);
  return '';
}

function exigirCamposMFA_(row, fields) {
  const missing = fields.filter((field) => row[field] === '' || row[field] === null || row[field] === undefined);
  if (missing.length) throw crearErrorMFA_('ERROR_VALIDACION', 422, `Faltan campos obligatorios: ${missing.join(', ')}`);
}

function validarLongitudMFA_(row) {
  ['TITULO', 'SLUG'].forEach((field) => {
    if (String(row[field] || '').length > 255) throw crearErrorMFA_('ERROR_VALIDACION', 422, `${field} supera 255 caracteres.`);
  });
}

function validarContenidoVisibleMFA_(html, minimoPalabras) {
  const visible = String(html || '')
    .replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, ' ')
    .replace(/<style\b[^>]*>[\s\S]*?<\/style>/gi, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;|&#160;/gi, ' ')
    .replace(/&amp;/gi, '&')
    .replace(/&quot;|&#34;/gi, '"')
    .replace(/&#39;|&apos;/gi, "'")
    .replace(/\s+/g, ' ')
    .trim();
  const palabras = visible ? visible.split(/\s+/).length : 0;
  if (palabras < minimoPalabras) {
    throw crearErrorMFA_('ERROR_VALIDACION', 422, `El cuerpo tiene ${palabras} palabras visibles; el mÃ­nimo es ${minimoPalabras}.`);
  }
  return palabras;
}

function errorDesdeRespuestaMFA_(response) {
  if (response.status === 409) return crearErrorMFA_('DUPLICADO_API', response.status, mensajeRespuestaMFA_(response));
  if (response.status === 422) return crearErrorMFA_('ERROR_VALIDACION', response.status, mensajeRespuestaMFA_(response));
  return crearErrorMFA_('ERROR_API', response.status, mensajeRespuestaMFA_(response));
}

function crearErrorMFA_(resultado, http, mensaje, detenerTodo) {
  const error = new Error(mensaje || resultado);
  error.mfa = { resultado, http: http || '', mensaje: mensaje || resultado, detenerTodo: Boolean(detenerTodo) };
  return error;
}

function normalizarErrorApiMFA_(error) {
  if (error && error.mfa) {
    const http = Number(error.mfa.http || 0);
    return {
      resultado: error.mfa.resultado,
      http: error.mfa.http,
      mensaje: limitarTextoMFA_(error.mfa.mensaje, 1000),
      error: limitarTextoMFA_(error.mfa.mensaje, 1000),
      detenerTodo: error.mfa.detenerTodo,
      desautorizar: [400, 401, 403, 404, 409, 422].includes(http),
    };
  }
  const message = String(error && error.message ? error.message : error || 'Error desconocido');
  return { resultado: 'ERROR', http: '', mensaje: limitarTextoMFA_(message, 1000), error: limitarTextoMFA_(message, 1000), detenerTodo: false };
}

function mensajeRespuestaMFA_(response) {
  if (response.json) {
    if (response.json.message) return String(response.json.message);
    if (response.json.errors) return JSON.stringify(response.json.errors).slice(0, 1000);
  }
  return String(response.text || `HTTP ${response.status}`).slice(0, 1000);
}

function limpiarObjetoMFA_(object, conservarNulos) {
  const keepNull = conservarNulos || [];
  const result = {};
  Object.keys(object).forEach((key) => {
    const value = object[key];
    if (value === null && keepNull.includes(key)) result[key] = null;
    else if (value !== '' && value !== null && value !== undefined) result[key] = value;
  });
  return result;
}

function normalizarTextoMFA_(value) {
  return String(value || '').trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function slugMFA_(value) {
  return normalizarTextoMFA_(value).replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function enteroOpcionalMFA_(value) {
  if (value === '' || value === null || value === undefined) return null;
  const number = Number(value);
  return Number.isInteger(number) && number > 0 ? number : null;
}

function listaEnterosMFA_(value) {
  if (value === '' || value === null || value === undefined) return null;

  const values = Array.isArray(value)
    ? value
    : String(value).split(/[;,\s]+/);

  const result = [...new Set(
    values
      .map((item) => enteroOpcionalMFA_(item))
      .filter((item) => item !== null)
  )];

  return result.length ? result : null;
}

function booleanoOpcionalMFA_(value) {
  const normalized = normalizarTextoMFA_(value);
  if (!normalized) return null;
  if (['si', 's', 'true', '1'].includes(normalized)) return true;
  if (['no', 'n', 'false', '0'].includes(normalized)) return false;
  return null;
}

function urlOpcionalMFA_(value) {
  const text = String(value || '').trim();
  if (!text) return '';
  if (!/^https?:\/\/[^\s]+$/i.test(text)) throw crearErrorMFA_('ERROR_VALIDACION', 422, `URL invÃ¡lida: ${text}`);
  return text;
}

function limitarTextoMFA_(value, max) {
  const text = String(value || '');
  return text.length > max ? text.slice(0, max) : text;
}

function primeraPalabraClaveMFA_(value) {
  return String(value || '').split(',')[0].trim();
}

function abrirPlanilla_() {
  return SpreadsheetApp.openById(MFA_CONFIG.SPREADSHEET_ID);
}

function fechaDiasAtras_(days) {
  const date = new Date();
  date.setDate(date.getDate() - days);
  return Utilities.formatDate(date, MFA_CONFIG.TIME_ZONE, 'yyyy-MM-dd');
}

function urlAbsoluta_(path) {
  if (!path) return MFA_CONFIG.DOMINIO_PRINCIPAL + '/';
  if (/^https?:\/\//i.test(path)) return path;
  return MFA_CONFIG.DOMINIO_PRINCIPAL + (path.startsWith('/') ? path : `/${path}`);
}

function numero_(value) {
  const number = Number(value || 0);
  return Number.isFinite(number) ? number : 0;
}
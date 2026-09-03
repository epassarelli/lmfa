# Piloto Content Refresh — Peñas y Radios

## Alcance

Validar seis operaciones controladas con el trigger diario deshabilitado:

1. `Peña` — `CREAR` y `ACTUALIZAR`.
2. `Radio` — `CREAR` y `ACTUALIZAR`.
3. `ProgramaRadio` — `CREAR` y `ACTUALIZAR`.

Las altas enviadas por este circuito siempre se crean como `draft/pending`. Content Refresh no publica ni verifica registros y los feature flags públicos continúan apagados.

## Preflight sin credenciales

En la exportación completa de `Contenidos`, dejar `ENVIAR_API=S` únicamente en las seis filas del piloto y ejecutar:

```bash
npm run apps-script:directory-pilot:check -- /ruta/Contenidos.csv
```

El resultado esperado es `PREFLIGHT OK (directories)`. El comando valida exactamente una alta y una actualización por tipo, `ID_WEB`, campos obligatorios, cuerpo mínimo, JSON estructurado y rutas API, sin conectarse a Google ni a Laravel.

## Columnas del contrato

Campos comunes: `ID_CONTENIDO`, `TIPO`, `ACCION_API`, `ID_WEB`, `ENVIAR_API`, `TITULO`, `SLUG`, `BAJADA`, `CUERPO`, `SOURCE_URLS`, `FEATURED_IMAGE_PATH`, `IMAGE_ALT`, `META_TITLE` y `META_DESCRIPTION`.

Peñas: `PROVINCE_ID`, `LOCALITY_ID`, `CITY`, `ADDRESS`, `LATITUDE`, `LONGITUDE`, `VENUE_TYPE`, `OPENING_HOURS_JSON`, `PHONE`, `EMAIL`, `WEB_URL`, `RESERVATION_URL`, `CAPACITY`, `ACCESSIBILITY_NOTES`, `REGULAR_EVENTS_SUMMARY`, `ADMISSION_NOTES` y `EVENT_IDS`.

Radios: `EDITORIAL_FOCUS`, `TRANSMISSION_MODES`, `PROVINCE_ID`, `LOCALITY_ID`, `CITY`, `ADDRESS`, `LATITUDE`, `LONGITUDE`, `COVERAGE_SCOPE`, `COVERAGE_NOTES`, `PHONE`, `EMAIL`, `WEB_URL` y `RADIO_CHANNELS_JSON`.

Programas: `RADIO_SIGNAL_ID`, `IS_FOLKLORE`, `RADIO_PLATFORM`, `LISTENING_URL` y `RADIO_SLOTS_JSON`.

`SOURCE_URLS` y `TRANSMISSION_MODES` aceptan un valor por línea, separados por punto y coma o un array JSON. `OPENING_HOURS_JSON` acepta objeto o array JSON. `RADIO_CHANNELS_JSON` y `RADIO_SLOTS_JSON` exigen arrays JSON de objetos. Cuando se envían canales, franjas o eventos en una actualización, la colección enviada reemplaza la relación completa; una celda vacía preserva lo existente.

## Ejecución y verificación

1. Confirmar CI verde y sincronizar Apps Script desde `dev` aprobado.
2. Deshabilitar temporalmente `MFA - Carga contenidos diario`.
3. Exportar auditorías antes del piloto:

```bash
php artisan mfa:penias:audit --csv=storage/app/audits/penias-refresh-antes.csv
php artisan mfa:radios:audit --csv=storage/app/audits/radios-refresh-antes.csv
```

4. Habilitar y ejecutar manualmente una sola fila por vez.
5. Confirmar `ESTADO=PUBLICADO` en la hoja, `ENVIAR_API=N`, `ID_WEB`, `RESULTADO_API`, fecha y error vacío. En la hoja, `PUBLICADO` significa “procesado por API”; en Laravel debe seguir como borrador pendiente.
6. Revisar el registro en backoffice. En actualizaciones, confirmar que estado, verificación, visitas y campos omitidos se preservaron.
7. Repetir ambas auditorías con sufijo `-despues` y contrastar los seis IDs.

Ante `401`, `403`, `404`, `409`, `422` o una respuesta inesperada, detener el piloto y no reautorizar la fila hasta comprender el error.

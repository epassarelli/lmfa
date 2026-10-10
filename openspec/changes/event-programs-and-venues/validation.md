# Validación y prueba local

## Corrección de listado vacío por roles combinados (2026-10-08)

Reporte del usuario: front tiene eventos, Backoffice no muestra. Ambos usan Event/tabla events en la misma conexión; front aplica publicación y fechas, Backoffice agrupación y autor según roles. Auditoría read-only: 324 Events, 317 filas unificadas sin filtros; seis administradores y uno con administrador+colaborador, cero eventos propios. El filtro por autor se aplicaba también a ese administrador.

Test de regresión agregado a UnifiedEventEntryTest reproduce el listado vacío antes del cambio. Corrección: el filtro colaborador/prensa solo se aplica si no hay rol administrador. Test cubre administrador combinado con ambos roles y verifica que colaborador sin administrador siga sin ver datos ajenos. Archivo completo: 7 pruebas/59 aserciones pasan. Lint PHP, OpenSpec y diff check pasan. Auditoría HTTP interna con la cuenta local de roles combinados ahora muestra los tres demos (200/4 jornadas, 200/5 jornadas, 200/Una fecha); no cambió usuarios, roles ni datos persistentes. Falta confirmar que la sesión reportada por el usuario corresponde a esa cuenta; se solicitó URL/rol de forma opcional.

Sin cambios de esquema, URLs públicas, caché público, SEO ni nuevas consultas; la corrección quita una condición SQL para administradores. No se volvió a ejecutar toda la regresión de 106 porque el cambio es una condición de permisos con prueba dirigida. Listado vacío por otros filtros/página o entorno requiere verificar URL si persiste después de recargar.

## Ampliación validada: ingreso único (2026-10-08)

- Usuario aprobó reemplazar el ingreso separado por un único Eventos / Cartelera. Se conserva la rama y esquema; no se ejecutó ninguna migración ni conversión de datos persistentes.
- Nuevo evento permite Una fecha o Varias jornadas en el mismo formulario y endpoint; cambia campos activos, validación y guardado. Mínimo dos jornadas al crear, máximo 60; recuperación del formato e inputs ante errores. Publicación permanece sujeta a permisos.
- El listado agrupa por programación en SQL (una fila representante por program_id), cuenta jornadas y obtiene fecha inicial con agregados, eager loading y paginación de 25. Búsqueda incluye título principal y artistas de jornadas; orden muestra títulos/estados principales. Sin consultas por fila, nuevos scripts globales ni cambios SEO públicos.
- Botón Agregar jornadas al evento existente: transacción y policy update, mantiene ID/slug/grilla/relaciones/imagen de la primera jornada; repetición conserva program_id. La imagen original permanece en esa jornada; imagen común se puede agregar desde el principal. No se convirtieron demos ni otros datos existentes porque ya ejemplifican ambos formatos.
- Rutas históricas de índice y creación de programaciones redirigen al ingreso único; edición y guardado se conservan por compatibilidad. Sidebar tiene solo Eventos / Cartelera, activo también durante edición de programaciones. Textos del Backoffice usan Evento y Jornada.
- `UnifiedEventEntryTest`: 6 pruebas y 53 aserciones pasan; cubren creación simple/nocturna/múltiple, búsqueda en cualquier jornada, errores/minimum, conservación de identidad/idempotencia, permisos, borradores, redirecciones y menú. Consultas constantes al crecer de 2 a 8 jornadas, sin lazy loading y HTML <350 KB.
- Regresión seleccionada final: **106 pruebas, 52.822 aserciones, todas pasan** en 39,57 s. Comando XML auditado con directorio Eventos y archivos de publicación, consistencia, encabezados, visibilidad, festivales, SEO y sidebar; caché en memoria, response cache desactivado y `--do-not-cache-result`. DatabaseTransactions, Queue/Http fake, sin resets/seeders/DDL.
- 24 vistas Blade compiladas y sintaxis PHP generado válida; sintaxis JS del selector/jornadas verificada con `node --check`. Pint y OpenSpec válidos, diff check sin errores (advertencia CRLF de documentación preexistente). No se reconstruyen assets: cambios exclusivamente en formularios Blade/JS locales de Backoffice y PHP/config.
- Auditoría HTTP interna autenticada read-only del listado sobre demos reales: Encuentro del Río → 200/4 jornadas, Fiesta de la Luna → 200/5 jornadas, Peña del Patio → 200/Una fecha. Script local ignorado `storage/app/local-audits/check-unified-event-demos.php`; no se creó cuenta ni sesión persistente. Primer intento del script no había inicializado Request CLI; corregido, sin fallo de aplicación.
- Conteos posteriores iguales a antes: events 324, venues 2, interpretes 446, news 366, users 435, migrations 82. No se tocó producción ni se hizo commit/push/despliegue. QA visual pendiente por falta de navegador disponible.

### Probar el ingreso único

1. Recargar Backoffice → Eventos / Cartelera. Ya no hay entrada Conjuntos / Jornadas.
2. Buscar Encuentro del Río o Fiesta de la Luna: cada uno aparece una vez con 4/5 jornadas. Abrir Editar evento y jornadas; cambiar defaults o entrar a una jornada para editar sus artistas/horarios.
3. Nuevo evento → Una fecha: cargar fecha, artistas y datos generales. Si la noche termina al día siguiente, sigue siendo una fecha.
4. Nuevo evento → Varias jornadas: cargar nombre/detalle/lugar comunes y completar las dos jornadas iniciales; Agregar jornada incorpora otras. Guardar abre la edición del evento y sus jornadas.
5. Abrir Peña del Patio → Agregar jornadas al evento: la fecha ya cargada se conserva como primera jornada; agregar las siguientes desde esa edición. Esta acción sí modifica esa demo, como parte de la prueba del usuario.
6. Probar fecha inválida: vuelve al mismo formato conservando valores y mostrando errores.

## Estado al 2026-10-08

Implementado y validado en local en `feature/event-programs-and-venues`. Aprobación funcional explícita del usuario, incluida rama nueva y ABM independiente de Salas/Lugares. El usuario autorizó las migraciones explícitamente; se aplicó únicamente la nueva. Listo para prueba del usuario, con revisión interactiva pendiente. Sin integración ni despliegue.

## Comprobado

- OpenSpec válido; `git diff --check` sin errores.
- Sintaxis PHP de modelos, servicios, controladores y tests nuevos verificada; Pint aplicado a archivos nuevos y EventService.
- 21 vistas afectadas compiladas y PHP generado analizado con TOKEN_PARSE, sin errores. Script local ignorado: `storage/app/local-audits/validate-event-views.php`.
- `npm run build` y `npm run build:verify`: 13 assets declarados presentes. JS público global conserva su hash; nuevos formularios usan scripts locales.
- Docker iniciado; status previo: 81 migraciones aplicadas y únicamente `2026_10_08_120000_create_event_programs` pendiente. Tras autorización se ejecutó `docker compose exec --user www-data app php artisan migrate --path=database/migrations/2026_10_08_120000_create_event_programs.php`: DONE. Status final: nueva migración Ran, batch 2; 82 aplicadas, ninguna pendiente.
- Base de tests confirmada: `.env.testing` usa mysql/db/mfa (base local persistente), caché en memoria. Tests ejecutados con DatabaseTransactions, Queue::fake y Http::fake cuando corresponden; sin RefreshDatabase, seeders, DDL ni resets en los tests seleccionados.
- Conteos iniciales por consulta read-only: events 314, venues 0, interpretes 446, news 366, users 435, migrations 81. Evidencia inicial sin datos personales: `storage/app/local-audits/event-programs-baseline.json`, no se reemplaza.
- Conteos finales después de migración y tests: events 314, venues 0, interpretes 446, news 366, users 435, migrations 82. Sin contenido demo persistente. Migración aditiva, sin actualización de filas existentes.
- Eventos: 52 pruebas pasan. Regresión combinada con AdminAutoPublishBackendTest, ContentModelConsistencyTest, RichTextHeadingBackendNormalizationTest, PublicEventVisibilityTest, FestivalPublicRelatedContentTest y SeoInfrastructureTest: 97 pasan y un fallo por colisión preexistente del slug de categoría `ritmos`. Se cambió solo la fixture a slug único; repetición del archivo: 2/2 pasan, 34 aserciones. Total: 98 pruebas distintas aprobadas entre ambas corridas.
- Comando combinado: `docker compose exec --user www-data -e RESPONSE_CACHE_ENABLED=false -e RESPONSE_CACHE_DRIVER=array app php artisan test -c storage/app/local-audits/event-programs-phpunit.xml --do-not-cache-result`. XML local ignorado enumera los tests auditados; repetición dirigida con las mismas variables. La corrida inicial de Eventos detectó dos fixtures de artista sin biografía; corregidas y validadas en la corrida combinada.

## Pendiente

Comprobar interacción real CKEditor/Select2 y creación/edición de jornadas con sesión habitual del usuario. La skill computer-use se consultó y se intentó abrir el formulario local, pero la herramienta no dispone de navegadores (`apps: [], browsers: []`; in-app browser no disponible). Formularios y guardado cubiertos por tests HTTP y compilación Blade; no se declara QA visual completado. No se crearon usuarios ni contenido demo persistente.

## Checklist para el usuario (listo para probar en local)

- Entrar a Backoffice → Salas / Lugares: crear un lugar ficticio con localidad/provincia; editar dirección; probar filtros e inactivación.
- Eventos → Conjuntos / Jornadas → Nuevo conjunto: cargar datos comunes y tres jornadas con artistas distintos; imagen opcional.
- Guardar; editar una jornada y cambiar entradas o lugar. Corregir datos comunes y comprobar que conserva esa excepción. Probar volver a heredar.
- Publicar conjunto y jornadas revisadas; visitar ficha del conjunto y fichas individuales, usar filtros por fecha y artista.
- Abrir un evento histórico: comprobar URL, ubicación anterior y HTML en CKEditor; guardar cambios propios.
- Como colaborador: proponer lugar/conjunto, comprobar borradores. Como administrador: revisar en Bandeja, activar lugar y publicar una sola jornada; las otras siguen borrador.

## Performance y SEO

Listados públicos y conjuntos paginados en SQL; eager loading de lugar, conjunto, imagen y artistas. No mapa, iframe ni script público global nuevo. Corrección de datos comunes procesa jornadas en bloques de 50 y conserva las excepciones; costo de escritura proporcional a las jornadas, sin cargas completas del conjunto. Slugs de eventos existentes no se regeneran; conjuntos tienen canonical propio, página 2+ noindex,follow e ItemList sin atribuir artistas a jornadas ajenas. Ficha y agenda usan EventSchema compartido con fechas, sede y coordenadas verificadas si existen. Cache de respuestas y provincias se invalida al cambiar eventos, conjuntos o lugares. Pruebas de consultas constantes, paginación, ausencia de lazy loading y HTML menor a 350 KB pasan; metadata, canonical y sitemap cubiertos por regresión. Sin mediciones productivas.

## Observaciones

- Advertencias preexistentes del entorno: OPcache cargado dos veces en PHP y caniuse-lite desactualizado en build; no bloquean sintaxis/build. No se actualizaron dependencias.
- Caché de resultados PHPUnit preexistente sin permiso de escritura: corridas finales con `--do-not-cache-result`, sin cambiar permisos ni archivos ajenos.
- Cambios previos de guía para publicar preservados en el checkout, incluidos sus cambios en rutas, sitemap, footer y documentación. Sin commit, push ni despliegue.

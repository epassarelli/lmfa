# 08 - Inventario Tecnico y Legacy

> Inventario tecnico y de legado relevado directamente sobre el repositorio, la BD local y la superficie publica de produccion en modo lectura.
> Fecha de corte: 2026-08-20.
> Objetivo: dejar una fotografia operativa de modulos, entidades, tablas, rutas, APIs, jobs, automatizaciones, integraciones y componentes legacy, con foco especial en noticias, shows y automatizaciones activas o pausadas.

---

## 1. Criterios y evidencia

Fuentes inspeccionadas para este inventario:

- `routes/web.php`
- `routes/admin.php`
- `routes/api.php`
- `app/Models/*`
- `app/Http/Controllers/Frontend/*`
- `app/Http/Controllers/Backend/*`
- `app/Http/Controllers/Api/*`
- `app/Http/Controllers/Pasarela/*`
- `app/Services/*`
- `app/Services/Connectors/*`
- `app/Jobs/*`
- `app/Console/Kernel.php`
- `database/migrations/*`
- `database/migrate_noticias_to_news.sql`
- `database/migrate_shows_to_events.sql`
- `project/docs/00_estado_actual.md`
- `project/docs/02_modelo_datos.md`
- `project/docs/04_arquitectura.md`
- `project/docs/05_migraciones.md`
- `project/docs/06_endpoints_api.md`
- `project/docs/07_hoja_de_ruta.md`
- `project/docs/ia/agente_carga_noticias.md`
- `project/docs/ia/agente_carga_eventos.md`
- `project/docs/ia/agente_carga_enciclopedia.md`
- superficie publica de `https://mifolkloreargentino.com/`
- superficie publica de `https://mifolkloreargentino.com/cartelera-de-eventos-folkloricos`
- superficie publica de `https://mifolkloreargentino.com/enciclopedia`
- superficie publica de `https://mifolkloreargentino.com/sitemap.xml`

Comandos de verificacion usados:

- `php artisan route:list --json`
- `php artisan about --only=environment`
- `SHOW TABLES`
- `SELECT COUNT(*)` sobre tablas clave

Identificacion exacta del repo auditado:

- ruta local: `C:\proyectos\lmfa`
- rama activa observada: `20260820`
- commit HEAD observado: `20c0394b5a9273b63ee059d52f9c26b627a43da2`
- observacion: el worktree no estaba limpio al momento del relevamiento; se audito sin revertir cambios ajenos

---

## 2. Resumen ejecutivo

La aplicacion vigente es un monolito Laravel 10 con:

- frontend publico Blade + Tailwind
- backend administrativo con AdminLTE
- API REST autenticada con Sanctum
- Pasarela de Contenidos integrada al mismo core
- modulos evergreen y modulos legacy conviviendo
- media canonica en `media_assets`
- compatibilidad residual para `noticias`, `shows` e `images`

Fotografia local observada el 2026-08-20:

- `news`: 353 registros
- `events`: 8 registros
- `noticias`: 0 registros
- `shows`: 0 registros
- `publication_requests`: 0 registros
- `publication_targets`: 0 registros
- `publication_attempts`: 0 registros
- `jobs`: 0 registros
- `failed_jobs`: 0 registros
- `newsletter_subscribers`: 2 registros
- `data_deletion_requests`: 0 registros

Conclusiones operativas inmediatas:

- `news` y `events` son las tablas canonicas efectivamente usadas.
- `Noticia` y `Show` subsisten como aliases deprecated sobre `news` y `events`.
- La Pasarela esta implementada y testeada, pero la BD local no muestra actividad cargada.
- Hay automatizaciones reales activas en codigo para newsletter, recordatorios de eventos y procesamiento de cola.
- `Entrevistas`, `Videos`, `Radios`, `Penias` y `venues` siguen siendo frentes parciales o incompletos.
- La superficie publica de produccion confirma home, cartelera, enciclopedia, sitemap y modulos publicos visibles coherentes con el core documentado, pero no permite por si sola validar jobs internos, colas, panel admin ni integraciones autenticadas.

---

## 3. Modulos principales

| Modulo | Ubicacion principal | Funcion | Estado | Dependencias | Evidencia |
|---|---|---|---|---|---|
| Frontend publico | `routes/web.php`, `app/Http/Controllers/Frontend/*` | Portal editorial, SEO, miniportales, legales y navegacion | Activo | Blade, Eloquent, middleware `web` | Rutas publicas y controladores presentes |
| Backend admin | `routes/admin.php`, `app/Http/Controllers/Backend/*` | CRUD editorial, moderacion, usuarios, permisos y newsletter | Activo | Auth, policies, Spatie Permission | Recursos admin activos |
| API REST v1 | `routes/api.php`, `app/Http/Controllers/Api/*` | Lectura autenticada y escritura administrativa | Activa | Sanctum, rol `administrador` | Endpoints `news`, `events`, `knowledge-articles`, etc. |
| Pasarela de Contenidos | `app/Http/Controllers/Pasarela/*`, `app/Services/Publication/*` | Solicitudes multicanal, targets, retry, templates, dashboards | Implementada | `publication_*`, `social_accounts`, jobs | Rutas, servicios y tests dedicados |
| Enciclopedia | `knowledge_*`, frontend, backend y API | Enciclopedia evergreen con categorias y articulos | Activa | `knowledge_categories`, `knowledge_articles`, pivots | Rutas, servicio, policies y tests |
| Torneos | `folklore_tournament*`, frontend y backend | Copa del Folklore 2026 | Activo | tablas y servicios de fixture/standing | Rutas y modelos activos |
| Clasificados | `classifieds`, `categories`, `tags` | Avisos clasificados publicos y admin | Activo | taxonomias propias, auth parcial | Rutas frontend y backend |
| Legales + Meta/Facebook | `LegalController`, `config/services.php` | Politica, condiciones, deleteuserdata, callback firmado | Activo localmente | Meta/Facebook, Socialite, `data_deletion_requests` | Rutas legales y test dedicado |

---

## 4. Entidades y tablas canonicas

### 4.1 Editorial principal

| Tabla | Modelo | Funcion | Estado | Evidencia |
|---|---|---|---|---|
| `news` | `News` | Noticias canonicas | Activa | `app/Models/News.php` |
| `events` | `Event` | Eventos y cartelera canonica | Activa | `app/Models/Event.php` |
| `interpretes` | `Interprete` | Artistas y miniportales | Activa | `app/Models/Interprete.php` |
| `albunes` | `Album` | Discografia | Activa | `app/Models/Album.php` |
| `canciones` | `Cancion` | Canciones y letras | Activa | `app/Models/Cancion.php` |
| `festivales` | `Festival` | Festivales evergreen | Activa | `app/Models/Festival.php` |
| `mitos` | `Mito` | Mitos y leyendas | Activa | `app/Models/Mito.php` |
| `comidas` | `Comida` | Recetas | Activa | `app/Models/Comida.php` |
| `knowledge_categories` | `KnowledgeCategory` | Taxonomia de Enciclopedia | Activa | `app/Models/KnowledgeCategory.php` |
| `knowledge_articles` | `KnowledgeArticle` | Articulos evergreen | Activa | `app/Models/KnowledgeArticle.php` |

### 4.2 Pasarela, colaboraciones y operacion

| Tabla | Modelo | Funcion | Estado | Evidencia |
|---|---|---|---|---|
| `organizations` | `Organization` | Organizaciones editoriales o institucionales | Activa | modelo y rutas Pasarela |
| `organization_members` | `OrganizationMember` | Membresias y roles | Activa | modelo y joins de Pasarela |
| `social_accounts` | `SocialAccount` | Cuentas conectadas por usuario u organizacion | Activa | `SocialAccountController`, conectores |
| `publication_requests` | `PublicationRequest` | Intencion multicanal | Activa | `PublicationService` |
| `publication_targets` | `PublicationTarget` | Destinos por canal | Activa | `PublicationService` y retry |
| `publication_attempts` | `PublicationAttempt` | Trazabilidad por intento | Activa | `BaseConnector::recordAttempt()` |
| `publication_templates` | `PublicationTemplate` | Templates por canal | Activa | CRUD admin Pasarela |
| `contributions` | `Contribution` | Flujo UGC | Activa | frontend y backend de contribuciones |
| `moderation_reviews` | `ModerationReview` | Revisiones editoriales | Activa | modelo y flujo de moderacion |
| `audit_logs` | `AuditLog` | Trazabilidad operativa | Activa | tests Pasarela |
| `user_notifications` | `UserNotification` | Notificaciones internas | Activa | controller y rutas |
| `newsletter_subscribers` | `NewsletterSubscriber` | Suscriptores del newsletter | Activa | job/comando de newsletter |
| `data_deletion_requests` | `DataDeletionRequest` | Solicitudes de borrado Meta/Facebook | Activa | modelo, migracion y flujo legal |

### 4.3 Geografia, taxonomias y clasificados

| Tabla | Modelo | Funcion | Estado | Evidencia |
|---|---|---|---|---|
| `provincias` | `Provincia` | Provincias | Activa | modelos y filtros |
| `localities` | `Locality` | Localidades | Activa | festivales |
| `meses` | `Mes` | Meses | Activa | festivales |
| `categorias` | `Categoria` | Categorias legacy/canonicas de noticias | Activa | `news.categoria_id` |
| `classifieds` | `Classified` | Clasificados | Activa | modulo publico y admin |
| `categories` | `Category` | Categorias de clasificados | Activa | modulo clasificados |
| `tags` | `Tag` | Etiquetas de clasificados | Activa | modulo clasificados |
| `classified_tag` | `ClassifiedTag` / pivot | Relacion N:M | Activa | pivot funcional |

### 4.4 Torneos

| Tabla | Modelo | Funcion | Estado | Evidencia |
|---|---|---|---|---|
| `folklore_tournaments` | `FolkloreTournament` | Torneos | Activa | frontend y backend |
| `folklore_tournament_groups` | `FolkloreTournamentGroup` | Zonas | Activa | modelo |
| `folklore_tournament_participants` | `FolkloreTournamentParticipant` | Participantes | Activa | modelo |
| `folklore_tournament_matches` | `FolkloreTournamentMatch` | Partidos | Activa | backend y frontend |

---

## 5. Legacy y compatibilidad

### 5.1 Legacy estructural

| Tabla / componente | Estado | Funcion actual | Evidencia |
|---|---|---|---|
| `noticias` | Legacy preservada | No debe usarse para nuevas altas | `database/migrate_noticias_to_news.sql`, docs |
| `shows` | Legacy preservada | No debe usarse para nuevas altas | `database/migrate_shows_to_events.sql`, docs |
| `images` | Legacy preservada | Reemplazada por `media_assets` | migracion `transform_images_to_media_assets_table` |

### 5.2 Alias de compatibilidad vivos

| Alias | Ubicacion | Estado | Observacion |
|---|---|---|---|
| `Noticia` | `app/Models/Noticia.php` | Deprecated | Extiende `News` y apunta a `news` |
| `Show` | `app/Models/Show.php` | Deprecated | Extiende `Event` y apunta a `events` |
| campos `titulo`, `noticia`, `foto`, `publicar`, `estado` | `News` | Compatibilidad viva | Solo seguros para instancias, no para queries nuevas |
| campos `show`, `detalles`, `fecha`, `lugar`, `direccion`, `publicar`, `estado` | `Event` | Compatibilidad viva | Mantienen compatibilidad con `shows` |
| `Interprete::shows()` | `app/Models/Interprete.php` | Deprecated | Redirige a `events()` |

### 5.3 Estado observado hoy para noticias y shows legacy

- `news` tiene 353 registros.
- `events` tiene 8 registros.
- `noticias` tiene 0 registros en la BD local.
- `shows` tiene 0 registros en la BD local.
- La app opera sobre `news` y `events`.
- La compatibilidad legacy se sostiene desde modelos, rutas SEO, tests y algunos nombres de campo.

Interpretacion:

- el legado sigue siendo relevante a nivel de codigo y documentacion;
- en la BD local no conserva hoy datos operativos;
- no conviene eliminar tablas o aliases sin una auditoria transversal de modelos, controladores, vistas, tests, jobs y SEO.

---

## 6. Rutas y entrypoints

### 6.1 Web publica

Archivo:

- `routes/web.php`

Familias reales observadas:

- home
- noticias
- cartelera
- interpretes
- letras
- discografias
- festivales
- radios
- penias
- mitos
- recetas
- enciclopedia
- contacto
- buscador
- clasificados
- legales y Meta/Facebook
- newsletter
- social auth
- copa del folklore 2026
- sitemaps
- miniportal por interprete

### 6.2 Backend admin

Archivo:

- `routes/admin.php`

Recursos reales observados:

- `events`
- `news`
- `knowledge-articles`
- `folklore-tournaments`
- `social-accounts`
- `publication-requests`
- `templates`
- `roles`
- `users`
- `permissions`
- `interpretes`
- `categories`
- `tags`
- `classifieds`
- `mitos`
- `comidas`
- `festivales`
- `discos`
- `canciones`
- `newsletter-subscribers`
- `moderation`
- `contributions`

### 6.3 API REST

Archivo:

- `routes/api.php`

Recursos reales observados:

- `news`
- `knowledge-articles`
- `knowledge-categories`
- `albums`
- `songs`
- `foods`
- `festivals`
- `artists`
- `myths`
- `events`

Regla de acceso:

- lectura: cualquier token Sanctum valido
- escritura: token Sanctum + rol `administrador`

### 6.4 Contraste puntual con produccion publica

Lecturas publicas verificadas el 2026-08-20:

- home `https://mifolkloreargentino.com/`: activa; muestra secciones publicas de noticias, artistas, cartelera, enciclopedia, clasificados, discos, canciones y festivales
- cartelera `https://mifolkloreargentino.com/cartelera-de-eventos-folkloricos`: activa; muestra filtros y eventos futuros, coherente con `events`
- enciclopedia `https://mifolkloreargentino.com/enciclopedia`: activa y enlazada desde la home
- sitemap `https://mifolkloreargentino.com/sitemap.xml`: activo en produccion publica
- radios y penias: aparecen como rutas publicas en el codigo y se indexan como modulos visibles, aunque siguen sin backend admin propio en el repo

Limite de esta verificacion:

- la produccion publica permite confirmar modulos frontend y consistencia superficial de URLs
- no permite confirmar por si sola estado real de colas, cron, integraciones autenticadas, panel admin ni tablas internas sin acceso adicional

---

## 7. Jobs, comandos y automatizaciones

### 7.1 Jobs reales

| Job | Ubicacion | Funcion | Estado |
|---|---|---|---|
| `SendNewsletterJob` | `app/Jobs/SendNewsletterJob.php` | Envio de newsletter por suscriptor | Activo |
| `EventReminderJob` | `app/Jobs/Publication/EventReminderJob.php` | Recordatorios y creacion de publication requests para eventos proximos | Activo |
| `PublishToProviderJob` | `app/Jobs/Publication/PublishToProviderJob.php` | Publicacion por canal y manejo de reintentos | Activo |

### 7.2 Comandos reales

| Comando | Ubicacion | Funcion | Estado |
|---|---|---|---|
| `newsletter:send-weekly` | `SendWeeklyNewsletterCommand` | Prepara contenido semanal y despacha jobs | Activo |
| `AssignDefaultRole` | `app/Console/Commands/AssignDefaultRole.php` | Soporte operativo de roles | Utilidad interna |
| `AuditRecipesCommand` | `app/Console/Commands/AuditRecipesCommand.php` | Auditoria de recetas | Utilidad interna |
| `EmergencyFixPermissions` | `app/Console/Commands/EmergencyFixPermissions.php` | Reparacion de permisos/caches | Utilidad interna |

### 7.3 Scheduler real

Definido en `app/Console/Kernel.php`:

- newsletter semanal: lunes 10:00
- `EventReminderJob`: diario 08:00
- `queue:work --stop-when-empty --tries=3`: cada 5 minutos

### 7.4 Automatizaciones activas o pausadas

| Automatizacion | Estado | Evidencia | Observacion |
|---|---|---|---|
| Newsletter semanal | Activa en codigo | `newsletter:send-weekly` + `SendNewsletterJob` | Depende de cron y mail configurado |
| Recordatorios de eventos | Activa en codigo | `EventReminderJob` | Genera `PublicationRequest` si corresponde |
| Procesamiento de cola | Activo en scheduler | `queue:work --stop-when-empty` | Depende de cron y `QUEUE_CONNECTION` del entorno |
| Pasarela multicanal | Implementada | `PublicationService`, `PublishToProviderJob`, conectores | BD local sin requests/targets/attempts cargados |
| Alta automatizada Noticias | Preparada | `project/docs/ia/agente_carga_noticias.md` | Token manual + draft |
| Alta automatizada Eventos | Preparada | `project/docs/ia/agente_carga_eventos.md` | Token manual + draft |
| Alta automatizada Enciclopedia | Preparada | `project/docs/ia/agente_carga_enciclopedia.md` | Token manual + draft; Apps Script externo pendiente de cierre E2E |

---

## 8. Integraciones externas

### 8.1 Integraciones implementadas

| Integracion | Ubicacion | Funcion | Estado |
|---|---|---|---|
| Google OAuth | `SocialiteController`, `config/services.php` | Login social | Implementada |
| Facebook OAuth | `SocialiteController`, `config/services.php` | Login social | Implementada |
| Facebook Graph | `FacebookConnector` | Publicacion Pasarela | Implementada |
| Instagram Graph / Meta Publishing | `InstagramConnector` | Publicacion Pasarela | Implementada |
| Telegram Bot API | `TelegramConnector` | Publicacion Pasarela | Implementada |
| Native portal | `NativePortalConnector` | Publicacion en portal | Implementada |
| Meta Data Deletion | `LegalController`, `DataDeletionRequest` | Callback firmado y trazabilidad | Implementada |

### 8.2 Integraciones no implementadas como runtime vigente

- X
- TikTok
- LinkedIn
- login por endpoint API para agentes externos
- Apps Script o Google Sheets dentro del repo

Observacion:

- existen referencias documentales a Apps Script y planillas, pero la automatizacion externa no vive en este repositorio.

---

## 9. Componentes parciales, diferidos o incompletos

| Componente | Ubicacion | Estado | Evidencia |
|---|---|---|---|
| Entrevistas | `Frontend/EntrevistasController.php` + rutas miniportal | Incompleto | el controller solo tiene `index`; rutas apuntan a `byArtista` y `show`; no se relevan modelo/vistas cerradas |
| Videos | `Frontend/VideosController.php` | Incompleto | usa `Video` sin modulo visible consolidado |
| Radios | `Frontend/RadiosController.php`, `app/Models/Radio.php` | Parcial | frontend y modelo presentes; sin backend admin propio |
| Penias | `Frontend/PeniasController.php`, `app/Models/Penia.php` | Parcial | frontend y modelo presentes; sin backend admin propio |
| Venues | tabla `venues` | Incompleto | tabla en BD; sin `Venue` model visible en `app/Models` |

---

## 10. Tests y validacion observable

Cobertura visible y util para este inventario:

- `tests/Feature/Pasarela/*`
- `tests/Feature/Knowledge/*`
- `tests/Feature/FolkloreTournament*`
- `tests/Feature/MetaDataDeletionFlowTest.php`
- `tests/Feature/AdminUserApiTokenManagementTest.php`
- `tests/Feature/ApiTokenPermissionsTest.php`

Cobertura especialmente relevante:

- Pasarela: requests, targets, templates, social accounts, dashboards, jobs
- Knowledge: frontend, API, autorizacion, seeder, normalizacion
- Meta/Facebook legales: flujo completo de `deleteuserdata`
- Bugs legacy: `NoticiasBugFixTest.php`, `ShowsBugFixTest.php`

Limitacion conocida:

- el proyecto documenta una falla preexistente en `tests/Feature/TwoFactorAuthenticationSettingsTest.php`, por lo que no debe asumirse que toda la suite completa este verde en todos los entornos.

---

## 11. Riesgos y faltantes

### 11.1 Riesgos tecnicos

- conviven naming y relaciones modernas con convenciones legacy;
- eliminar `noticias`, `shows`, `images` o aliases deprecated sin auditoria puede romper frontend, SEO, tests o relaciones historicas;
- `venues` sigue siendo una pieza incompleta del modelo;
- `Entrevistas` y `Videos` tienen huellas de modulo no cerrado.

### 11.2 Riesgos operativos

- la Pasarela existe, pero la BD local no muestra uso cargado ni actividad reciente;
- el procesamiento real depende de cron y configuracion de cola del entorno;
- la alta automatizada por API para agentes sigue dependiendo de tokens manuales Sanctum;
- el cierre end-to-end del flujo externo con Apps Script no puede validarse desde este repo.

### 11.3 Faltantes documentales o de gobernanza

- conviene mantener este inventario sincronizado con `00_estado_actual.md`, `02_modelo_datos.md`, `04_arquitectura.md`, `05_migraciones.md` y `06_endpoints_api.md`;
- cualquier limpieza futura de legado deberia apoyarse en una auditoria transversal de codigo y datos;
- si el modulo de Peñas o Radios pasa a prioridad real, necesita definicion funcional y backlog propio.

## 12. Diferencias explicitas entre codigo, base, produccion publica y documentacion

| Area | Codigo / repo | Base local | Produccion publica | Documentacion | Lectura operativa |
|---|---|---|---|---|---|
| Noticias / eventos canonicos | controladores, modelos y API sobre `news` / `events` | `news` con 353 y `events` con 8 | home y cartelera muestran contenido vigente | `00_estado_actual`, `02_modelo_datos`, `06_endpoints_api` lo reflejan | consistente |
| Legacy `noticias` / `shows` | aliases y compatibilidad aun presentes | ambas tablas existen pero con 0 registros | no hay evidencia publica de uso directo de esas tablas | documentadas como legacy preservado | consistente, pero no eliminable aun |
| Pasarela de Contenidos | implementada en controladores, servicios, jobs y tests | `publication_requests`, `publication_targets`, `publication_attempts` existen pero con 0 registros locales | no visible en frontend publico | `00_estado_actual` y este inventario la marcan como implementada pero no validada E2E en produccion | consistente con cautela |
| Automatizaciones / scheduler | `Kernel.php` programa newsletter, recordatorios y cola | `jobs` y `failed_jobs` estan en 0 en esta BD local | no visibles desde frontend publico | documentadas como activas en codigo y dependientes del entorno | consistente, pero no demostradas en runtime productivo desde esta lectura |
| Radios / Penias | rutas y controladores frontend presentes; sin backend admin | tablas `radios` y `penias` existen | modulos publicos inferibles por URLs indexadas y rutas presentes | `00_estado_actual` e inventario las marcan como parciales | consistente |
| Entrevistas / Videos | huellas de modulo incompleto en controladores/rutas | sin evidencia de cierre como modulo completo | no verificados como modulo publico vigente en esta pasada | documentados como incompletos/diferidos | consistente |
| Backlog y operacion diaria | no viven en runtime Laravel | no aplica | no visible publicamente | `07_hoja_de_ruta` y `FUENTES_CANONICAS` remiten a Drive | consistente |

---

## 13. Resumen final

Mi Folklore Argentino opera hoy sobre un core canonico moderno para noticias, eventos, enciclopedia, media, API y Pasarela, pero mantiene una capa importante de compatibilidad historica.

La evidencia relevada hoy muestra que:

- `news` y `events` son la verdad operativa del contenido reciente;
- `noticias` y `shows` sobreviven como legado estructural y semantico, no como tablas activas de contenido en esta BD local;
- la Pasarela y las automatizaciones existen realmente en codigo;
- su uso local visible hoy es nulo o no cargado, y la produccion publica no alcanza por si sola para demostrar su runtime interno;
- persisten modulos parciales que deben tratarse como deuda tecnica, no como funcionalidad cerrada.

# 05 - Migraciones

## Actualización 2026-09-04: directorios evergreen

- `2026_09_02_200000_create_penia_profiles_table`: crea el contrato canónico `penia_profiles`.
- `2026_09_02_200100_create_penia_profile_event_table`: incorpora la relación N:M con eventos.
- `2026_09_03_010000_retire_legacy_penias_table`: quedó como no-op seguro; no debe destruir legado durante el release.
- `2026_09_03_015000_restore_legacy_penias_bridge`: restaura de forma idempotente el puente `legacy_penia_id` cuando corresponde.
- `2026_09_03_020000_create_radio_signals_table`: crea señales de radio canónicas.
- `2026_09_03_020100_create_radio_programming_tables`: crea canales de escucha, programas y franjas semanales.

En el Docker local estas migraciones canónicas están aplicadas. Existen otras migraciones ajenas pendientes, por lo que el release gate no autoriza ejecutar una migración global sin backup y revisión de staging.

> Estado real de las migraciones versionadas al **2026-08-20**.  
> Este documento reemplaza la propuesta teórica original y describe qué migraciones existen hoy, qué problema resolvió cada bloque y qué deudas o compatibilidades siguen vigentes.

---

## 1. Criterios de lectura

- La base del proyecto es híbrida: conviven migraciones legacy importadas con migraciones nuevas orientadas a `news`, `events`, `media_assets`, Enciclopedia, Festivales y performance.
- No todas las tablas presentes en la base son canónicas ni todas las migraciones implican reemplazo inmediato de tablas legacy.
- En varios casos la estrategia fue:
  - crear tabla o estructura nueva,
  - mantener compatibilidad temporal,
  - migrar el uso del código antes de pensar en eliminar legado.
- Este documento refleja:
  1. archivos realmente presentes en `database/migrations`,
  2. migraciones efectivamente ejecutadas en el entorno local,
  3. relación con el esquema y la app tal como funciona hoy.

---

## 2. Resumen ejecutivo

La historia de migraciones hoy se divide en cinco etapas:

1. **Bootstrap legacy del portal**  
   Alta inicial de tablas históricas como `interpretes`, `albunes`, `canciones`, `noticias`, `shows`, `festivales`, `mitos`, `comidas`, `provincias`, `meses`, `users`, ACL y sesiones.

2. **Ampliaciones tempranas del portal**  
   Imágenes, contribuciones, clasificados, newsletter y ajustes de usuario.

3. **Modernización de contenido y pasarela editorial**  
   Aparición de `organizations`, `events`, `news`, `media_assets`, publicación multicanal, moderación, jobs y cuentas sociales.

4. **Nuevos módulos estructurados**  
   Torneos folklóricos y Enciclopedia (`knowledge_categories`, `knowledge_articles` y pivots).

5. **Hardening operativo**  
   Reestructuracion de Festivales, multiples tandas de indices de performance y cumplimiento legal/Meta.

---

## 3. Estado actual de ejecución

## 3.1 Migraciones presentes en el repositorio

Hoy existen migraciones desde:

- `2019_12_14_000001_create_personal_access_tokens_table.php`
- `2025_11_26_194813_*`
- `2025_11_26_194816_*`
- `2026_03_*`
- `2026_04_*`
- `2026_06_*`
- `2026_08_*`

## 3.2 Últimas migraciones registradas

Las últimas migraciones ejecutadas localmente son:

- `2026_08_20_120000_create_data_deletion_requests_table`
- `2026_08_09_150000_add_content_section_indexes`
- `2026_08_09_140000_add_secondary_performance_indexes`
- `2026_08_09_120000_add_performance_indexes`
- `2026_08_08_120000_restructure_festivales_first_stage`
- `2026_08_04_010200_create_knowledge_article_relationship_tables`
- `2026_08_04_010100_create_knowledge_articles_table`
- `2026_08_04_010000_create_knowledge_categories_table`

## 3.3 Consideraciones de entorno

- Producción puede requerir cuidado especial con migraciones que agregan FKs sobre tablas legacy.
- Ya se detectó en Hostinger un caso real donde la migración de Festivales falló por una foreign key mal formada en `localities -> provincias`.
- Por eso no conviene asumir que “si corre local, corre igual en producción” sin revisar tipos, collations y estado previo de tablas.

---

## 4. Cronología real por bloques

## 4.1 Bootstrap Laravel y autenticación

### `2019_12_14_000001_create_personal_access_tokens_table`

Objetivo:

- habilitar Laravel Sanctum para tokens personales y autenticación API

Impacto actual:

- tabla activa y crítica para `/api/*`
- usada también para la generación manual de bearer tokens

---

## 4.2 Bloque legacy importado del portal

### Prefijo `2025_11_26_194813_*`

Estas migraciones crean la base histórica del portal:

- `create_users_table`
- `create_permissions_table`
- `create_roles_table`
- `create_model_has_permissions_table`
- `create_model_has_roles_table`
- `create_role_has_permissions_table`
- `create_sessions_table`
- `create_password_resets_table`
- `create_failed_jobs_table`
- `create_provincias_table`
- `create_meses_table`
- `create_categorias_table`
- `create_interpretes_table`
- `create_albunes_table`
- `create_canciones_table`
- `create_albunes_canciones_table`
- `create_album_interprete_table`
- `create_noticias_table`
- `create_interprete_noticia_table`
- `create_shows_table`
- `create_festivales_table`
- `create_mitos_table`
- `create_comidas_table`
- `create_penias_table`
- `create_radios_table`

Lectura correcta de este bloque:

- no responde a una convención moderna homogénea;
- fija la realidad histórica del proyecto;
- varias tablas siguen vigentes y activas hoy;
- otras quedaron como legado de compatibilidad.

Tablas de este bloque que siguen siendo parte del dominio activo:

- `interpretes`
- `albunes`
- `albunes_canciones`
- `canciones`
- `festivales`
- `mitos`
- `comidas`
- `provincias`
- `meses`
- `categorias`

Tablas de este bloque que hoy son legacy o de transición:

- `noticias`
- `shows`

### Prefijo `2025_11_26_194816_*`

Objetivo:

- agregar foreign keys sobre parte del bloque anterior

Archivos:

- `add_foreign_keys_to_canciones_table`
- `add_foreign_keys_to_comidas_table`
- `add_foreign_keys_to_mitos_table`
- `add_foreign_keys_to_model_has_permissions_table`
- `add_foreign_keys_to_model_has_roles_table`
- `add_foreign_keys_to_noticias_table`
- `add_foreign_keys_to_penias_table`
- `add_foreign_keys_to_radios_table`
- `add_foreign_keys_to_role_has_permissions_table`
- `add_foreign_keys_to_shows_table`

Riesgos heredados:

- algunas estructuras legacy no fueron diseñadas originalmente con el mismo rigor relacional que las nuevas;
- al tocar estas tablas conviene revisar siempre nombres reales de columnas, tipos y restricciones existentes.

---

## 4.3 Primeras ampliaciones funcionales

### `2026_03_20_014528_create_images_table`

Objetivo:

- introducir una tabla de media reutilizable previa a `media_assets`

Estado actual:

- `images` sigue existiendo en la base
- hoy es legacy frente a `media_assets`

### `2026_03_20_204231_make_noticias_foto_nullable`

Objetivo:

- flexibilizar la carga de imágenes en noticias legacy

Estado actual:

- sigue siendo relevante sólo para compatibilidad histórica

### `2026_03_21_172708_add_points_and_rank_to_users_table`

Objetivo:

- agregar gamificación básica a `users`

Campos incorporados:

- `points`
- `rank`

### `2026_03_21_172708_create_contributions_table`

Objetivo:

- registrar contribuciones de usuarios sobre contenidos

Estado actual:

- tabla activa
- sigue un patrón polimórfico (`contributable_type`, `contributable_id`)

### `2026_03_22_041551_create_categories_and_classifieds_tables`

Objetivo:

- crear el módulo de clasificados

Tablas creadas:

- `categories`
- `classifieds`
- `tags`
- `classified_tag`

### `2026_03_23_024951_create_newsletter_subscribers_table`

Objetivo:

- alta de suscriptores newsletter

Estado actual:

- tabla activa y usada por el job de newsletter

---

## 4.4 Modernización editorial y pasarela

### `2026_04_09_031600_add_publisher_fields_to_users_table`

Objetivo:

- convertir `users` en base de publicadores/colaboradores además de admins

Campos incorporados:

- `phone`
- `status`
- `is_verified_publisher`
- `publisher_type_default`
- `last_login_at`

### `2026_04_09_032400_create_organizations_table`

Objetivo:

- crear organizaciones editoriales / institucionales

Estado actual:

- tabla activa
- soporta pasarela, ownership y publicación multicanal

### `2026_04_09_032700_create_organization_members_table`

Objetivo:

- membresías de usuarios dentro de organizaciones

### `2026_04_09_033200_transform_shows_to_events_table`

Objetivo:

- introducir `events` como tabla canónica de cartelera
- mantener una transición ordenada desde `shows`

Resultado funcional:

- `events` es hoy la tabla vigente
- `shows` sigue existiendo como legado
- parte del código conserva aliases de compatibilidad

### `2026_04_09_033230_create_venues_table`

Objetivo:

- modelar sedes/lugares de eventos

Estado actual:

- la tabla existe
- no hay un `Venue` model consolidado hoy
- su uso aplicativo está incompleto

### `2026_04_09_035300_transform_noticias_to_news_table`

Objetivo:

- introducir `news` como tabla canónica de noticias
- conservar compatibilidad con el legado `noticias`

Resultado funcional:

- `news` es hoy la tabla vigente para nuevas altas
- `noticias` continúa presente por compatibilidad histórica

### `2026_04_09_035700_transform_images_to_media_assets_table`

Objetivo:

- sustituir `images` por `media_assets`
- llevar media a una estructura polimórfica más completa

Resultado funcional:

- `media_assets` es la tabla canónica actual
- `images` sigue existiendo
- el modelo `Image` quedó como wrapper de compatibilidad

### `2026_04_09_040200_make_profile_nullable_in_media_assets`

Objetivo:

- flexibilizar la persistencia de perfiles/variantes de media

### `2026_04_09_042000_create_event_interprete_table`

Objetivo:

- relación N:M entre `events` e `interpretes`

Estado actual:

- pivot activa del módulo de cartelera

### `2026_04_09_042900_create_moderation_reviews_table`

Objetivo:

- registrar revisiones/moderación editorial

### `2026_04_09_045500_create_social_accounts_table`

Objetivo:

- cuentas sociales conectadas por usuario u organización

### `2026_04_09_050000_create_publication_orchestration_tables`

Objetivo:

- crear la pasarela de publicación multicanal

Tablas creadas:

- `publication_requests`
- `publication_targets`
- `publication_attempts`

### `2026_04_09_053000_create_templates_notifications_audit_tables`

Objetivo:

- completar infraestructura operativa de publicación y trazabilidad

Tablas creadas:

- `publication_templates`
- `notifications` o equivalente inicial
- `audit_logs`

Nota importante:

- luego hubo un rename de `notifications` a `user_notifications`

### `2026_04_09_212730_create_jobs_table`

Objetivo:

- habilitar driver de colas por base de datos

Estado actual:

- la tabla `jobs` existe
- el proyecto aún puede correr con `QUEUE_CONNECTION=sync` según entorno

### `2026_04_14_230000_add_social_provider_ids_to_users_table`

Objetivo:

- login social directo en `users`

Campos relevantes:

- `google_id`
- `facebook_id`

### `2026_04_26_000001_rename_notifications_to_user_notifications`

Objetivo:

- estabilizar naming del módulo de notificaciones internas

Resultado funcional:

- la tabla vigente es `user_notifications`

---

## 4.5 Nuevos módulos funcionales

### `2026_06_10_150000_create_folklore_tournament_tables`

Objetivo:

- crear la infraestructura de torneos

Tablas creadas:

- `folklore_tournaments`
- `folklore_tournament_groups`
- `folklore_tournament_participants`
- `folklore_tournament_matches`

### `2026_08_04_010000_create_knowledge_categories_table`

Objetivo:

- taxonomía jerárquica de Enciclopedia

### `2026_08_04_010100_create_knowledge_articles_table`

Objetivo:

- tabla canónica de artículos evergreen/enciclopédicos

Características:

- SEO dedicado
- autor y revisor
- publicación editorial
- soft deletes

### `2026_08_04_010200_create_knowledge_article_relationship_tables`

Objetivo:

- relaciones N:M de Enciclopedia con el resto del dominio

Pivots creadas:

- `knowledge_article_interprete`
- `knowledge_article_cancion`
- `knowledge_article_album`
- `knowledge_article_festival`
- `event_knowledge_article`
- `knowledge_article_provincia`
- `knowledge_article_related`

---

## 4.6 Festivales y performance

### `2026_08_08_120000_restructure_festivales_first_stage`

Objetivo:

- normalizar el módulo de Festivales
- introducir `localities`
- formalizar relaciones con provincias y otras entidades

Estado actual:

- parte clave de la reestructuración ya está versionada
- en producción puede requerir especial verificación de foreign keys

Riesgo conocido:

- la FK `localities.province_id -> provincias.id` puede fallar si el estado previo de la tabla no coincide con lo esperado por la migración

### `2026_08_09_120000_add_performance_indexes`

Objetivo:

- primera tanda de índices para bajar tiempos de respuesta en frontend y backend

### `2026_08_09_140000_add_secondary_performance_indexes`

Objetivo:

- segunda pasada de índices sobre consultas secundarias y relaciones

### `2026_08_09_150000_add_content_section_indexes`

Objetivo:

- índices específicos para secciones de contenido y listados públicos

Resultado conjunto:

- mejoraron sensiblemente los tiempos desktop
- siguen existiendo oportunidades de mejora mobile fuera del plano estrictamente SQL

### `2026_08_20_120000_create_data_deletion_requests_table`

Objetivo:

- registrar solicitudes de eliminacion o desvinculacion de datos iniciadas desde el flujo legal/Meta-Facebook.

Tabla creada:

- `data_deletion_requests`

Impacto actual:

- da trazabilidad local a callbacks firmados de `POST /deleteuserdata`
- respalda el estado publico por `confirmation_code`
- forma parte del cumplimiento operativo aunque no del dominio editorial

---

## 5. Mapa por área funcional

## 5.1 Autenticación e infraestructura

- `create_users_table`
- `create_sessions_table`
- `create_password_resets_table`
- `create_personal_access_tokens_table`
- `create_jobs_table`
- `create_failed_jobs_table`

## 5.2 Permisos y roles

- `create_permissions_table`
- `create_roles_table`
- `create_model_has_permissions_table`
- `create_model_has_roles_table`
- `create_role_has_permissions_table`
- migraciones de FK asociadas

## 5.3 Contenido legacy del portal

- `create_interpretes_table`
- `create_albunes_table`
- `create_canciones_table`
- `create_albunes_canciones_table`
- `create_album_interprete_table`
- `create_noticias_table`
- `create_shows_table`
- `create_festivales_table`
- `create_mitos_table`
- `create_comidas_table`
- `create_penias_table`
- `create_radios_table`
- `create_provincias_table`
- `create_meses_table`
- `create_categorias_table`

## 5.4 Modernización editorial

- `transform_shows_to_events_table`
- `transform_noticias_to_news_table`
- `transform_images_to_media_assets_table`
- `create_event_interprete_table`
- `create_venues_table`

## 5.5 Pasarela de contenidos

- `add_publisher_fields_to_users_table`
- `create_organizations_table`
- `create_organization_members_table`
- `create_social_accounts_table`
- `create_publication_orchestration_tables`
- `create_templates_notifications_audit_tables`

## 5.6 Nuevos módulos

- `create_contributions_table`
- `create_categories_and_classifieds_tables`
- `create_newsletter_subscribers_table`
- `create_folklore_tournament_tables`
- `create_knowledge_categories_table`
- `create_knowledge_articles_table`
- `create_knowledge_article_relationship_tables`
- `restructure_festivales_first_stage`
- `create_data_deletion_requests_table`

## 5.7 Performance

- `add_performance_indexes`
- `add_secondary_performance_indexes`
- `add_content_section_indexes`

---

## 6. Compatibilidades y deudas vigentes

## 6.1 Tablas legacy que siguen existiendo

- `noticias`
- `shows`
- `images`

Motivo:

- compatibilidad histórica
- transición progresiva del código
- preservación de datos previos

## 6.2 Tablas activas con naming histórico

- `albunes`
- `albunes_canciones`
- `categorias`
- `interprete_noticia`

Motivo:

- forman parte de la convención real del proyecto
- no deben renombrarse “por prolijidad” sin una migración mayor aprobada

## 6.3 Infraestructura creada pero no totalmente consolidada

- `venues`
- parte del flujo de publicación multicanal
- compatibilidad residual con tablas legacy aún presentes

---

## 7. Reglas para futuras migraciones

- No eliminar tablas legacy en una migración aislada sin antes auditar referencias en modelos, controladores, vistas, tests, jobs y API.
- No renombrar `albunes`, `albunes_canciones` ni otras tablas históricas sólo por convención lingüística.
- Toda nueva migración sobre módulos legacy debe verificar:
  - tipo de columna real,
  - nulabilidad,
  - valores existentes,
  - compatibilidad con producción.
- Si una migración introduce una tabla canónica nueva, el cambio debe completarse también en:
  - modelos,
  - queries,
  - relaciones,
  - validaciones,
  - tests,
  - documentación.
- Para tablas con alta lectura pública, pensar índices desde el diseño inicial.

---

## 8. Riesgos operativos observados

- Las migraciones antiguas no siempre responden a una convención homogénea de nombres o relaciones.
- Los entornos productivos pueden arrastrar diferencias históricas respecto de local.
- Hay que prestar especial atención a:
  - foreign keys sobre tablas viejas,
  - collations/tipos distintos,
  - datos huérfanos,
  - columnas que existen en un entorno y en otro no.

---

## 9. Resumen ejecutivo

Las migraciones del proyecto ya no representan un MVP hipotético sino una evolución real del portal:

- base legacy amplia;
- modernización parcial pero importante en noticias, eventos y media;
- pasarela editorial y social versionada;
- Enciclopedia y Festivales con estructura propia;
- varias tandas recientes de índices por performance.

Quien trabaje sobre este repositorio debe asumir una estrategia de **evolución segura sobre un esquema híbrido**, no una reescritura desde cero.

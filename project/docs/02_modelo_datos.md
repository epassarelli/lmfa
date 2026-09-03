# 02 - Modelo de Datos

> Estado real del modelo de datos al **2026-08-20**.  
> Este documento describe el esquema que hoy existe y usa la aplicacion, distinguiendo entre tablas canónicas, tablas legacy, pivots y tablas de infraestructura.

---

## 1. Criterios de lectura

- La aplicacion no sigue una traduccion total al ingles ni una normalizacion completa de nombres.
- Conviven tablas canonicas nuevas (`news`, `events`, `media_assets`) con tablas legacy que siguen existiendo en BD (`noticias`, `shows`, `images`).
- En varios modulos se mantiene compatibilidad de codigo mediante accessors y aliases, pero **las consultas nuevas deben usar nombres canonicos reales**.
- Este documento prioriza:
  1. tablas efectivamente presentes en la base,
  2. modelos efectivamente presentes en `app/Models`,
  3. relaciones realmente implementadas en el codigo.

---

## 2. Mapa general del dominio

### 2.1 Contenido principal

- `news`: noticias del portal
- `events`: eventos / cartelera
- `interpretes`: artistas / interpretes
- `albunes`: discografia
- `canciones`: letras / canciones
- `festivales`: silo evergreen de festivales
- `mitos`: mitos y leyendas
- `comidas`: recetas de comidas tipicas
- `knowledge_categories` + `knowledge_articles`: enciclopedia

### 2.2 Clasificados

- `classifieds`
- `categories`
- `tags`
- `classified_tag`

### 2.3 Pasarela y colaboraciones

- `organizations`
- `organization_members`
- `social_accounts`
- `publication_requests`
- `publication_targets`
- `publication_attempts`
- `publication_templates`
- `contributions`
- `moderation_reviews`
- `audit_logs`
- `user_notifications`
- `newsletter_subscribers`
- `data_deletion_requests`

### 2.4 Torneos folkloricos

- `folklore_tournaments`
- `folklore_tournament_groups`
- `folklore_tournament_participants`
- `folklore_tournament_matches`

### 2.5 Geografia y taxonomias auxiliares

- `provincias`
- `localities`
- `meses`
- `categorias` (categorias editoriales de noticias)

### 2.6 Media

- tabla canonica: `media_assets`
- tabla legacy: `images`

### 2.7 Tablas legacy todavia presentes

- `noticias`
- `shows`
- `images`

### 2.8 Tablas de infraestructura / framework

- `users`
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`
- `personal_access_tokens`
- `sessions`
- `jobs`
- `failed_jobs`
- `migrations`
- `password_resets`

---

## 3. Entidades canonicas y tablas reales

## 3.1 Usuarios y permisos

### `users` -> `User`

Campos relevantes:

- `id`
- `name`
- `email`
- `password`
- `google_id`
- `facebook_id`
- `phone`
- `status`
- `is_verified_publisher`
- `publisher_type_default`
- `last_login_at`
- `points`
- `rank`
- timestamps

Notas:

- Es la tabla base de autenticacion.
- Usa Sanctum (`HasApiTokens`) y Spatie Permission (`HasRoles`).
- Se usa tanto para admins como para publicadores/colaboradores.

Relaciones implementadas:

- `hasMany(Contribution)`
- `morphMany(SocialAccount, owner)`
- `hasMany(PublicationRequest, requested_by)`

### ACL

Tablas reales:

- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

Notas:

- Son tablas operativas del sistema de permisos.
- No deben tratarse como detalle accesorio: afectan backend, API y moderacion.

---

## 3.2 Organizaciones y pasarela

### `organizations` -> `Organization`

Campos relevantes:

- `id`
- `type`
- `name`
- `slug`
- `legal_name`
- `bio_short`
- `bio_long`
- `description`
- `website`
- `email`
- `phone`
- `province_id`
- `city`
- `address`
- `logo_path`
- `banner_path`
- `logo_media_id`
- `cover_media_id`
- `social_links`
- `is_verified`
- `status`
- `created_by`
- timestamps

Relaciones implementadas:

- `belongsTo(User, created_by)`
- `hasMany(OrganizationMember)`
- `morphMany(SocialAccount, owner)`

### `organization_members` -> `OrganizationMember`

Campos:

- `id`
- `organization_id`
- `user_id`
- `role`
- `status`
- `invited_at`
- `accepted_at`
- timestamps

Uso:

- membresia de usuarios dentro de organizaciones
- soporte para flujos de pasarela/publicacion

### `social_accounts` -> `SocialAccount`

Campos:

- `id`
- `owner_type`
- `owner_id`
- `provider`
- `account_name`
- `account_external_id`
- `page_or_profile_name`
- `token_encrypted`
- `refresh_token_encrypted`
- `token_expires_at`
- `scopes_json`
- `status`
- `last_checked_at`
- timestamps

Notas:

- Es una relacion polimorfica.
- Un social account puede pertenecer a `User` u `Organization`.

### `publication_requests` -> `PublicationRequest`

Campos:

- `id`
- `content_type`
- `content_id`
- `requested_by`
- `mode`
- `wants_portal_publish`
- `wants_portal_social`
- `wants_own_social`
- `scheduled_at`
- `reminder_policy`
- `status`
- timestamps

Notas:

- Modela la intencion de publicar/distribuir contenido.
- `content_type` y `content_id` forman una relacion polimorfica.

### `publication_targets` -> `PublicationTarget`

Campos:

- `id`
- `publication_request_id`
- `provider`
- `social_account_id`
- `destination_type`
- `template_variant`
- `scheduled_at`
- `status`
- `priority`
- timestamps

### `publication_attempts` -> `PublicationAttempt`

Campos:

- `id`
- `publication_target_id`
- `attempt_number`
- `started_at`
- `finished_at`
- `request_payload_json`
- `response_payload_json`
- `external_post_id`
- `external_url`
- `status`
- `error_code`
- `error_message`
- `is_retryable`
- timestamps

### `publication_templates` -> `PublicationTemplate`

Campos:

- `id`
- `content_type`
- `provider`
- `variant_name`
- `template_text`
- `is_active`
- timestamps

---

## 3.3 Noticias

### Tabla canonica: `news` -> `News`

Campos relevantes:

- `id`
- `organization_id`
- `title`
- `slug`
- `subtitle`
- `excerpt`
- `body`
- `news_type`
- `editorial_status`
- `publication_mode`
- `featured_image_id`
- `featured_image_path`
- `seo_title`
- `meta_description`
- `approved_by`
- `approved_at`
- `published_at`
- `created_by`
- `estado` (compatibilidad)
- `interprete_id`
- `categoria_id`
- `visitas`
- timestamps

Notas clave:

- Es la tabla canónica para noticias nuevas y vigentes.
- Mantiene compatibilidad con nombres legacy via accessors:
  - `titulo` -> `title`
  - `noticia` -> `body`
  - `foto` -> `featured_image_path`
  - `user_id` -> `created_by`
  - `publicar` -> `published_at`
  - `estado` <-> `editorial_status`

Relaciones implementadas:

- `belongsTo(Organization)`
- `belongsTo(User, created_by)`
- `belongsTo(Categoria, categoria_id)`
- `belongsTo(Interprete, interprete_id)` como interprete principal
- `belongsToMany(Interprete, interprete_noticia)` como interpretes secundarios
- `belongsToMany(Festival, festival_news)`

### Tabla legacy: `noticias` -> `Noticia`

Notas:

- Sigue existiendo en BD.
- No debe usarse para contenido nuevo.
- Mantiene valor historico y compatibilidad residual.

### Categoria editorial de noticias: `categorias` -> `Categoria`

Campos:

- `id`
- `nombre`
- `slug`
- `foto`
- `metetittle`
- `metadescription`
- `status`

Notas:

- Aunque el modulo de clasificados usa `categories`, las noticias siguen dependiendo de `categorias`.
- Hay convivencia de ambas taxonomias.

### Pivot legacy de artistas/noticias: `interprete_noticia`

Campos:

- `id`
- `interprete_id`
- `noticia_id`

Notas:

- Sigue siendo parte del modelo real.
- Convive con `news.interprete_id`.
- Se usa para relaciones N:M historicas y compatibilidad.

---

## 3.4 Eventos / cartelera

### Tabla canonica: `events` -> `Event`

Campos relevantes:

- `id`
- `organization_id`
- `venue_id`
- `title`
- `subtitle`
- `excerpt`
- `body`
- `event_type`
- `modality`
- `slug`
- `start_at`
- `end_at`
- `timezone`
- `province_id`
- `city`
- `address`
- `latitude`
- `longitude`
- `ticket_url`
- `price_text`
- `is_free`
- `capacity`
- `status`
- `editorial_status`
- `publication_mode`
- `featured_image_id`
- `featured_image_path`
- `seo_title`
- `meta_description`
- `approved_by`
- `approved_at`
- `published_at`
- `created_by`
- campos legacy de compatibilidad:
  - `show`
  - `detalles`
- timestamps

Notas clave:

- Es la tabla canónica para cartelera.
- Conserva accessors de compatibilidad con `shows`.
- `status` y `editorial_status` conviven.

Relaciones implementadas:

- `belongsTo(Organization)`
- `belongsTo(User, created_by)`
- `belongsTo(Provincia, province_id)`
- `belongsToMany(Interprete, event_interprete)`
- `belongsToMany(Festival, event_festival)`
- `belongsToMany(KnowledgeArticle, event_knowledge_article)` desde el lado de enciclopedia

### Tabla legacy: `shows` -> `Show`

Notas:

- Sigue existiendo en BD.
- No debe usarse para nuevas altas.
- El sistema actual trabaja sobre `events`.

### `venues`

Notas:

- La tabla existe en BD.
- Hoy no existe `Venue` model en `app/Models`.
- Por eso debe considerarse infraestructura de dominio incompleta / pendiente de auditoria, no entidad plenamente cerrada.

### Pivot `event_interprete`

Campos:

- `event_id`
- `interprete_id`
- `role`
- `sort_order`

### Pivot `event_festival`

Campos:

- `festival_id`
- `event_id`
- timestamps

---

## 3.5 Interpretes

### `interpretes` -> `Interprete`

Campos relevantes:

- `id`
- `telefono`
- `correo`
- `facebook`
- `youtube`
- `twitter`
- `instagram`
- `interprete`
- `slug`
- `biografia`
- `foto`
- `user_id`
- `visitas`
- `estado`
- timestamps

Relaciones implementadas:

- `hasMany(Album)`
- `hasMany(Cancion)`
- `belongsToMany(Festival, festival_interprete)`
- `morphMany(Image, imageable)` via compatibilidad con media
- relaciones con noticias y enciclopedia

Notas:

- No existe tabla `artists`.
- El equivalente real del dominio artistico hoy es `interpretes`.

---

## 3.6 Discografia

### `albunes` -> `Album`

Campos relevantes:

- `id`
- `interprete_id`
- `publicar`
- `album`
- `slug`
- `spotify`
- `anio`
- `foto`
- `visitas`
- `user_id`
- `estado`
- timestamps

Notas:

- `albunes` es nombre vigente del proyecto y **no debe renombrarse**.

### Pivot `album_interprete`

Campos:

- `albu_id`
- `inte_id`

Notas:

- Existe en BD.
- Responde a una convencion legacy propia del proyecto.

---

## 3.7 Canciones

### `canciones` -> `Cancion`

Campos relevantes:

- `id`
- `cancion`
- `slug`
- `letra`
- `youtube`
- `spotify`
- `interprete_id`
- `user_id`
- `visitas`
- `publicar`
- `estado`
- timestamps

### Pivot `albunes_canciones`

Campos:

- `album_id`
- `cancion_id`
- `orden`

Notas:

- Relaciona canciones con discos.
- El nombre vigente es `albunes_canciones` y no debe renombrarse.

---

## 3.8 Festivales

### `festivales` -> `Festival`

Campos relevantes:

- `id`
- `mes_id`
- `slug`
- `title`
- `excerpt`
- `body`
- `featured_image_id`
- `featured_image_path`
- `province_id`
- `locality_id`
- `seo_title`
- `meta_description`
- `status`
- `published_at`
- `visitas`
- `user_id`
- timestamps

Relaciones implementadas:

- `belongsTo(User)`
- `belongsTo(Provincia, province_id)`
- `belongsTo(Locality, locality_id)`
- `belongsTo(Mes, mes_id)`
- `belongsToMany(News, festival_news)`
- `belongsToMany(Event, event_festival)`
- `belongsToMany(Interprete, festival_interprete)`
- `belongsToMany(KnowledgeArticle, knowledge_article_festival)`

### Pivot `festival_news`

- `festival_id`
- `news_id`
- timestamps

### Pivot `festival_interprete`

- `festival_id`
- `interprete_id`
- timestamps

---

## 3.9 Mitos y recetas

### `mitos` -> `Mito`

Campos:

- `id`
- `titulo`
- `slug`
- `mito`
- `foto`
- `publicar`
- `user_id`
- `visitas`
- `estado`
- timestamps

### `comidas` -> `Comida`

Campos:

- `id`
- `titulo`
- `slug`
- `receta`
- `foto`
- `publicar`
- `user_id`
- `visitas`
- `estado`
- timestamps

Notas:

- Ambos modulos siguen usando nomenclatura heredada en espanol.
- Ambos tienen media asociada y optimizaciones recientes por rendimiento.

---

## 3.10 Enciclopedia

### `knowledge_categories` -> `KnowledgeCategory`

Campos:

- `id`
- `parent_id`
- `name`
- `slug`
- `description`
- `sort_order`
- `is_active`
- `seo_title`
- `meta_description`
- timestamps

Notas:

- Soporta jerarquia por `parent_id`.

### `knowledge_articles` -> `KnowledgeArticle`

Campos:

- `id`
- `knowledge_category_id`
- `title`
- `slug`
- `excerpt`
- `body`
- `featured_image_id`
- `featured_image_path`
- `image_alt`
- `seo_title`
- `meta_description`
- `primary_keyword`
- `secondary_keywords`
- `editorial_status`
- `published_at`
- `last_verified_at`
- `author_id`
- `reviewed_by`
- `visits`
- timestamps
- `deleted_at` (soft deletes)

Relaciones implementadas:

- `belongsTo(KnowledgeCategory)`
- `belongsTo(User, author_id)`
- `belongsTo(User, reviewed_by)`
- `belongsToMany(Interprete, knowledge_article_interprete)`
- `belongsToMany(Cancion, knowledge_article_cancion)`
- `belongsToMany(Album, knowledge_article_album)`
- `belongsToMany(Festival, knowledge_article_festival)`
- `belongsToMany(Event, event_knowledge_article)`
- `belongsToMany(Provincia, knowledge_article_provincia)`
- `belongsToMany(KnowledgeArticle, knowledge_article_related)` como enlazado interno

### Pivots de enciclopedia

- `knowledge_article_interprete`
- `knowledge_article_cancion`
- `knowledge_article_album`
- `knowledge_article_festival`
- `event_knowledge_article`
- `knowledge_article_provincia`
- `knowledge_article_related`

Todas tienen estructura simple de FK + timestamps.

---

## 3.11 Clasificados

### `classifieds` -> `Classified`

Campos relevantes:

- `id`
- `user_id`
- `category_id`
- `title`
- `slug`
- `description`
- `price`
- `location`
- `contact_info`
- `contact_whatsapp`
- `expiration_date`
- `is_active`
- `is_featured`
- `estado`
- `moderator_comment`
- timestamps

Relaciones implementadas:

- `belongsTo(Category)`
- `belongsTo(User)`
- `belongsToMany(Tag, classified_tag)`

Notas:

- Convivencia de `is_active` y `estado`.
- El estado operativo real suele pasar por `estado`.

### `categories` -> `Category`

Campos:

- `id`
- `name`
- `slug`
- `icon`
- timestamps

### `tags` -> `Tag`

Campos:

- `id`
- `name`
- `slug`
- timestamps

### Pivot `classified_tag`

- `classified_id`
- `tag_id`

Nota:

- Existe modelo `ClassifiedTag`, pero su funcion es la de una pivot.

---

## 3.12 Torneos folkloricos

### `folklore_tournaments` -> `FolkloreTournament`

Campos:

- `id`
- `name`
- `slug`
- `description`
- `year`
- `starts_at`
- `ends_at`
- `status`
- `rules`
- timestamps

### `folklore_tournament_groups` -> `FolkloreTournamentGroup`

Campos:

- `id`
- `tournament_id`
- `name`
- `slug`
- `sort_order`
- timestamps

### `folklore_tournament_participants` -> `FolkloreTournamentParticipant`

Campos:

- `id`
- `tournament_id`
- `group_id`
- `artist_id`
- `display_name`
- `slug`
- `image_path`
- `seed_order`
- `status`
- timestamps

### `folklore_tournament_matches` -> `FolkloreTournamentMatch`

Campos:

- `id`
- `tournament_id`
- `group_id`
- `phase`
- `matchday`
- `participant_1_id`
- `participant_2_id`
- `participant_1_votes`
- `participant_2_votes`
- `winner_participant_id`
- `status`
- `scheduled_at`
- `voting_opens_at`
- `voting_closes_at`
- `instagram_url`
- `notes`
- timestamps

---

## 3.13 Geografia y navegacion

### `provincias` -> `Provincia`

Campos:

- `id`
- `nombre`
- timestamps

### `localities` -> `Locality`

Campos:

- `id`
- `province_id`
- `name`
- `slug`
- timestamps

### `meses` -> `Mes`

Campos:

- `id`
- `nombre`
- timestamps

---

## 3.14 Media

### Tabla canonica: `media_assets` -> `MediaAsset`

Campos relevantes:

- `id`
- `imageable_type`
- `imageable_id`
- `profile`
- `original_path`
- `variants_json`
- `alt`
- `sort_order`
- `original_width`
- `original_height`
- `mime`
- `disk`
- `original_name`
- `size`
- `caption`
- `group`
- `created_by`
- timestamps

Notas:

- Es la tabla canónica actual para media.
- La relacion es polimorfica (`imageable_type` / `imageable_id`).
- Guarda variantes optimizadas webp en `variants_json`.

### Compatibilidad: `Image` extiende `MediaAsset`

- El modelo `Image` existe como wrapper de compatibilidad.
- No implica que `images` siga siendo la tabla principal.

### Tabla legacy: `images`

Campos:

- `id`
- `imageable_type`
- `imageable_id`
- `profile`
- `original_path`
- `variants_json`
- `alt`
- `sort_order`
- `original_width`
- `original_height`
- `mime`
- timestamps

Notas:

- Existe por legado.
- Fue reemplazada por `media_assets`.

---

## 3.15 Contribuciones, moderacion y auditoria

### `contributions` -> `Contribution`

Campos:

- `id`
- `user_id`
- `contributable_type`
- `contributable_id`
- `payload`
- `status`
- `moderator_comment`
- timestamps

Notas:

- Relacion polimorfica hacia el contenido contribuido.
- `payload` se castea a array en el modelo.

### `moderation_reviews` -> `ModerationReview`

Campos:

- `id`
- `content_type`
- `content_id`
- `reviewer_user_id`
- `action`
- `comments`
- timestamps

### `audit_logs` -> `AuditLog`

Campos:

- `id`
- `user_id`
- `action`
- `entity_type`
- `entity_id`
- `old_values`
- `new_values`
- `ip_address`
- `user_agent`
- `created_at`

### `user_notifications` -> `UserNotification`

Campos:

- `id`
- `user_id`
- `type`
- `title`
- `body`
- `action_url`
- `is_read`
- `read_at`
- timestamps

### `newsletter_subscribers` -> `NewsletterSubscriber`

Campos:

- `id`
- `user_id`
- `email`
- `name`
- `status`
- `token`
- `source`
- `unsubscribed_at`
- timestamps

### `data_deletion_requests` -> `DataDeletionRequest`

Campos:

- `id`
- `provider`
- `provider_user_id`
- `confirmation_code`
- `status`
- `signed_request_hash`
- `request_payload`
- `processed_at`
- timestamps

Notas:

- Se usa para el flujo legal y de cumplimiento Meta/Facebook.
- Da trazabilidad a solicitudes de borrado o desvinculacion iniciadas por `POST /deleteuserdata`.
- No forma parte del dominio editorial, pero si del dominio operativo y legal del portal.

---

## 3.16 Tablas existentes con uso parcial o no consolidado

### `radios` -> `Radio`

Campos:

- `id`
- `titulo`
- `slug`
- `detalle`
- `foto`
- `escucharOnline`
- `user_id`
- `visitas`
- `publicar`
- `estado`
- timestamps

Estado:

- Existe modelo y frontend publico.
- No esta consolidado como modulo administrativo completo.

### `penias` -> `Penia`

Campos:

- `id`
- `titulo`
- `slug`
- `detalle`
- `foto`
- `user_id`
- `visitas`
- `publicar`
- `estado`
- timestamps

Estado historico:

- Retirado el 2026-09-03 por autorizacion explicita: la tabla estaba vacia y su modelo, rutas y vistas no tenian uso funcional.
- El contrato vigente es `penia_profiles` -> `PeniaProfile`; no conserva FK ni dependencia sobre `penias`.

### `venues`

Estado:

- Existe en la base y fue creada en la transformacion hacia `events`.
- No hay `Venue` model actual en `app/Models`.
- Requiere auditoria antes de considerarla entidad plenamente activa.

---

## 4. Relaciones conceptuales reales

- Un `User` puede crear organizaciones, eventos, noticias, contributions y solicitudes de publicacion.
- Una `Organization` puede tener muchos `OrganizationMember` y muchas `SocialAccount`.
- Un `Event` puede relacionarse con muchos `Interprete`.
- Un `Festival` puede relacionarse con muchos `Interprete`, muchos `Event` y muchas `News`.
- Una `News` puede tener un `Interprete` principal y varios secundarios via `interprete_noticia`.
- Un `KnowledgeArticle` puede relacionarse con artistas, canciones, discos, festivales, eventos, provincias y otros articulos.
- Un `Classified` pertenece a una `Category` y puede tener muchos `Tag`.
- Un `PublicationRequest` genera multiples `PublicationTarget`.
- Un `PublicationTarget` genera multiples `PublicationAttempt`.
- Un `MediaAsset` puede pertenecer de forma polimorfica a distintas entidades de contenido.

---

## 5. Enumeraciones y estados observados

## 5.1 Estados editoriales modernos

Se observan especialmente en `news`, `events` y `knowledge_articles`:

- `draft`
- `pending_review`
- `approved`
- `published`

Pueden existir otros estados segun el modulo o flujo.

## 5.2 Estados legacy / simples

Muchos modulos heredados siguen usando:

- `estado` entero (`0/1`)
- `status` string (`active`, `pending`, `published`, etc.)
- `publicar` / `published_at` / `start_at` como fecha de publicacion efectiva

Conclusión:

- El proyecto **no tiene un unico criterio fisico de publicacion** a nivel de BD.
- La unificacion actual se resuelve desde modelos, accessors, scopes y servicios.

---

## 6. Canonico vs legacy

## 6.1 Canonico actual

- `news`
- `events`
- `media_assets`
- `knowledge_categories`
- `knowledge_articles`

## 6.2 Legacy aun presente

- `noticias`
- `shows`
- `images`

## 6.3 Legacy con convivencia funcional

- `categorias`
- `albunes`
- `albunes_canciones`
- `interprete_noticia`

Estas tablas no son “errores” del proyecto: forman parte de la convencion real actual y deben tratarse con compatibilidad, no con renombre automatico.

---

## 7. Consideraciones tecnicas para futuros cambios

- No asumir equivalencia 1:1 entre nombres en ingles y tablas reales.
- No renombrar `albunes`, `albunes_canciones`, `categorias` ni otras tablas historicas sin una migracion de alto impacto aprobada.
- No usar `noticias`, `shows` o `images` para contenido nuevo.
- Para noticias y eventos, usar siempre columnas canonicas en queries:
  - `news.title`, `news.body`, `news.editorial_status`, `news.published_at`
  - `events.title`, `events.body`, `events.editorial_status`, `events.start_at`
- Verificar siempre si una relacion esta soportada por pivot real o solo por compatibilidad de modelo.
- Distinguir claramente:
  - tablas de dominio,
  - pivots funcionales,
  - tablas legacy,
  - tablas de infraestructura.

---

## 8. Resumen ejecutivo

El modelo de datos real del proyecto hoy es **hibrido**:

- parcialmente modernizado para `news`, `events`, `media_assets`, pasarela, enciclopedia y torneos;
- parcialmente heredado en `interpretes`, `albunes`, `canciones`, `mitos`, `comidas`, `categorias`, `radios`, `penias`;
- con tablas legacy todavia presentes para compatibilidad historica.

La documentacion futura debe partir de esta realidad y no de una propuesta teorica idealizada.

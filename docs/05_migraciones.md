# Migraciones y tablas Laravel

## 1. Objetivo
Definir la base inicial de migraciones para implementar el MVP del módulo de Pasarela de Contenidos y Distribución Multicanal en Laravel.

## 2. Convenciones recomendadas
- tablas en plural y snake_case
- claves foráneas con sufijo _id
- índices por estado, owner, provider y fechas programadas
- soft deletes en entidades operativas relevantes si el negocio lo requiere
- timestamps en todas las tablas principales
- JSON sólo cuando aporte flexibilidad real

## 3. Orden sugerido de migraciones
1. users ajustes
2. organizations
3. organization_members
4. artists
5. venues
6. media_assets
7. media_relations
8. events
9. event_artists
10. news
11. social_accounts
12. publication_requests
13. publication_targets
14. publication_attempts
15. moderation_reviews
16. publication_templates
17. notifications
18. audit_logs

## 4. Migraciones base
### 4.1 users (ajustes)
Agregar:
- phone string nullable
- status string default active
- is_verified_publisher boolean default false
- publisher_type_default string nullable
- last_login_at timestamp nullable

### 4.2 organizations
Campos:
- id
- type string
- name string
- slug string unique
- legal_name string nullable
- bio_short text nullable
- bio_long longText nullable
- website string nullable
- email string nullable
- phone string nullable
- province_id unsignedBigInteger nullable
- city string nullable
- address string nullable
- logo_media_id unsignedBigInteger nullable
- cover_media_id unsignedBigInteger nullable
- is_verified boolean default false
- status string default active
- created_by unsignedBigInteger nullable
- timestamps

### 4.3 events
Campos principales:
- organization_id
- venue_id nullable
- title
- slug
- subtitle
- excerpt
- body
- event_type
- modality
- start_at
- end_at
- province_id
- city
- address
- ticket_url
- price_text
- is_free
- status
- editorial_status
- publication_mode
- featured_image_id
- seo_title
- meta_description
- approved_by
- approved_at
- published_at
- created_by

### 4.4 news
Campos principales:
- organization_id
- artist_id nullable
- related_event_id nullable
- title
- slug
- excerpt
- body
- source_name
- source_url
- author_name
- category_id
- status
- editorial_status
- publication_mode
- featured_image_id
- seo_title
- meta_description
- approved_by
- approved_at
- published_at
- created_by

### 4.5 social_accounts
- owner_type
- owner_id
- provider
- account_name
- account_external_id
- token_encrypted
- refresh_token_encrypted
- token_expires_at
- scopes_json
- status

### 4.6 publication_requests
- content_type
- content_id
- requested_by
- mode
- wants_portal_publish
- wants_portal_social
- wants_own_social
- scheduled_at
- reminder_policy
- status

### 4.7 publication_targets
- publication_request_id
- provider
- social_account_id
- destination_type
- template_variant
- scheduled_at
- status
- priority

### 4.8 publication_attempts
- publication_target_id
- attempt_number
- started_at
- finished_at
- request_payload_json
- response_payload_json
- external_post_id
- external_url
- status
- error_code
- error_message
- is_retryable

## 5. Foreign keys recomendadas
- organizations.created_by -> users.id
- organization_members.organization_id -> organizations.id
- organization_members.user_id -> users.id
- artists.organization_id -> organizations.id
- media_assets.uploaded_by -> users.id
- events.organization_id -> organizations.id
- events.venue_id -> venues.id
- event_artists.event_id -> events.id
- event_artists.artist_id -> artists.id
- news.organization_id -> organizations.id
- news.artist_id -> artists.id
- news.related_event_id -> events.id
- publication_requests.requested_by -> users.id
- publication_targets.publication_request_id -> publication_requests.id
- publication_targets.social_account_id -> social_accounts.id
- publication_attempts.publication_target_id -> publication_targets.id
- moderation_reviews.reviewer_user_id -> users.id
- notifications.user_id -> users.id
- audit_logs.user_id -> users.id

## 6. Observaciones de implementación
- media_relations puede ser polimórfica o resolverse con tablas específicas; para MVP la polimórfica es más flexible
- province_id y category_id pueden referenciar catálogos existentes del portal
- content_type y owner_type pueden manejarse con morphs o strings controlados
- si el portal ya posee tablas de artistas, categorías o provincias, integrar en vez de duplicar
- publication_attempts debe ser la fuente principal para debugging y soporte

## 7. Nivel siguiente: propuesta casi implementable para Laravel

## 8. Nombres sugeridos de archivos de migración
- `xxxx_xx_xx_xxxxxx_add_publisher_fields_to_users_table.php`
- `xxxx_xx_xx_xxxxxx_create_organizations_table.php`
- `xxxx_xx_xx_xxxxxx_create_organization_members_table.php`
- `xxxx_xx_xx_xxxxxx_create_artists_table.php`
- `xxxx_xx_xx_xxxxxx_create_venues_table.php`
- `xxxx_xx_xx_xxxxxx_create_media_assets_table.php`
- `xxxx_xx_xx_xxxxxx_create_media_relations_table.php`
- `xxxx_xx_xx_xxxxxx_create_events_table.php`
- `xxxx_xx_xx_xxxxxx_create_event_artists_table.php`
- `xxxx_xx_xx_xxxxxx_create_news_table.php`
- `xxxx_xx_xx_xxxxxx_create_social_accounts_table.php`
- `xxxx_xx_xx_xxxxxx_create_publication_requests_table.php`
- `xxxx_xx_xx_xxxxxx_create_publication_targets_table.php`
- `xxxx_xx_xx_xxxxxx_create_publication_attempts_table.php`
- `xxxx_xx_xx_xxxxxx_create_moderation_reviews_table.php`
- `xxxx_xx_xx_xxxxxx_create_publication_templates_table.php`
- `xxxx_xx_xx_xxxxxx_create_notifications_table.php`
- `xxxx_xx_xx_xxxxxx_create_audit_logs_table.php`

## 9. Ejemplos simplificados
### 9.1 organizations
```php
Schema::create('organizations', function (Blueprint $table) {
    $table->id();
    $table->string('type', 50);
    $table->string('name');
    $table->string('slug')->unique();
    $table->boolean('is_verified')->default(false);
    $table->string('status', 30)->default('active');
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();

    $table->index('type');
    $table->index('status');
});
```

### 9.2 events
```php
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
    $table->string('title');
    $table->string('slug')->unique();
    $table->timestamp('start_at');
    $table->string('editorial_status', 30)->default('draft');
    $table->string('publication_mode', 40)->default('portal_only');
    $table->timestamps();

    $table->index('start_at');
    $table->index('editorial_status');
});
```

### 9.3 publication_requests
```php
Schema::create('publication_requests', function (Blueprint $table) {
    $table->id();
    $table->string('content_type', 30);
    $table->unsignedBigInteger('content_id');
    $table->foreignId('requested_by')->constrained('users');
    $table->string('mode', 40);
    $table->timestamp('scheduled_at')->nullable();
    $table->string('status', 30)->default('pending');
    $table->timestamps();

    $table->index(['content_type', 'content_id']);
    $table->index('status');
});
```

## 10. Modelos Laravel sugeridos
- User
- Organization
- OrganizationMember
- Artist
- Venue
- Event
- News
- MediaAsset
- SocialAccount
- PublicationRequest
- PublicationTarget
- PublicationAttempt

## 11. Relaciones Eloquent clave
### 11.1 Event
- belongsTo Organization
- belongsToMany Artist
- hasMany PublicationRequest (filtrado por content_type)

### 11.2 PublicationRequest
- hasMany PublicationTarget

### 11.3 PublicationTarget
- hasMany PublicationAttempt

## 12. Índices críticos
- events(start_at)
- publication_targets(status, scheduled_at)
- publication_attempts(publication_target_id)

## 13. Orden sugerido de desarrollo
- iteración 1: core de contenido
- iteración 2: media y relaciones
- iteración 3: social y publicación
- iteración 4: soporte y auditoría

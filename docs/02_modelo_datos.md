# Modelo de datos

## 1. Entidades principales
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
- PublicationTemplate
- ModerationReview
- Notification
- AuditLog

## 2. Relación conceptual
- un usuario puede pertenecer a varias organizaciones
- una organización puede crear eventos y noticias
- un artista puede estar vinculado a una organización o ser independiente
- un evento puede tener uno o varios artistas
- un venue hospeda eventos
- eventos y noticias tienen media asociada
- organizaciones, usuarios o el portal poseen cuentas sociales
- eventos y noticias generan solicitudes de publicación
- cada solicitud genera múltiples destinos
- cada destino genera múltiples intentos
- eventos y noticias pueden tener múltiples revisiones de moderación

## 3. Tablas sugeridas
### 3.1 users
- id
- name
- email
- password
- phone
- status
- is_verified_publisher
- publisher_type_default
- last_login_at
- created_at
- updated_at

### 3.2 organizations
- id
- type
- name
- slug
- legal_name
- bio_short
- bio_long
- website
- email
- phone
- province_id
- city
- address
- logo_media_id
- cover_media_id
- is_verified
- status
- created_by
- created_at
- updated_at

### 3.3 organization_members
- id
- organization_id
- user_id
- role
- status
- created_at
- updated_at

### 3.4 artists
- id
- organization_id nullable
- stage_name
- full_name
- slug
- bio
- province_id
- image_media_id
- status
- created_at
- updated_at

### 3.5 venues
- id
- name
- slug
- description
- address
- province_id
- city
- latitude
- longitude
- phone
- website
- status
- created_at
- updated_at

### 3.6 events
- id
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
- end_at nullable
- timezone
- province_id
- city
- address
- latitude
- longitude
- ticket_url
- price_text
- is_free
- capacity nullable
- status
- editorial_status
- publication_mode
- featured_image_id
- seo_title
- meta_description
- approved_by nullable
- approved_at nullable
- published_at nullable
- created_by
- created_at
- updated_at

### 3.7 event_artists
- id
- event_id
- artist_id
- sort_order

### 3.8 news
- id
- organization_id
- artist_id nullable
- related_event_id nullable
- title
- slug
- excerpt
- body
- source_name nullable
- source_url nullable
- author_name nullable
- category_id nullable
- status
- editorial_status
- publication_mode
- featured_image_id
- seo_title
- meta_description
- approved_by nullable
- approved_at nullable
- published_at nullable
- created_by
- created_at
- updated_at

### 3.9 media_assets
- id
- disk
- path_original
- path_webp
- mime_type
- file_size
- width
- height
- alt_text
- title
- uploaded_by
- status
- created_at
- updated_at

### 3.10 media_relations
- id
- media_asset_id
- related_type
- related_id
- role
- sort_order

### 3.11 social_accounts
- id
- owner_type
- owner_id
- provider
- account_name
- account_external_id
- page_or_profile_name
- token_encrypted
- refresh_token_encrypted nullable
- token_expires_at nullable
- scopes_json
- status
- last_checked_at
- created_at
- updated_at

### 3.12 publication_requests
- id
- content_type
- content_id
- requested_by
- mode
- wants_portal_publish
- wants_portal_social
- wants_own_social
- scheduled_at nullable
- reminder_policy nullable
- status
- created_at
- updated_at

### 3.13 publication_targets
- id
- publication_request_id
- provider
- social_account_id nullable
- destination_type
- template_variant
- scheduled_at nullable
- status
- priority
- created_at
- updated_at

### 3.14 publication_attempts
- id
- publication_target_id
- attempt_number
- started_at
- finished_at nullable
- request_payload_json
- response_payload_json nullable
- external_post_id nullable
- external_url nullable
- status
- error_code nullable
- error_message nullable
- is_retryable
- created_at
- updated_at

### 3.15 moderation_reviews
- id
- content_type
- content_id
- reviewer_user_id
- action
- comments
- created_at

### 3.16 publication_templates
- id
- content_type
- provider
- variant_name
- template_text
- is_active
- created_at
- updated_at

### 3.17 notifications
- id
- user_id
- type
- title
- body
- action_url nullable
- is_read
- created_at
- read_at nullable

### 3.18 audit_logs
- id
- user_id nullable
- entity_type
- entity_id
- action
- old_values_json nullable
- new_values_json nullable
- meta_json nullable
- ip nullable
- user_agent nullable
- created_at

## 4. Enumeraciones recomendadas
### 4.1 publication_mode
- portal_only
- own_social_only
- portal_social_only
- portal_and_own_social
- portal_and_portal_social
- all

### 4.2 editorial_status
- draft
- pending_review
- observed
- approved
- scheduled
- published
- partially_published
- rejected
- archived

### 4.3 target_status
- pending
- queued
- processing
- published
- error
- retrying
- cancelled

## 5. Consideraciones técnicas
- separar intención de publicar de publicación efectiva
- separar target de attempt
- no guardar un único estado global
- soportar publicación parcial
- permitir múltiples reintentos
- reconstruir historial exacto por contenido y por canal
- cifrar tokens y datos sensibles
- prever índices por estado, provider, scheduled_at y owner

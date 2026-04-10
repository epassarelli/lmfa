# Arquitectura técnica por componentes

## 1. Objetivo
Definir la arquitectura técnica del módulo de Pasarela de Contenidos y Distribución Multicanal sobre Laravel, con foco en desacople, trazabilidad, escalabilidad y automatización.

## 2. Principios
- core transaccional dentro de Laravel
- integraciones externas desacopladas por conectores
- ejecución asíncrona mediante colas
- publicación parcial permitida
- trazabilidad completa por contenido y canal
- seguridad sobre credenciales y tokens
- automatizaciones complementarias fuera del core sólo cuando no comprometan la operación principal

## 3. Stack propuesto
### 3.1 Core
- Laravel 10/11
- PHP 8.2+
- MySQL o MariaDB
- Redis
- Laravel Queue
- Laravel Horizon
- Scheduler de Laravel

### 3.2 Media
- almacenamiento local o S3 compatible
- procesamiento de imágenes en cola
- generación de thumbnails y WebP

### 3.3 Integraciones externas
- Meta Graph API para Facebook e Instagram
- Telegram Bot API
- X API opcional según costo
- TikTok Content Posting API en fase posterior
- LinkedIn API en fase posterior
- servicio de email para notificaciones y newsletter

### 3.4 Automatización complementaria
- n8n para flujos secundarios
- webhooks de entrada y salida
- notificaciones internas a Telegram o email

## 4. Componentes del sistema
### 4.1 Frontend público
Responsable de mostrar eventos y noticias publicados, exponer perfiles de artistas y organizaciones y mostrar CTAs de difusión y participación.

### 4.2 Panel de publicadores
Responsable de alta y edición de eventos y noticias, conexión de redes sociales, consulta de estados de publicación y observaciones editoriales.

### 4.3 Panel administrativo
Responsable de moderación, configuración de reglas, gestión de cuentas oficiales del portal, monitoreo operativo, reintentos manuales y configuración de templates y recordatorios.

### 4.4 Módulo de contenido
Responsable de persistencia de eventos y noticias, slugs, SEO básico, relación con artistas, venues y organizaciones y estados editoriales.

### 4.5 Módulo de media
Responsable de subida de archivos, validación de formato y tamaño, optimización de imágenes, thumbnails, WebP y asociación de media al contenido.

### 4.6 Módulo de moderación
Responsable de cola de revisión, aprobar, observar o rechazar, registrar historial de decisiones y soportar autoaprobación por regla.

### 4.7 Módulo de cuentas sociales
Responsable de almacenar cuentas conectadas, manejar OAuth o credenciales por proveedor, cifrar tokens, detectar expiración y permitir reconexión.

### 4.8 Orquestador de publicación
Responsable de recibir la solicitud de publicación, crear targets por canal, resolver templates por canal, despachar jobs a colas y administrar publicación programada.

### 4.9 Conectores por canal
- FacebookConnector
- InstagramConnector
- TelegramConnector
- XConnector
- TikTokConnector
- LinkedInConnector
- NewsletterConnector

Cada conector debe implementar una interfaz común.

### 4.10 Motor de templates y transformaciones
Responsable de generar copy específico por canal, aplicar hashtags, generar variantes de recordatorio y permitir override manual.

### 4.11 Módulo de intentos y auditoría
Responsable de registrar cada intento por canal, guardar payload y respuesta, almacenar ids externos y URLs y permitir trazabilidad y debugging.

### 4.12 Módulo de notificaciones
Responsable de avisar aprobación, rechazo u observación, publicaciones exitosas o con error y tokens vencidos o reconexión requerida.

### 4.13 Scheduler y recordatorios
Responsable de ejecutar publicaciones programadas, disparar recordatorios y recalcular publicaciones futuras si cambia un evento.

### 4.14 Observabilidad
Responsable de logs técnicos, métricas operativas, tablero de errores, control de colas y monitoreo de conectores.

## 5. Flujo técnico resumido
1. El usuario crea o edita contenido.
2. El contenido se guarda en draft o pending_review.
3. El moderador aprueba o el sistema autoaprueba.
4. Se crea una PublicationRequest.
5. Se crean PublicationTargets por canal.
6. El Scheduler o un evento inmediato envía jobs a la cola.
7. Cada job invoca un conector.
8. Cada intento se registra en PublicationAttempts.
9. El sistema actualiza estados del target y del contenido.
10. Se notifican resultados al publicador y al admin.

## 6. Colas sugeridas
- default
- media-processing
- publication-facebook
- publication-instagram
- publication-telegram
- publication-x
- publication-tiktok
- publication-linkedin
- notifications
- analytics

## 7. Patrones recomendados
- Strategy para conectores por canal
- Factory para resolver el conector según provider
- Template Method o servicios dedicados para generar copy
- Domain Events para disparar workflows
- Jobs idempotentes
- reintentos con backoff exponencial

## 8. Seguridad
- cifrado de tokens sociales
- uso de secretos por entorno
- verificación de firmas de webhook
- rate limiting
- políticas de acceso por organización
- auditoría de acciones sensibles

## 9. Estrategia de resiliencia
- un fallo en un canal no bloquea otros
- dead-letter o estado de error recuperable
- reintentos automáticos con máximo configurable
- reintento manual desde panel admin

## 10. Integración con n8n
Usos sugeridos:
- newsletter semanal
- alertas internas a Telegram
- resumen semanal por provincia o artista
- exportes a Google Sheets
- flujos comerciales y marketing

No debería llevar:
- el núcleo transaccional
- la lógica principal de publicación
- el estado maestro de los contenidos

## 11. Roadmap técnico recomendado
### 11.1 Fase 1
- core de contenido
- moderación
- publicación portal
- publication requests / targets / attempts
- Facebook, Instagram y Telegram

### 11.2 Fase 2
- templates avanzados
- recordatorios automáticos
- dashboards operativos
- notificaciones avanzadas

### 11.3 Fase 3
- TikTok / LinkedIn / X
- analytics enriquecido
- monetización
- IA editorial avanzada

# Pasarela de Contenidos y Distribución Multicanal

## 1. Objetivo general
Permitir que artistas, productoras, organizadores, peñas y festivales carguen eventos y noticias desde el portal para publicarlos en el sitio, solicitar difusión en redes del portal, publicar en sus propias redes conectadas y programar automatizaciones.

## 2. Objetivos específicos
- aumentar el volumen y frecuencia de contenido
- reducir el cuello de botella editorial
- construir una red de publicadores externos
- captar contenido local y federal
- convertir el portal en canal de difusión
- habilitar futura monetización por planes o destacadas

## 3. Alcance funcional
### 3.1 Incluye
- alta de usuarios publicadores
- alta y gestión de organizaciones
- carga de eventos
- carga de noticias
- carga de imágenes, flyers y media
- workflow de moderación
- publicación en portal
- publicación automática a redes seleccionadas
- programación de publicaciones
- republicaciones automáticas
- estados por canal
- logs, auditoría y errores
- dashboard operativo
- notificaciones al publicador y al admin

### 3.2 No incluye en MVP
- facturación automática
- app mobile nativa
- publicación de video largo en YouTube
- campañas pagas automatizadas
- scoring predictivo avanzado
- IA generativa compleja en tiempo real para cada paso

## 4. Tipos de usuarios
### 4.1 Administrador general
- gestiona configuración global
- aprueba contenido
- administra redes oficiales
- consulta métricas y errores
- configura reglas

### 4.2 Editor / Moderador
- revisa contenido
- aprueba, observa o rechaza
- corrige textos mínimos
- decide difusión en redes del portal

### 4.3 Artista
- carga noticias y eventos propios
- conecta sus redes
- consulta estado de publicaciones

### 4.4 Productora / Organizador / Peña / Festival
- carga múltiples eventos
- administra perfiles organizacionales
- conecta cuentas sociales
- programa publicaciones

### 4.5 Colaborador / Prensa
- puede cargar noticias o agenda
- sujeto a reglas de aprobación

## 5. Casos de uso principales
- registro de usuario publicador
- alta de organización
- alta de evento
- alta de noticia
- adjuntar media
- selección de canales
- moderación
- publicación multicanal
- seguimiento de estado
- recordatorios automáticos
- notificaciones
- auditoría

## 6. Requerimientos funcionales
### 6.1 Usuarios
- clasificar al usuario como artista, productora, organizador, peña, festival, prensa o colaborador
- permitir organizaciones con miembros y roles

### 6.2 Eventos
El formulario debe soportar:
- título
- subtítulo o bajada
- descripción corta
- descripción larga
- fecha y hora
- fecha/hora fin opcional
- lugar
- dirección
- provincia y ciudad
- coordenadas opcionales
- artista/s relacionados
- organizador relacionado
- link de entradas
- precio
- gratuito / pago
- flyer
- galería
- hashtags sugeridos o manuales
- estado del evento
- política de publicación
- redes a disparar
- fecha/hora de publicación

### 6.3 Noticias
El formulario debe soportar:
- título
- bajada
- cuerpo
- fuente opcional
- autor
- artista relacionado
- evento relacionado
- categoría
- tags
- imagen destacada
- galería
- elección de canales
- programación

### 6.4 Workflow
Estados soportados:
- borrador
- pendiente de revisión
- observado
- aprobado
- programado
- publicado en portal
- publicado parcialmente
- rechazado
- archivado

### 6.5 Publicación
- publicar en portal con URL amigable y SEO básico
- seleccionar destinos: portal, redes propias, redes del portal o combinaciones
- gestionar cuentas sociales conectadas
- generar variantes por canal
- programar recordatorios y republicaciones
- registrar estado por canal e intentos
- reintentar fallos temporales
- guardar payload, respuesta, id externo, url externa y error

### 6.6 Dashboards
#### Dashboard publicador
- contenidos cargados
- estado general
- estado por canal
- observaciones
- próximos eventos
- resultados básicos

#### Dashboard admin
- pendientes de moderación
- publicaciones del día
- fallos por canal
- tokens vencidos
- contenidos más publicados
- top publicadores

## 7. Reglas de negocio
- todo contenido nuevo de un usuario no verificado requiere aprobación manual
- un usuario verificado puede tener autoaprobación parcial
- un evento no puede publicarse si la fecha ya pasó
- un evento debe tener como mínimo título, fecha, provincia, lugar o modalidad online e imagen destacada
- una noticia debe tener como mínimo título, cuerpo suficiente e imagen o justificación
- si una publicación va a redes debe existir una variante válida por canal
- si un canal requiere media obligatoria no podrá enviarse sin media válida
- si una cuenta social está desconectada o vencida el sistema debe bloquear ese canal y avisar
- la falla en una red no debe impedir publicar en el portal ni en otros canales
- cada intento por canal es independiente
- no debe permitirse duplicar masivamente la misma publicación en una ventana configurable
- las redes oficiales del portal sólo podrán usarse si el contenido fue aprobado para difusión institucional
- los recordatorios automáticos deben recalcularse si cambia la fecha del evento

## 8. Requerimientos no funcionales
- escalabilidad mediante colas
- trazabilidad completa
- tokens cifrados
- desacople por conectores
- resiliencia ante fallos externos
- observabilidad con logs y panel
- mantenibilidad de plantillas

## 9. Pantallas sugeridas
### 9.1 Panel publicador
- Mis publicaciones
- Mis eventos
- Mis noticias
- Nueva publicación
- Mis organizaciones
- Mis redes conectadas
- Estado de difusión
- Métricas básicas
- Notificaciones

### 9.2 Panel admin
- Cola de revisión
- Vista detalle del contenido
- Aprobar / observar / rechazar
- Programación manual
- Difusión institucional
- Historial de publicaciones
- Cuentas oficiales
- Estado de conectores
- Errores por canal
- Reintentos
- Logs
- Tokens por vencer
- Reglas y plantillas

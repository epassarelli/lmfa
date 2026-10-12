# Cambio: admin-attention-dashboard (tablero A + contadores en el sidebar B)

Estado: APROBADA por el usuario en el chat (2026-10-13: "Vayamos por A y B primero"). Implementada en local, sin migraciones. Valores no respondidos por el usuario, tomados de la propuesta: festivales en borrador dentro del Centro de Moderacion, umbrales de color 3 y 7 dias, contadores globales conservados al pie.

## Problema
El tablero (`/admin`) solo mostraba contadores globales y una tarjeta de contacto: no respondia "¿que tengo que atender hoy?". El Centro de Moderacion no incluia **festivales en borrador** (19 en la base local) ni los que nacen de aprobar altas de ficha.

## A. Tablero de administrador ("Requiere tu atencion")
- Tarjetas con **cantidad + lo mas antiguo** y color por antiguedad (0 = verde "al dia"; < 3 dias azul; 3 a 6 ambar; 7 o mas rojo), cada una enlazada a su lista:
  - **Solicitudes de ficha** pendientes (vinculos y altas) → Solicitudes de vinculo.
  - **Mensajes de contacto nuevos** → bandeja.
  - **Contenido a revisar**: total y desglose (noticias, eventos, jornadas, salas, artistas, discos, canciones, **festivales**) → Centro de Moderacion. Misma definicion de "pendiente" que el Centro (`ModerationQueue`).
  - **Clasificados por moderar** (`estado = pendiente`).
- **Alertas** (solo si hay algo): publicaciones de la pasarela fallidas, solicitudes de eliminacion de datos pendientes o fallidas, trabajos fallidos en cola.
- **Seguimiento de organizaciones:** por contactar (prospectos), con email obtenido, contactadas y sin respuesta hace mas de 14 dias.
- **Ultimos 7 dias:** usuarios nuevos, suscriptores nuevos al newsletter, solicitudes y mensajes recibidos (el spam no cuenta), eventos publicados en los proximos 7 dias.
- **Totales del portal:** los contadores actuales, al pie y mas compactos; el total de usuarios solo lo ve el administrador (antes lo veia cualquiera con el flag apagado).
- Datos con cache corta (30 s) y memoizados por peticion; se invalidan al cambiar solicitudes, mensajes, moderacion o clasificados.

## B. Contadores en el sidebar
Insignias en los items del menu **solo para administradores**: Solicitudes de vinculo (y su grupo Usuarios y Roles), Mensajes de contacto, Centro de Moderacion y Avisos Clasificados, con el mismo color por antiguedad. Mecanismo: filtro propio de menu de AdminLTE (`PendingBadgeFilter`) con la clave `pending` en cada item. Sin insignia cuando no hay pendientes.

## Centro de Moderacion (correccion incluida)
Se agrega la seccion **Festivales en borrador** con acciones Revisar y Publicar (`status = published`, fecha de publicacion si falta). Las consultas de "pendiente" viven en `ModerationQueue` y las usan el Centro y el tablero (una sola definicion).

## Fuera de alcance
"Mi panel" para no administradores (C) y feed de ultimos movimientos (D); cambios en las listas destino; migraciones.

## Archivos
`app/Support/ModerationQueue.php`, `app/Services/AdminAttentionService.php`, `app/Menu/Filters/PendingBadgeFilter.php`, `Dashboard`, `ModerationController` + vista, `routes` (sin cambios), `config/adminlte.php`, `AppServiceProvider`, modelos `EntityClaimRequest` y `ContactMessage` (invalidacion), `tests/Feature/AdminAttentionDashboardTest.php`.

## Rendimiento
Solo administradores; ~15 consultas agrupadas por calculo, cacheadas 30 s; el sidebar reutiliza el mismo resultado (sin consultas extra por item). Sin cache de respuestas.

## Validacion
`AdminAttentionDashboardTest` y suite completa.

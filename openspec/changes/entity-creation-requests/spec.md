# Cambio: entity-creation-requests

Estado: APROBADA e implementada en local (2026-10-11). Migracion aplicada por el usuario.

## Objetivo
Que un usuario que no encuentra su artista/festival pueda solicitar que se cree la ficha, explicando su relacion, sin alta directa. Reemplaza la opcion de dejar crear fichas libremente a quien no tiene ninguna.

## Reglas
- Reutiliza `entity_claim_requests` (`kind = create`, `entity_id` nulo hasta aprobar; `proposed_name`, `evidence_url`, `proposed_data`).
- Usuario (Mis fichas): tipo, nombre, relacion, mensaje, link de respaldo opcional, mes si es festival. Aviso de fichas parecidas antes de enviar. Mismos topes: 5 pendientes, throttle.
- Administrador: ve datos propuestos y fichas parecidas; puede crear la ficha (minima, sin publicar: Interprete `estado = 0` y biografia vacia; Festival `status = draft` con el mes pedido), vincular a una ficha existente (duplicado) o rechazar.
- Al aprobar: organizacion + membresia activa con el rol elegido (owner sugerido) + `user_id` del solicitante en la ficha nueva.
- No se inventan datos: festival sin mes valido no se crea.

## Fuera de alcance
Peñas/radios (tipos no registrados), notificaciones por email, deteccion avanzada de duplicados (solo coincidencia de nombre).

## Validacion
`EntityCreationRequestTest` (8 casos). Suite completa: 484 pasan; 13 fallos previos por datos reales.

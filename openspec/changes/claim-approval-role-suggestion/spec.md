# Cambio: claim-approval-role-suggestion

Estado: APROBADA (alternativa B elegida por el usuario, 2026-10-11) e implementada en local.

## Objetivo
Ahorrar el paso de elegir el rol de membresia al aprobar una solicitud y dar al administrador el contexto para decidir, sin aprobar nada automaticamente.

## Reglas
- Mapa configurable `entity_management.claims.suggested_roles`: artista, representante, organizador -> owner; prensa, otra -> editor. `member` nunca se sugiere.
- Si la ficha ya tiene un owner activo (distinto del solicitante), owner baja a editor.
- Se muestra el motivo y alertas: organizacion inactiva (danger), responsable activo existente (info), prensa/otra sin responsable (warning), solicitante ya miembro (warning), alta sin link de respaldo y fichas con nombre parecido (warning).
- El administrador puede elegir cualquier rol; la sugerencia no cambia lo que se aprueba.

## Fuera de alcance
Aprobacion automatica; cambiar permisos de owner/editor (hoy iguales); notificaciones.

## Archivos
config/entity_management.php, app/Services/EntityClaimService.php (`suggestApproval`), EntityClaimRequestController@show, resources/views/backend/claims/show.blade.php, tests/Feature/EntityManagement/ApprovalRoleSuggestionTest.php.

## Validacion
`ApprovalRoleSuggestionTest` (7 casos); `tests/Feature/EntityManagement` completo: 98 pasan.

# Cambio: owner-delegated-claim-review

Estado: APROBADA por el usuario en el chat (2026-10-12) e implementada en local.

## Objetivo
Que el responsable (owner) de una ficha apruebe o rechace las solicitudes de nuevos editores de su propia ficha, sin pasar por un administrador. El administrador sigue aprobando al primer owner y puede intervenir siempre.

## Reglas
- **Quién puede revisar (delegado):** miembro `owner` con estado `active` de una organización `active` vinculada a la ficha pedida. Un editor o `member` no puede.
- **Qué puede revisar:** solicitudes de vínculo (`kind = link`) en estado `pending` sobre esa ficha, de otro usuario (nunca la propia). Las solicitudes de alta de ficha (`kind = create`) y las fichas sin owner activo o con organización inactiva quedan solo para administradores.
- **Qué puede otorgar:** rol `editor` o `member`. Nunca `owner` (solo administrador). No degrada a quien ya es owner de esa organización.
- **Rechazar:** exige un motivo (3 a 1000 caracteres) que ve el solicitante.
- **Auditoría:** `reviewed_by` y `reviewed_at` registran al owner; el administrador ve quién revisó.
- **Datos que ve el owner:** nombre del solicitante, relación declarada, mensaje y fecha. No ve su email ni teléfono.
- **Interruptor:** `entity_management.claims.delegate_to_owners` (env `ENTITY_CLAIMS_DELEGATE_TO_OWNERS`, true por defecto). Apagado, solo revisa el administrador.
- El administrador conserva todo su flujo actual en Backoffice → Solicitudes de vínculo.

## Fuera de alcance
Notificaciones por email al owner, delegar solicitudes de alta, gestión de miembros por el owner (quitar o cambiar roles), migraciones.

## Archivos
config/entity_management.php; app/Services/EntityClaimService.php; app/Http/Controllers/Backend/MyEntitiesController.php; routes/admin.php; resources/views/backend/my-entities/index.blade.php; tests/Feature/EntityManagement/DelegatedClaimReviewTest.php.

## Validacion
`DelegatedClaimReviewTest` y suite completa.

## Riesgos
- Un owner malicioso puede dar acceso de editor a quien quiera sobre su ficha: es el alcance de su responsabilidad (editores solo escriben sobre esa ficha, todo va a revisión salvo `verificado`). El administrador puede revertir desde la organización.

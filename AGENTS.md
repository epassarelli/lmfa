# AGENTS.md

Este proyecto usa IA asistida con Spec Driven Development mediante OpenSpec.

## Fuente principal

Ningún agente puede modificar código sin una especificación aprobada.

## Agentes compatibles

Estas reglas aplican a Claude, Codex, Gemini, Antigravity u otros agentes.

## Prioridad de instrucciones

1. Instrucciones del usuario en el chat
2. AGENTS.md
3. OpenSpec: openspec/project.md, openspec/changes/*, openspec/specs/*
4. project/docs/00_estado_actual.md
5. backlog.json solo como referencia
6. Documentación histórica

## Reglas obligatorias

- No ejecutar backlog automáticamente.
- No implementar sin spec aprobada.
- No mezclar tareas no relacionadas.
- No modificar rutas SEO sin justificar impacto.
- No cambiar arquitectura sin spec específica.
- No ejecutar migraciones ni SQL destructivo sin autorización.
- No inventar modelos, columnas, rutas ni relaciones.
- Antes de implementar, listar archivos afectados.
- Después de implementar, validar y documentar.

## Stack

- Laravel 10
- PHP 8.x
- MySQL/MariaDB
- Blade clásico
- Tailwind CSS 3.x en frontend (Bootstrap es contaminación de agentes — si se encuentra, reportarlo al usuario)
- AdminLTE 3 en backend
- Livewire solo si ya existe o se solicita
- No React salvo autorización explícita

## Modo de trabajo

1. Usuario pide cambio.
2. Agente crea propuesta OpenSpec usando openspec/templates/spec.md.
3. Agente presenta spec.
4. Esperar aprobación
5. Usuario aprueba.
6. Agente implementa solo esa spec.
7. Agente valida ejecutando tests o checklist
8. Agente actualiza docs/00_estado_actual.md si corresponde.
9. Informar resultado

---

## Estrategia de ramas

- Rama base de desarrollo: `dev`
- Para cada feature o cambio: crear rama desde `dev` → validar → mergear a `dev`
- Deploy a producción desde `main`
- La rama activa se lee del `gitStatus` al iniciar sesión — no asumir
- No crear ramas nuevas sin autorización explícita del usuario

---

## Base de datos — Reglas críticas

### El agente NUNCA puede:
- Ejecutar SQL modificatorio en el contenedor sin autorización explícita
- Correr `php artisan migrate` sin mostrar primero `migrate:status` y esperar confirmación
- Usar `RefreshDatabase` en tests — crashea MariaDB en Docker/Windows

### El agente PUEDE (solo lectura):
- `docker exec lmfa-db-1 mysql -umfa -pmfa mfa -e "SHOW TABLES;"`
- `docker exec lmfa-db-1 mysql -umfa -pmfa mfa -e "DESCRIBE tabla;"`
- `docker exec lmfa-db-1 mysql -umfa -pmfa mfa -e "SELECT COUNT(*) FROM tabla;"`
- `php artisan migrate:status`

### Para cualquier cambio en BD:
1. Generar archivo `.sql` con guards `IF NOT EXISTS` / `IF EXISTS`
2. Presentarlo al usuario con descripción clara
3. Esperar que el usuario lo ejecute

---

## Testing

- Siempre usar `DatabaseTransactions` — nunca `RefreshDatabase`
- Tests en `tests/Feature/`
- Si un test existente usa `RefreshDatabase`, reemplazarlo por `DatabaseTransactions`
- Usar `Queue::fake()` para jobs, `Http::fake()` para conectores externos
- Mínimo un test feature por historia de usuario

---

## Convención de commits

```
feat(<id>): descripción breve
fix(<id>): descripción breve
refactor(<id>): descripción breve
test(<id>): descripción breve
```

Incluir siempre al final:
`Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>`

---

## Cuándo frenar y pedir intervención humana

Detenerse y consultar al usuario si:
- Falta definición funcional que afecta datos, UX o reglas de negocio
- Una tarea contradice documentos existentes
- Una integración externa requiere credenciales
- Hay riesgo de afectar datos del usuario
- Hay múltiples caminos con impacto arquitectónico
- Dos fallos consecutivos del mismo problema de infraestructura

En esos casos: informar el bloqueo, describir el motivo, proponer alternativas.
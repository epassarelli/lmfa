# OpenSpec — Reglas Globales del Proyecto

> Estas reglas aplican a **todo cambio** en este repositorio, sin excepción.
> Todo agente debe leerlas antes de generar o aprobar cualquier spec.

---

## Stack permitido

| Capa | Tecnología permitida | Restricción |
|---|---|---|
| Backend | Laravel 10, PHP 8.2+ | — |
| Frontend (público) | Blade, Bootstrap 5, Alpine.js | Sin React ni Vue |
| Frontend (admin) | AdminLTE 3, Bootstrap 4, DataTables | Sin React ni Vue |
| Componentes reactivos | Livewire 3 | Solo si ya existe en el área o se solicita explícitamente |
| Base de datos | MySQL / MariaDB | Sin SQLite, sin cambios de driver |
| Colas | `QUEUE_CONNECTION=database` | Sin Redis hasta autorización explícita |

---

## Reglas de implementación

### Rutas SEO
- **No modificar** slugs de rutas públicas existentes (`/noticias-del-folklore-argentino`, `/artistas-del-folklore-argentino`, etc.) sin generar simultáneamente una redirección 301.
- Toda ruta nueva o modificada en `web.php` debe considerar: redirección 301, etiqueta `canonical`, y `meta title`/`meta description` en la vista correspondiente.
- Si una ruta desaparece, proponer la redirección antes de eliminarla.

### Layouts globales
- **No modificar** `resources/views/layouts/` (app.blade.php, admin.blade.php u otros layouts base) sin presentar primero una propuesta al usuario y esperar aprobación explícita.
- Cambios en `config/adminlte.php` (sidebar, menú) se consideran modificación de layout y requieren propuesta previa.

### Variables de vistas y sidebars
- **No eliminar** variables que sean consumidas por cualquier vista Blade, componente o sidebar de AdminLTE.
- Antes de renombrar o quitar una variable, listar todas las vistas que la consumen.
- Si un controlador inyecta variables a un `View::share()` o `ViewComposer`, no tocar sin inventario completo.

### Migrations y base de datos
- Nuevas migrations siempre con guards: `Schema::hasTable()` / `Schema::hasColumn()` / `IF NOT EXISTS`.
- Nunca ejecutar `php artisan migrate` sin mostrar `migrate:status` y esperar confirmación del usuario.
- Para cambios de BD: generar un archivo `.sql` y entregarlo al usuario para ejecución manual.

---

## Qué debe incluir cada spec

Todo spec debe cubrir las siguientes secciones (ver `_template.md`):

1. **Objetivo** — qué se cambia y por qué
2. **Stack involucrado** — checkbox de capas afectadas
3. **Archivos afectados** — lista exhaustiva de archivos a crear/modificar/eliminar
4. **Impacto en rutas** — tabla con rutas afectadas y su tratamiento SEO
5. **Cambios en layouts** — ninguno / propuesta explícita
6. **Variables de vistas** — nuevas, modificadas o eliminadas
7. **Checklist de validación manual** — pasos concretos para verificar el cambio a mano
8. **Notas para el agente** — restricciones específicas al implementar

---

## Flujo de trabajo con specs

```
1. Agente escribe el spec en project/specs/<id>_<slug>.md
2. Agente presenta el spec al usuario — NO implementa
3. Usuario revisa y aprueba (o pide cambios al spec)
4. Agente implementa estrictamente lo descrito en el spec aprobado
5. Agente ejecuta el checklist de validación
6. Agente actualiza backlog.json y docs/00_estado_actual.md
7. Commit con formato de convención
```

> **Regla de oro:** Si no hay spec aprobado, no hay código.

---

## Cierre obligatorio después de cada tarea / corrección

Toda sesión de desarrollo o corrección de bugs **debe cerrar** con estos tres pasos en orden:

1. **CHANGELOG.md** — agregar entrada bajo la versión activa con formato `### Fixed / Added / Changed`
2. **Git tag semántico** — `git tag -a vX.Y.Z -m "descripción"` siguiendo semver:
   - patch (Z): bug fixes y correcciones menores
   - minor (Y): nuevas funcionalidades compatibles
   - major (X): cambios que rompen compatibilidad
3. **project/docs/00_estado_actual.md** — actualizar estado, fecha y resumen de lo realizado

> No cerrar una sesión sin estos tres pasos.

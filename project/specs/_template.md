# Spec: [Título del cambio]

> **ID:** `SPEC-XXX`
> **Tarea backlog:** `PC-XX` (si aplica)
> **Estado:** `draft` | `approved` | `implementing` | `done`
> **Fecha:** YYYY-MM-DD
> **Autor:** [agente / usuario]

---

## Objetivo

<!-- Describir qué se cambia, qué problema resuelve y qué queda fuera del alcance. -->

---

## Stack involucrado

- [ ] Backend PHP/Laravel (controladores, modelos, servicios, jobs)
- [ ] Vistas frontend — Blade + Bootstrap 5
- [ ] Vistas backend — AdminLTE 3 / Bootstrap 4
- [ ] Rutas (`web.php` / `admin.php` / `api.php`)
- [ ] Migrations / cambios de BD
- [ ] Assets (CSS/JS compilados con Vite)
- [ ] Livewire (solo si ya existe en el área o fue solicitado explícitamente)
- [ ] Config (`config/*.php`)
- [ ] Tests

---

## Archivos afectados

<!-- Lista exhaustiva. Usar la convención: (C) crear, (M) modificar, (E) eliminar -->

| Acción | Archivo |
|--------|---------|
| (M) | `app/Http/Controllers/...` |
| (M) | `resources/views/...` |
| (M) | `routes/...` |
| (C) | `...` |

---

## Impacto en rutas SEO

<!-- Completar solo si hay rutas nuevas, modificadas o eliminadas en web.php -->

| Ruta anterior | Ruta nueva / acción | Redirección 301 | `canonical` actualizado | Meta tags actualizados |
|---|---|:---:|:---:|:---:|
| — | — | — | — | — |

> Si se elimina una ruta pública, especificar a dónde redirige el 301.

---

## Cambios en layouts globales

<!-- Ninguno / O descripción de la propuesta que requiere aprobación -->

**Ninguno.** *(borrar esta línea si hay cambios)*

---

## Variables de vistas

<!-- Listar variables nuevas, renombradas o eliminadas y qué vistas las consumen -->

| Variable | Acción | Vistas que la usan |
|---|---|---|
| `$example` | nueva | `views/frontend/x.blade.php` |

> Si se elimina una variable, confirmar que ninguna vista, sidebar o ViewComposer la consume.

---

## Checklist de validación manual

<!-- Pasos concretos que el revisor (humano o agente) debe ejecutar para validar el cambio -->

- [ ] Iniciar servidor con `php artisan serve` y navegar a la ruta afectada
- [ ] Verificar que la página carga sin errores 500
- [ ] Verificar que el layout (navbar, sidebar, footer) se renderiza correctamente
- [ ] Verificar que no hay variables `undefined` en la vista (`$undefinedVariable`)
- [ ] Comprobar en DevTools que no hay errores JS
- [ ] Verificar que el `<title>`, meta description y canonical son correctos (si aplica)
- [ ] Verificar redirección 301 con `curl -I <url-anterior>` (si aplica)
- [ ] Ejecutar `php artisan test --filter <TestClass>` y confirmar que pasa
- [ ] Revisar el sidebar de AdminLTE para confirmar que no se rompió ningún ítem de menú (si el cambio afecta admin)
- [ ] ...

---

## Notas para el agente

<!-- Restricciones específicas, decisiones de diseño, comportamientos a preservar -->

- No usar React ni Vue.
- No modificar layouts globales más allá de lo especificado en este spec.
- Si aparece una ambigüedad durante la implementación, detener y consultar antes de asumir.

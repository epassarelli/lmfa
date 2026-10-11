# Cambio: event-agenda-description-fallback

## Objetivo

Que el JSON-LD `Event.description` de la cartelera nunca salga vacío cuando el evento tiene algún texto real (meta_description, excerpt o body), con la misma prioridad que la ficha.

## Problema actual

`resources/views/frontend/shows/index.blade.php` arma la description del JSON-LD solo con `body` (sin HTML, 160 caracteres). Si `body` está vacío o es HTML sin texto, emite `description` vacía aunque haya `excerpt` o `meta_description`. La ficha usa `SeoMetadata::event()` (meta_description → excerpt → body → texto genérico). Origen: auditoría `project/docs/releases/event-description-audit-2026-10-09.md`.

## Alcance incluido

- La cartelera usa `SeoMetadata::event($event)['description']` para el JSON-LD.
- Test Feature con body vacío / HTML vacío y excerpt disponible.

## Fuera de alcance

- Columnas nuevas (no se crea `description`), migraciones o cambios de BD.
- Completar o inventar descripciones de los 114 eventos locales sin body (deuda editorial).
- Cambiar el texto visible de las tarjetas, rutas, slugs o URLs.
- Afirmar que esto resuelve el aviso de Search Console productivo.

## Archivos afectados

- resources/views/frontend/shows/index.blade.php
- tests/Feature/Events/EventAgendaDescriptionTest.php

## Reglas funcionales

### Caso 1
Dado un evento publicado con body `<p></p>` y excerpt "Resumen real"
Cuando se consulta la cartelera
Entonces su JSON-LD tiene description "Resumen real".

### Caso 2
Dado un evento con meta_description, excerpt y body
Cuando se consulta la cartelera
Entonces la description es la meta_description (igual que la ficha).

### Caso 3
Dado un evento sin ningún texto
Cuando se consulta la cartelera
Entonces la description es el texto genérico de `SeoMetadata` (no vacía) y no se inventa contenido cultural.

## Reglas técnicas

- Sin consultas adicionales por ítem (solo atributos ya cargados).
- Tests con `DatabaseTransactions`.

## Validación

- Test nuevo + regresión de `tests/Feature/Events`.

## Riesgos

- Bajo: cambia solo el valor de un campo del JSON-LD. Reversible.

## Criterio de aceptación

Los tres casos pasan y la regresión de Eventos no muestra fallos nuevos.

## Aprobación

Pedido explícito del usuario (opción B) en el chat, 2026-10-11.

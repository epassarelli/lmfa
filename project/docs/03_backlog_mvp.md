# 03 - Backlog Maestro

> Backlog estructural en Git alineado con `07_hoja_de_ruta.md`.
> Ultima actualizacion: 2026-09-04.
> Google Drive conserva la priorizacion humana; esta es la cola local ejecutable del agente.

---

## 1. Funcion del documento

Este backlog conserva:

- trabajo estructural vigente;
- prioridades maestras por programa;
- dependencias y gates;
- historial util de frentes ya cerrados.

No reemplaza la priorizacion humana en Drive ni reabre automaticamente tareas ya completadas. Si una tarea incluye autonomía explícita, sí habilita su ejecución ordenada por el agente.

---

## 2. Marco operativo vigente

El proyecto ya no esta en una fase puramente tecnica de estabilizacion. El backlog maestro debe leerse en dos programas paralelos:

### Programa A - Autoridad editorial

Objetivo:

- mejorar calidad, profundidad, relaciones, SEO y media del inventario publicado;
- sostener lotes de curacion con auditoria y control humano.

### Programa B - Producto y servicios

Objetivo:

- evolucionar navegacion, agenda, comunidad, automatizacion, distribucion y servicios diferenciales;
- validar valor real en produccion antes de expandir alcance.

---

## 3. Reglas de estados y autoridad

- Drive manda sobre prioridades humanas, comerciales y editoriales.
- Git manda sobre estructura, historial util, dependencias, criterios de cierre y la cola ejecutable del agente.
- `project/docs/backlog.json` queda como legado historico y no gobierna prioridades.

Estados validos en este documento:

- `pending`
- `in_progress`
- `blocked`
- `done`
- `needs_review`

---

## 4. Prioridades maestras vigentes

### A. Autoridad editorial

| ID | Tarea | Estado | Prioridad | Nota |
|---|---|---|---|---|
| `ED-A1` | Cerrar piloto controlado de seis operaciones Content Refresh para Artista, Receta y Mito | `pending` | Critica | Gate previo a escala automatizada |
| `ED-A2` | Ejecutar primer lote P1 de Biografias y medir score antes/despues | `pending` | Critica | Lote pequeno y auditable |
| `ED-A3` | Ejecutar lotes equivalentes de Recetas y Mitos | `pending` | Critica | No habilitar updates masivos |
| `ED-A4` | Incorporar visitas reales al auditor de Festivales | `pending` | Alta | El desempate actual sigue siendo provisional |
| `ED-A5` | Consolidar auditoria mensual y linea base editorial recurrente | `in_progress` | Alta | Ya existe matriz inicial 2026-09-01 |

### B. Producto y servicios

| ID | Tarea | Estado | Prioridad | Nota |
|---|---|---|---|---|
| `PS-B1` | Validar Pasarela de Contenidos end-to-end en produccion | `pending` | Alta | Codigo implementado, cierre operativo pendiente |
| `PS-B2` | Validar UGC mas alla de Noticias en produccion | `pending` | Alta | Mantener moderacion y trazabilidad |
| `PS-B3` | Definir siguiente release funcional por impacto en descubrimiento y recurrencia | `pending` | Alta | No decidir por disponibilidad de legacy |
| `PS-B4` | Cerrar release gate de directorios Peñas y Radios | `in_progress` | Alta | Implementados en DEV; CI verde y smoke oscuro local aprobados. Restan staging HTTPS, piloto Apps Script y smoke visible. |
| `PS-B5` | Diseñar primer formato multicanal repetible | `pending` | Media | Solo despues del gate editorial minimo |

### C. Derechos y gobernanza

| ID | Tarea | Estado | Prioridad | Nota |
|---|---|---|---|---|
| `GOV-C1` | Aprobar politica operativa de derechos para Discografia/Cancionero | `blocked` | Critica | Sin esto no debe automatizarse ni ampliarse el cancionero |
| `GOV-C2` | Corregir el test sintacticamente roto que bloquea `php artisan test` completo | `done` | Media | La suite Jetstream obsoleta fue retirada en `9b3dc68`; `php artisan test --list-tests` carga sin errores sintacticos. |

### D. Ejecución autónoma hacia producto 99%

| ID | Tarea | Estado | Prioridad | Autonomía | Dependencia / criterio de cierre |
|---|---|---|---|---|---|
| `PROD-01` | Remediar vulnerabilidades de dependencias PHP y convertir auditorías en gate CI | `needs_review` | Crítica | `IA_CON_VALIDACION` | Hardening compatible aplicado a Guzzle: auditoría bajó de 56 a 43 avisos. Las migraciones locales pendientes ya se aplicaron y las pruebas focalizadas de noticias pasan; quedan Laravel/Symfony para un plan coordinado de Laravel 11+. La suite completa espera la estabilización de MariaDB local ante DDL. |
| `PROD-02` | Incorporar healthcheck, diagnóstico de scheduler/cola y runbook operativo mínimo | `done` | Crítica | `IA_AUTONOMA` | `GET /healthz`, diagnóstico admin, heartbeat y runbook; 4 tests/18 assertions. |
| `PROD-03` | Completar release gate HTTPS de Peñas/Radios | `blocked` | Crítica | `HUMANA` | URL staging, acceso, backup y Apps Script de staging claramente separados de producción. |
| `PROD-04` | Limpiar depuración visible y estandarizar acabado técnico de layouts públicos/admin | `done` | Alta | `IA_AUTONOMA` | Logs retirados de layouts activos; prueba de regresión y Blade cache verde. |
| `PROD-05` | Diseñar e implementar administración verificada de entidades por usuarios | `pending` | Alta | `IA_CON_VALIDACION` | Requiere modelo de gobernanza, claims y decisión funcional previa. |
| `PROD-06` | Implementar favoritos, seguimientos y alertas territoriales/editoriales | `pending` | Alta | `IA_CON_VALIDACION` | Requiere definición de canales, frecuencia y consentimiento. |
| `PROD-07` | Diseñar servicios comerciales y atribución de resultados | `pending` | Media | `HUMANA` | Requiere decisión comercial, pricing, pagos y obligaciones fiscales. |
| `PROD-08` | Agregar E2E crítico, accesibilidad y presupuestos de calidad al CI | `done` | Alta | `IA_AUTONOMA` | Suite publica con 2 pruebas y 50 aserciones incorporada a CI; valida landings, estructura accesible y presupuesto HTML. |

---

## 5. Historial util conservado

- El backlog tecnico original `PC-*` permanece cerrado en `project/docs/backlog.json`.
- La estabilizacion tecnica base, las modernizaciones de Festivales, Biografias, Recetas y Mitos y la integracion editorial asociada ya no deben figurar como frentes "por iniciar".
- El rollout tecnico puede leerse como desplegado por oleadas, aunque la explotacion editorial posterior siga abierta.

---

## 6. Criterio de paso a Drive

Una tarea pasa al backlog operativo de Drive cuando:

1. entra en ejecucion cercana;
2. necesita validacion diaria o accion concreta de Eduardo;
3. tiene un siguiente paso observable;
4. conviene monitorearla fuera del historial estructural de Git.

## 7. Continuidad autónoma

Al iniciar una sesión, el agente lee `00_estado_actual.md`, esta cola y la spec puntual. Toma la tarea `pending` de mayor prioridad con `IA_AUTONOMA` y dependencias satisfechas; la mueve a `in_progress`, crea OpenSpec, implementa, valida, documenta evidencia y continúa. Si no hay tarea elegible, deja el bloqueo concreto en `00_estado_actual.md` y solicita únicamente el dato o decisión imprescindible.

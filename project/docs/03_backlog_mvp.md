# 03 - Backlog Maestro

> Backlog estructural en Git alineado con `07_hoja_de_ruta.md`.
> Ultima actualizacion: 2026-09-01.
> El seguimiento diario activo vive en Google Drive (`Backlog Asistente ChatGPT`, pestana `Backlog`).

---

## 1. Funcion del documento

Este backlog conserva:

- trabajo estructural vigente;
- prioridades maestras por programa;
- dependencias y gates;
- historial util de frentes ya cerrados.

No reemplaza el seguimiento diario en Drive ni reabre automaticamente tareas ya completadas.

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

- Drive manda sobre prioridad diaria y seguimiento corto.
- Git manda sobre estructura, historial util, dependencias y criterios de cierre.
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
| `PS-B4` | Auditar y priorizar Penias como siguiente modulo candidato | `pending` | Media | Sigue mejor posicionado que Entrevistas y Radios |
| `PS-B5` | Diseñar primer formato multicanal repetible | `pending` | Media | Solo despues del gate editorial minimo |

### C. Derechos y gobernanza

| ID | Tarea | Estado | Prioridad | Nota |
|---|---|---|---|---|
| `GOV-C1` | Aprobar politica operativa de derechos para Discografia/Cancionero | `blocked` | Critica | Sin esto no debe automatizarse ni ampliarse el cancionero |
| `GOV-C2` | Corregir el test sintacticamente roto que bloquea `php artisan test` completo | `pending` | Media | Deuda tecnica documentada en estado actual |

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

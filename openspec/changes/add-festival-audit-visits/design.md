## Context

`AuditFestivals` ya procesa los festivales por chunks, calcula un score y produce consola/CSV sin escribir en la base. A diferencia de los auditores de Artistas, Recetas y Mitos, no incorpora `visitas` ni un desempate estable completo.

## Goals / Non-Goals

**Goals:**

- alinear el orden con el criterio editorial común;
- exponer la señal de demanda en consola y CSV;
- preservar lectura por chunks y comportamiento read-only.

**Non-Goals:**

- cambiar el cálculo del score o los umbrales P1/P2/P3;
- modificar cómo se contabilizan visitas;
- escribir, normalizar o migrar datos.

## Decisions

1. `visitas` se normaliza a entero en la fila de auditoría para tolerar valores nulos legacy.
2. El comparador será prioridad numérica P1/P2/P3, score ascendente, visitas descendentes e ID ascendente. Aunque prioridad deriva hoy del score, mantenerla explícita documenta el contrato y admite futuros ajustes de umbral.
3. Consola y CSV usarán el encabezado `Visitas`/`visits`, consistente con los otros auditores.
4. La prueba creará dos festivales con igual score y visitas distintas, verificará orden, CSV y ausencia de mutaciones.

## Risks / Trade-offs

- [Los contadores pueden contener ruido histórico] → se usan sólo como desempate, nunca como score ni criterio de publicación.
- [Ordenar el arreglo completo consume memoria] → no cambia el comportamiento actual; la consulta sigue chunked y el inventario es pequeño.

## Migration Plan

No requiere migración. El rollback consiste en revertir comando y prueba.

## Open Questions

Ninguna.

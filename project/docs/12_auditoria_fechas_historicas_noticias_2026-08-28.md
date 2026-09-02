# Auditoría de fechas históricas de noticias

> Backlog: `BL-0010N` — 2026-08-28 (ART). Alcance de diagnóstico, sólo lectura.

## Conclusión

No es seguro completar `news.published_at` en forma automática con los datos locales actuales. La tarea queda parcial: existe una clasificación reproducible y un dry-run de selección, pero faltan fuentes históricas independientes para verificar cada fecha candidata.

No se ejecutaron migraciones, SQL modificatorio, backfill, despliegues ni commits.

## Evidencia local

Contraste realizado sobre la rama `20260827`, commit `1b0c5368192c16ca130987a7ecae671d58babf83`.

La tabla canónica es `news`; la tabla histórica `noticias` sigue existiendo sólo por compatibilidad y está vacía en la base local. La migración histórica conserva `publicar` como `published_at` y copia `created_at` sin convertirlo en fecha editorial. El modelo expone ambos campos y el frontend sólo usa `published_at` como fecha de publicación efectiva.

| Estado de fecha en `news` | Cantidad | Tratamiento seguro |
| --- | ---: | --- |
| `published_at` y `created_at` presentes | 117 | Conservar; no se sobrescribe |
| Sólo `published_at` presente | 1 | Conservar; fecha canónica existente |
| Sólo `created_at` presente | 120 | Candidato a contraste externo, no a backfill automático |
| Ambas fechas ausentes | 115 | Sin fuente local recuperable |

En total, 235 de 353 registros carecen de `published_at`; 345 están publicados. De los 120 candidatos con sólo `created_at`, 6 son de 2024, 113 de 2025 y 1 de 2026. La muestra contradice la regla simplista `published_at = created_at`: la noticia `cosquin-2024-una-cuarta-luna-de-emociones-lagrimas-y-fiesta-inolvidable` fue creada el 2025-01-30. Además, 59 registros sin `published_at` contienen un año en el título y 133 en el cuerpo: esas menciones describen hechos o eventos, no prueban por sí solas la fecha de publicación.

No se encontraron backups, dumps o archivos de exportación locales con una fuente de fecha adicional. `updated_at` tampoco es fuente editorial: en la muestra de registros sin fecha fue actualizado durante la operación de 2026-08-06.

## Impacto detectado

El frontend y el sitemap toleran fechas nulas con fallbacks. En la plantilla de detalle, `datePublished` usa `published_at`, luego `created_at` y finalmente `updated_at`; por tanto, usar la última opción para noticias históricas puede comunicar como fecha de publicación una actualización técnica. No se modificó este comportamiento porque requiere una decisión SEO y una especificación separada.

## Dry-run propuesto

La siguiente consulta es exclusivamente de lectura. Clasifica los registros sin alterar datos y deja explícito que ningún candidato queda autorizado para escritura sin una fuente externa verificable (captura histórica, export original o fuente editorial atribuible).

```sql
SELECT
  id,
  slug,
  title,
  editorial_status,
  published_at,
  created_at,
  updated_at,
  CASE
    WHEN published_at IS NOT NULL THEN 'CONSERVAR_FECHA_CANONICA'
    WHEN created_at IS NOT NULL THEN 'REQUIERE_FUENTE_EXTERNA_POR_REGISTRO'
    ELSE 'SIN_FUENTE_LOCAL_RECUPERABLE'
  END AS accion_propuesta
FROM news
WHERE editorial_status = 'published'
ORDER BY id;
```

## Gate para un backfill futuro

Antes de preparar cualquier comando de escritura se requiere:

1. Una fuente histórica verificable por registro o lote homogéneo, registrada junto a cada ID.
2. Aprobación humana de la regla exacta de elegibilidad; `created_at`, años en título/cuerpo y `updated_at` no bastan por sí solos.
3. Un dry-run que informe IDs, valor previo, valor propuesto, fuente y confianza.
4. Una ejecución transaccional y reversible, limitada al lote aprobado, sin tocar registros sin evidencia.

La próxima acción es aportar o autorizar el acceso read-only a un backup/export histórico, archivo editorial o fuente web archivada; entonces se podrá convertir el dry-run en una propuesta de backfill revisable.

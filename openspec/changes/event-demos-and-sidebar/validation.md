# Evidencia local 2026-10-08

Autorización explícita del usuario para insertar demos y modificar menú; rama activa conservada. Script revisable presentado antes de aplicar, sin migraciones ni SQL contra registros anteriores.

## Resultado

| Ejemplo | Backoffice | Fecha | Jornadas |
|---|---|---|---|
| Encuentro del Río | `/admin/event-programs/25/edit` | 15–18/10/2026 | 4 (Events 4114–4117) |
| Fiesta de la Luna | `/admin/event-programs/26/edit` | 29/10–02/11/2026 | 5 (Events 4118–4122) |
| Peña del Patio | `/admin/events/4123/edit` | 12/11/2026 | 1 independiente |

Sedes ficticias 19/20. Dos intérpretes existentes en cada Event; grillas distintas, vínculos ficticios identificados en cuerpo/título. No se modificaron perfiles ni crearon usuarios. Jornada 2 en cada conjunto usa price_text propio; resto hereda datos comunes. El evento independiente comparte la sede 19 y es gratuito.

## Validación

- `BackofficeSidebarTest`: 2 pruebas, 30 aserciones, pasan. Transacciones sobre mysql/db/mfa; caché en memoria y response cache desactivado. Sin resets, seeders ni DDL. Estructura, orden ASC de todas las secciones/submenús, HTML de administrador y restricción de enlaces de Pasarela verificados.
- Sintaxis del script y Pint pasan; OpenSpec y git diff --check pasan (advertencia de normalización CRLF preexistente).
- Ejecución como www-data de `php project/scripts/local/event-programs-demo.php --apply`: created_now=true. Repetición: false, mismos IDs, sin nuevas filas.
- Conteos antes/después: events 314→324, venues 0→2, interpretes 446→446, news 366→366, users 435→435, migrations 82→82. Dos conjuntos insertados mediante servicio; diez Events y sus pivots creados en una transacción.
- 12 fichas públicas con HTTP 200 y aviso DEMO visible (2 conjuntos, 9 jornadas y evento simple), usando HTTP al Apache local desde contenedor con Host mfa.localhost. Intento desde PowerShell no resolvió ese hostname; no se cambió DNS ni hosts. No hay navegador disponible para QA visual.
- Manifiesto local con IDs, horarios, grillas, excepciones y URLs: `storage/app/local-demos/event-programs-manifest.json`.

## Prueba del usuario

1. Abrir Conjuntos / Jornadas y editar Encuentro del Río: ver defaults y las cuatro jornadas; abrir cada jornada para ver su grilla.
2. Cambiar el precio común del conjunto: jornadas 1/3/4 deben heredar; jornada 2 conserva la excepción.
3. En jornada 2 marcar volver a heredar price_text y guardar; comparar.
4. Abrir Fiesta de la Luna para ver cinco noches con artistas propios y finales al día siguiente.
5. Abrir Peña del Patio en Eventos / Cartelera: program_id vacío, gratis, sede del catálogo reutilizada.
6. Recargar Backoffice: módulos culturales directos, Pasarela plegable, Usuarios y Roles ordenado Permisos/Roles/Usuarios.

## Performance y SEO

Menú estático: sin consultas, dependencias ni JS nuevo. La carga usa servicios ya validados y se ejecuta únicamente por CLI local; no agrega costo al frontend productivo ni cambia URLs. Demos publicados solo en la base local para inspeccionar páginas con el render habitual; no son contenido editorial verificable. Sin push, commit, integración ni despliegue. Borrado posterior de demos requiere autorización, no se ejecuta automáticamente.

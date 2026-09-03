## Context

El portal conserva una tabla y una pantalla legacy de Peñas, con campos mínimos (`titulo`, `detalle`, `foto`, `publicar`, `estado`) y sin un detalle público consistente. Esa superficie no modela dirección verificable, tipo de espacio, reserva, fuentes, vigencia ni relaciones editoriales. En contraste, Festivales ofrece el patrón vigente del portal para una entidad evergreen: modelo canónico, estado editorial, filtros, SEO, sitemap, relaciones y auditoría.

Las Peñas son espacios permanentes, pero sus fechas y artistas invitados cambian. La arquitectura debe preservar esa distinción y reutilizar `Event` para la agenda temporal. El módulo se implementará en Laravel 10, Blade, AdminLTE 3, MySQL/MariaDB y la infraestructura SEO/API existente, sin ejecutar migraciones ni cambios de datos sin aprobación humana.

## Goals / Non-Goals

**Goals:**

- Publicar fichas de Peñas verificables y útiles para búsquedas territoriales.
- Separar de forma explícita la identidad estable de una Peña de sus Eventos futuros.
- Reutilizar los patrones técnicos validados por Festivales y Enciclopedia sin acoplarse a las tablas legacy.
- Permitir una curación incremental, auditable y reversible antes de la indexación masiva.
- Establecer una base compatible con integración editorial y una futura oferta B2B, sin comprometer el contrato inicial.

**Non-Goals:**

- Reconstruir Radios en este release.
- Convertir Peñas en un marketplace, motor de reservas o sistema de venta de entradas.
- Publicar automáticamente datos legacy o inferir relaciones por coincidencia de texto.
- Duplicar información temporal de `Event` en la ficha de una Peña.
- Migrar, borrar o modificar datos existentes sin una estrategia aprobada y validada en entorno seguro.

## Decisions

### Entidad canónica independiente de la tabla legacy

Se creará una entidad canónica `Penia` o una tabla equivalente nueva, según el relevamiento de colisiones de modelo y migraciones. Su contrato incluirá identidad, slug, texto editorial, estado, ubicación normalizada, contacto, reserva, tipo de espacio, horarios, accesibilidad, fuentes y verificación.

Se evita ampliar la tabla legacy directamente porque sus campos y estado no expresan el contrato necesario. Cada registro canónico podrá conservar una referencia de procedencia legacy nullable para auditoría y backfill controlado.

Alternativa descartada: reutilizar `penias` con columnas sucesivas. Reduce el número de tablas, pero fija el módulo moderno a semánticas legacy y hace menos reversible una migración incompleta.

### Peña permanente y Evento temporal mediante relación persistida

Una Peña tendrá una relación muchos-a-muchos con `Event`, implementada únicamente cuando se confirme la tabla y convención de pivotes del proyecto. El frontend mostrará solo eventos publicados y futuros. Artistas se descubrirán prioritariamente a través de esos eventos; una relación directa con Artistas solo se añadirá si existe curación explícita y no duplica la agenda.

Alternativa descartada: almacenar agenda y artistas invitados como texto dentro de la Peña. Eso degrada la vigencia, impide filtros fiables y compite con la cartelera existente.

### Descubrimiento territorial con filtros indexables controlados

La landing nacional filtrará por texto, provincia, localidad y tipo de espacio. Las combinaciones con resultados y contexto editorial podrán tener canonical propio; las búsquedas arbitrarias o vacías conservarán canonical de la landing y no se convertirán en URLs indexables de bajo valor.

Se reutilizarán `Provincia` y `Locality` solo después de confirmar sus llaves y contratos. Los resultados usarán eager loading, paginación y caché selectiva siguiendo el patrón de Festivales.

### Verificación como requisito de publicación, no como metadato decorativo

El modelo diferenciará `draft`, `approved`, `published` y `archived`, junto con `verification_status` y `last_verified_at`. Los datos sensibles al cambio, como horarios, reservas y contacto, exigirán fuente y fecha de verificación para publicar. Un registro desactualizado podrá conservarse para revisión, pero no deberá presentarse como vigente.

Alternativa descartada: publicar la ficha y marcar después los faltantes. Esto perjudica la confianza de visitantes y el valor SEO del directorio.

### Integración editorial y API detrás de un gate de release

El backoffice y la API autenticada gestionarán altas y actualizaciones; Content Refresh se incorporará después de que el CRUD, auditor y campos de calidad estén cubiertos por pruebas. El lote inicial será de diez o más fichas con fuentes, media autorizada y vigencia comprobada.

La carga masiva desde legacy o integraciones no formará parte de la primera publicación. La API seguirá los patrones de autenticación y autorización existentes del portal y usará requests compartibles entre admin y API cuando sea razonable.

### SEO específico para lugares culturales

Las fichas públicas tendrán URL canónica bajo una base corta nueva, breadcrumbs, title y meta description persistidos, JSON-LD `MusicVenue` o `LocalBusiness` según la evidencia disponible, y presencia en sitemap solo cuando estén publicadas y verificadas. La URL legacy se conservará hasta que el inventario determine si corresponde un 301 uno-a-uno.

No se declarará un tipo de negocio que contradiga la naturaleza del espacio ni se indexarán borradores, archivos o fichas sin requisitos de calidad.

### Verification responsibility

The canonical profile will include `verified_by_user_id` and `verification_method`, alongside `last_verified_at` and structured sources. To remain published, the verifier must be an authorized internal user and the verification of hours, contact, and reservations must be less than 90 days old. When that period expires, the profile remains available for editorial review but is no longer public or indexable as current.

Future assignment of external representatives to artists, festivals, and PeÃ±as is not part of this module. It requires a cross-domain specification for claims, human approval, entity permissions, revocation, and audit; the verification field does not grant ownership or delegated permissions.

## Risks / Trade-offs

- [Datos legacy incompletos o duplicados] → Auditar antes de migrar, conservar referencia de origen y activar un lote piloto manualmente verificado.
- [Horarios y contactos cambian con frecuencia] → Mostrar fecha de verificación, priorizar canales oficiales y despublicar o marcar para revisión cuando la información venza.
- [Relaciones falsas entre lugares, artistas y eventos] → Exigir pivotes o referencias editoriales persistidas; prohibir la inferencia por texto.
- [URLs territoriales de bajo valor SEO] → Limitar indexación a landings con contenido y resultados, con canonicalización para filtros arbitrarios.
- [Colisión con `Penia`/`penias` legacy] → Relevamiento técnico previo para decidir nombre de tabla, modelo y transición antes de generar una migración.
- [Ampliación prematura a marketplace B2B] → Mantener reservas como enlace de contacto y dejar pagos, reclamación de fichas y productos comerciales fuera del MVP.

## Migration Plan

1. Inventariar datos, rutas, tráfico y dependencias de Peñas legacy en modo solo lectura.
2. Confirmar el modelo canónico, llaves territoriales, pivote con Eventos, permisos y estrategia de slug.
3. Crear migraciones nuevas y reversibles, modelos, requests, policies y pruebas, sin alterar las migraciones históricas.
4. Implementar admin, API, auditor y frontend detrás de una bandera de release o equivalente ya existente.
5. Importar o cargar manualmente un lote piloto, verificar cada ficha, ejecutar smoke y validar SEO, sitemap y rendimiento.
6. Habilitar el directorio y, solo tras confirmar equivalencias, aplicar redirecciones 301 de URLs legacy.

Rollback: deshabilitar la superficie nueva, excluirla de navegación y sitemap, y revertir la migración únicamente si no contiene datos aprobados. Una vez cargados datos editoriales, el rollback será lógico mediante estado `archived`, nunca borrado automático.

## Relevamiento inicial - 2026-09-02

- La tabla legacy `penias` existe pero esta vacia. Su modelo `Penia` solo expone `user` e `images`; no cuenta con estado editorial canonico, datos territoriales ni detalle publico funcional.
- La ruta legacy `/penias-folkloricas-de-argentina` conserva dos rutas publicas. La vista enlaza incorrectamente a Noticias y el controlador declara un detalle que no implementa. No hay coincidencias de uso en el log local disponible, por lo que no se define migracion ni redirect 301 en esta etapa.
- El contrato canonico sera `App\\Models\\PeniaProfile` sobre la tabla nueva `penia_profiles`. Asi se evita alterar la tabla/modelo legacy y se conserva una separacion reversible y auditable.
- La URL publica canonica propuesta es `/penias/{slug}`. No colisiona con las rutas actuales; la base legacy se mantiene sin cambios hasta que existan equivalencias uno-a-uno auditadas.
- `Provincia`, `Locality`, `Event`, `Interprete`, `Festival` y `media_assets` son contratos reutilizables. `Venue` no tiene modelo canonico, por lo que queda fuera del MVP.
- La relacion inicial confirmada sera solo `penia_profile_event`. Relaciones directas con artistas, festivales, noticias o enciclopedia quedan fuera hasta que exista curacion editorial persistida.
- API y autorizacion reutilizaran el patron de Festivales: lectura con token Sanctum y escritura para `administrador`; backoffice para administrador, prensa y colaborador segun propiedad.
- Falta aprobacion funcional para el umbral de vigencia de contacto, horarios y reservas antes de crear las reglas de publicacion y la migracion.

## Retiro de legado autorizado - 2026-09-03

- Por autorización explícita, se retiró la tabla vacía `penias`, la FK nullable de `penia_profiles.legacy_penia_id`, el modelo `Penia`, las rutas `/penias-folkloricas-de-argentina*`, su controlador y vista.
- No se implementó 301: no existían registros, tráfico ni equivalencias canónicas auditables.
- `PeniaProfile` es la única entidad de Peñas vigente. Se cargaron diez perfiles y diez eventos ficticios, claramente identificados como demo exclusiva de DEV.

## Open Questions

- ¿La implementación vigente de `Penia` y la tabla `penias` permiten coexistencia con un nuevo modelo o requieren un nombre canónico distinto?
- ¿Qué estrategia de URL corta se aprobará para el módulo (`/penias/{slug}` u otra) y qué equivalencias legacy justifican un 301?
- ¿Cuál será el umbral temporal para considerar vencida una verificación de horarios, contacto y reservas?
- ¿El primer lote piloto se limitará a una o más provincias para asegurar capacidad editorial de mantenimiento?
- ¿Qué campos del auditor y de Content Refresh ya pueden reutilizarse sin extender contratos compartidos?

# 06 - Endpoints API

## Extensión Instagram Noticias (2026-11-11, implementada local; pendiente deploy)

Mismo operador PAT limitado y middleware de publicaciones; bandera Instagram apagada por defecto. `/api/v1/social-publications/instagram`: GET `candidates`, POST `claims`, POST `{target}/render`, POST `{target}/begin`, POST `{target}/container`, POST `{target}/result` y GET `{target}`. Cola de noticias públicas con éxito Facebook, independientes por destino IG. `container` recibe attempt_id, lease y container_id y vuelve a comprobar el snapshot antes de autorizar media_publish. `result` admite media_id numérico y permalink Instagram real en external_url; callbacks/status siguen disponibles al pausar para conciliar.

GET público `/api/v1/social-publication-assets/{sha256}.jpg`: solo JPEG generado, hash de64 caracteres hexadecimales, sin acceso a otros archivos; noindex, image/jpeg y caché de24h, sin regenerar la imagen. APIs autenticadas no-store. Guía completa de contrato/despliegue: `resources/integrations/n8n/INSTAGRAM.md`; no implica activación productiva.

> Estado real de la API versionada al **2026-09-08**.
> Describe la superficie vigente y sus restricciones, alineada con `00_estado_actual.md`.

---

## 1. Resumen

La API actual:

- vive bajo `/api` y `/api/v1`;
- usa `auth:sanctum` para lectura y escritura;
- restringe escritura a `role:administrador`;
- expone recursos para noticias, enciclopedia, discos, canciones, comidas, festivales, artistas, mitos, eventos, Peñas y Radios evergreen.

---

## 2. Convenciones vigentes

- lectura autenticada; no existe una API publica anonima versionada para estos recursos;
- paginacion por defecto de 15 en la mayoria de los listados;
- coexistencia de payloads modernos y legacy segun recurso;
- en `news` y `events` conviven campos canonicos y aliases legacy;
- `knowledge-articles` es el contrato mas consistente del proyecto.

---

## 3. Recursos activos

| Recurso | Rutas base | Tabla/modelo real | Estado |
|---|---|---|---|
| Usuario autenticado | `/api/user` | `users` / `User` | Activo |
| Noticias | `/api/v1/news` | `news` / `News` | Activo |
| Enciclopedia | `/api/v1/knowledge-articles` | `knowledge_articles` / `KnowledgeArticle` | Activo |
| Categorias enciclopedia | `/api/v1/knowledge-categories` | `knowledge_categories` / `KnowledgeCategory` | Activo |
| Discos | `/api/v1/albums` | `albunes` / `Album` | Activo |
| Canciones | `/api/v1/songs` | `canciones` / `Cancion` | Activo |
| Recetas | `/api/v1/foods` | `comidas` / `Comida` | Activo |
| Festivales | `/api/v1/festivals` | `festivales` / `Festival` | Activo |
| Artistas | `/api/v1/artists` | `interpretes` / `Interprete` | Activo |
| Mitos | `/api/v1/myths` | `mitos` / `Mito` | Activo |
| Eventos | `/api/v1/events` | `events` / `Event` | Activo |
| Peñas | `/api/v1/penia-profiles` | `penia_profiles` / `PeniaProfile` | Desplegado; validación operativa pendiente |
| Señales de radio | `/api/v1/radio-signals` | `radio_signals` / `RadioSignal` | Desplegado; validación operativa pendiente |
| Programas de radio | `/api/v1/radio-programs` | `radio_programs` / `RadioProgram` | Desplegado; validación operativa pendiente |

---

## 4. Observaciones por familia

### Noticias

- usa `news` como tabla canonica;
- acepta alias legacy como `titulo`, `noticia`, `publicar` y `user_id`;
- soporta filtros editoriales y relaciones basicas.

### Enciclopedia

- acepta categoria por ID, slug o nombre;
- devuelve `BLOQUEADO_CATEGORIA` cuando la familia evergreen es invalida o inactiva;
- soporta relaciones N:M;
- mantiene publish/unpublish y archivado controlado.

### Festivales

- CRUD activo y documentable;
- backend y frontend ya fueron modernizados;
- la API sigue siendo menos rica que el backend respecto de relaciones editoriales N:M.

### Artistas, Recetas y Mitos

- recursos operativos y alineados con la modernizacion tecnica desplegada;
- integrados con la bandeja editorial a nivel operativo;
- la API sigue conviviendo con contratos legacy-vivos en nombres y persistencia.

### Discos y Canciones

- siguen siendo recursos operativos de alto valor por cobertura y trafico;
- no deben considerarse habilitados para automatizacion o ampliacion de letras sin gate humano de derechos.

### Peñas y Radios

- la lectura y escritura v1 requieren `auth:sanctum`; la escritura respeta policies y estados editoriales;
- Peñas usa `penia-profiles`; Radios separa señal, canal de escucha, programa y franja semanal;
- los listados aceptan filtros de territorio, búsqueda y atributos propios del directorio;
- Content Refresh crea propuestas en `draft/pending` y preserva campos omitidos en actualizaciones; el piloto real sigue pendiente antes de automatizar volumen.

---

## 5. Filtros y restricciones relevantes

- `GET /api/v1/news`: filtros editoriales y por categoria.
- `GET /api/v1/knowledge-articles`: `search`, categoria, estado editorial, rango y `per_page`.
- `GET /api/v1/festivals`: `province_id`, `mes_id`, `published_only`.
- `GET /api/v1/events`: estado editorial, provincia, tipo, rango y `per_page`.

---

## 6. Reglas operativas

- no asumir que una URL moderna implica una tabla moderna;
- usar siempre nombres canonicos reales en consultas nuevas;
- documentar explicitamente toda compatibilidad legacy que se mantenga;
- no presentar la API como publica anonima ni como contrato totalmente unificado;
- cualquier automatizacion editorial externa debe respetar el gate `ENVIAR_API` y los estados de revision humana del proceso.

---

## 7. Historial util conservado

Se mantiene como antecedente valido que:

- la serializacion todavia no esta completamente unificada;
- `/artists`, `/albums`, `/foods` y `/myths` siguen mapeando a tablas/modelos historicos;
- Enciclopedia sigue siendo la referencia mas madura para contratos editoriales modernos.

## Publicaciones sociales — piloto local

Base `/api/v1/social-publications`. PAT Sanctum real sin wildcard y operador NO administrador en allowlist con permisos `social-publications:read` / `social-publications:execute`. No acepta cookie como sustituto ni token Ingesta. Rate limit lectura 60/min y mutación 30/min; respuestas privadas sin cache.

- GET `/candidates`: content_kind news/events, provider facebook, destination_key configurado, per_page 1..50. Puede devolver menos filas que per_page al descartar contenido retirado/vencido; metadata total refiere habilitaciones reservables antes de la validación editorial final.
- POST `/claims`: content_kind, content_id, provider facebook, destination_key y opcional n8n_execution_id. Header Idempotency-Key UUID. 201 con lease una sola vez; replay 200 sin lease. Conflicto 409.
- POST `/{target}/begin`: attempt_id, lease, content_hash, asset_reference HTTPS y asset_sha256. Autoriza un solo envío; replay 409.
- POST `/{target}/result`: attempt_id, lease, outcome success/definitive_failure/uncertain; éxito exige external_post_id `<PAGE>_<POST>`. Fallo seguro exige error_code normalizado, provider_http_status 4xx distinto de 408 y failure_evidence provider_rejected. No se reciben payloads de Meta ni mensajes de error crudos.
- GET `/{target}`: estado/evidencia allowlist sin secretos.

Bandera por defecto apagada: candidates/claims/begin 503; estado y callbacks de envíos iniciados siguen disponibles. La API no habilita contenidos ni hace llamadas externas. Contrato operativo y casos de pérdida de respuesta: `resources/integrations/n8n/README.md`. Solo News/Eventos y la Página institucional en este MVP.

Actualización render binario 2026-11-10: POST `/{target}/render` exige attempt_id y lease del intento reservado; devuelve PNG base64 1080x1350, SHA256 y referencia sha256:<digest>. Rate limit 10/min, requiere execute y bandera activa; respuesta privada. `begin` acepta esta referencia además de HTTPS legacy y verifica archivo privado/hash/relación con render del mismo intento. La salida se sube multipart desde n8n, sin hosting público adicional. Fecha/lugar verificados de eventos se incluyen en snapshot y hash; fonts privadas se configuran por entorno.

### Noticias automática — actualización2026-11-11

Con news_automatic=true (nuevo default), GET candidates para news o tipo omitido consulta News directamente: ID>news_after_id (default833), orden ascendente, mismo estado publishedVisible del frontend. No exige eligibility.enabled. Respuesta y paginación sin cambios; GET no escribe y excluye destinos completados, bloqueados o aún no reintentables. POST claims crea eligibility/request/target faltantes bajo política interna autorizada y actor operador; conserva publication_mode y todos los locks/hashes/leases/resultados. El token sigue sin disponer de un endpoint genérico de habilitación y no habilita Eventos. Begin vuelve a exigir contenido público actual. Los clientes n8n no requieren cambios de contrato ni credenciales; deploy/config:cache necesario, sin migraciones.

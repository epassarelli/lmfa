# 06 - Endpoints API

> Estado real de la API versionada al **2026-09-01**.
> Describe la superficie vigente y sus restricciones, alineada con `00_estado_actual.md`.

---

## 1. Resumen

La API actual:

- vive bajo `/api` y `/api/v1`;
- usa `auth:sanctum` para lectura y escritura;
- restringe escritura a `role:administrador`;
- expone recursos para noticias, enciclopedia, discos, canciones, comidas, festivales, artistas, mitos y eventos.

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

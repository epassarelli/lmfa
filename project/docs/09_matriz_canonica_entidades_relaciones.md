# 09 - Matriz Canonica de Entidades y Relaciones

> Vista resumida y operativa alineada al corte del **2026-09-04**.
> No reemplaza `02_modelo_datos.md`; sirve como referencia cruzada rapida.

---

## 1. Nucleo editorial y operativo

| Dominio | Tabla real | Modelo | Estado | Relaciones clave |
|---|---|---|---|---|
| Noticias | `news` | `News` | Canonica hibrida | `Categoria`, `Interprete`, `Festival`, `User`, `Organization` |
| Eventos | `events` | `Event` | Canonica hibrida | `Provincia`, `Interprete`, `Festival`, `User`, `Organization` |
| Artistas | `interpretes` | `Interprete` | Legacy-viva operativa | `News`, `Event`, `Festival`, `Album`, `Cancion` |
| Discos | `albunes` | `Album` | Legacy-viva operativa | `Interprete`, `Cancion` |
| Canciones | `canciones` | `Cancion` | Legacy-viva operativa | `Interprete`, `Album`, `KnowledgeArticle` |
| Festivales | `festivales` | `Festival` | Activa modernizada | `Provincia`, `Locality`, `Mes`, `News`, `Event`, `Interprete`, `KnowledgeArticle` |
| Recetas | `comidas` | `Comida` | Activa modernizada | media y relaciones operativas simples |
| Mitos | `mitos` | `Mito` | Activa modernizada | media y relaciones operativas simples |
| Enciclopedia categorias | `knowledge_categories` | `KnowledgeCategory` | Canonica | jerarquia y `KnowledgeArticle` |
| Enciclopedia articulos | `knowledge_articles` | `KnowledgeArticle` | Canonica | `Interprete`, `Cancion`, `Album`, `Festival`, `Event`, `Provincia`, relacionados |
| Media | `media_assets` | `MediaAsset` / `Image` | Canonica | relacion polimorfica |
| Contribuciones | `contributions` | `Contribution` | Activa | relacion polimorfica de contenido |
| Organizaciones | `organizations` | `Organization` | Activa | `User`, `SocialAccount`, `PublicationRequest` indirecta |
| Publicacion | `publication_requests`, `publication_targets`, `publication_attempts`, `publication_templates` | varios | Activa | circuito Pasarela |
| Newsletter | `newsletter_subscribers` | `NewsletterSubscriber` | Activa | `User` cuando aplica |
| Legal | `data_deletion_requests` | `DataDeletionRequest` | Activa | trazabilidad Meta/Facebook |
| Peñas | `penia_profiles` | `PeniaProfile` | Canónica evergreen en DEV | `Provincia`, `Locality`, `Event`, `User`, media |
| Señales de radio | `radio_signals` | `RadioSignal` | Canónica evergreen en DEV | `Provincia`, `Locality`, `RadioListeningChannel`, `RadioProgram`, `User` |
| Programas de radio | `radio_programs` | `RadioProgram` | Canónica evergreen en DEV | `RadioSignal` opcional, `RadioProgramSlot`, `User` |

---

## 2. Relaciones pivot que siguen importando

- `interprete_noticia`
- `event_interprete`
- `event_festival`
- `festival_news`
- `festival_interprete`
- `albunes_canciones`
- `knowledge_article_interprete`
- `knowledge_article_cancion`
- `knowledge_article_album`
- `knowledge_article_festival`
- `event_knowledge_article`
- `knowledge_article_provincia`
- `knowledge_article_related`
- `classified_tag`
- `penia_profile_event`

---

## 3. Lectura operativa por tipo de entidad

### Canonicas modernas

- `news`
- `events`
- `knowledge_categories`
- `knowledge_articles`
- `media_assets`
- `penia_profiles`
- `radio_signals`
- `radio_listening_channels`
- `radio_programs`
- `radio_program_slots`

### Canonicas hibridas

- `news`
- `events`

Observacion:

- siguen exponiendo compatibilidad con campos legacy en modelos y API, pero las consultas nuevas deben usar nombres canonicos reales.

### Legacy-vivas estructurales

- `interpretes`
- `albunes`
- `canciones`
- `categorias`
- varios pivots historicos

Observacion:

- no son residuos descartables; forman parte del contrato real actual.

### Legacy no elegible para contenido nuevo

- `noticias`
- `shows`
- `images`

---

## 4. Alineacion con el estado actual

- Festivales, Artistas, Recetas y Mitos ya cuentan con auditorias y linea base editorial al 2026-09-01.
- Artistas, Recetas y Mitos ya se integran con la bandeja `Contenidos`.
- Discografia/Cancionero sigue operativa pero su evolucion esta frenada por el gate humano de derechos.
- Peñas y Radios tienen dominio, backoffice, API, frontend y Content Refresh en DEV; sus flags permanecen apagados hasta completar staging HTTPS y el piloto editorial controlado.
- La matriz debe leerse junto con `00_estado_actual.md` cuando haya que decidir prioridad o madurez real de una entidad.

---

## 5. Historial util conservado

Se conserva la distincion entre:

- capa canonica moderna;
- capa canonica hibrida;
- capa legacy-viva;
- capa legacy no operativa para contenido nuevo.

Esa distincion sigue siendo clave para evitar supuestos falsos en automatizaciones, auditorias o cambios de modelo.

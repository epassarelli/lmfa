# Diseno tecnico: copa-folklore-2026

## Resumen

El modulo se implementara como una capacidad autocontenida del portal, con tablas propias para torneo, grupos, participantes y partidos. El backend sera la unica fuente de verdad para el modelo de datos. Admin y frontend consumiran ese modelo a traves de Eloquent, servicios dedicados y consultas de lectura. El contenido editorial quedara desacoplado en archivos Markdown.

## Decisiones clave

### 1. Propiedad del modelo

El frente Backend base define:

- migraciones
- modelos
- relaciones
- seeders
- servicios de fixture
- servicios de posiciones

Ningun otro frente redefine estructura, reglas de persistencia ni calculos de dominio.

### 2. Alineacion con stack real

- Frontend publico: Blade + Tailwind CSS 3.x
- Panel admin: Blade + AdminLTE 3
- Sin Bootstrap en frontend publico
- Sin React, sin Vue, sin Livewire nuevo

### 3. Integracion con artistas

Se debe inspeccionar y usar el modelo real `Interprete` con su tabla `interpretes`.

Estrategia:

- `artist_id` nullable en participantes del torneo
- `display_name` obligatorio como fallback editorial
- resolucion de vinculo por nombre exacto o estrategia definida en seeder

### 4. Gestion de resultados

Los votos son el marcador del partido. No existe integracion automatica con Instagram.

Los datos administrables por partido son:

- votos del participante 1
- votos del participante 2
- URL del post de Instagram
- estado
- ganador manual si corresponde
- notas opcionales

### 5. Fases del torneo

MVP funcional:

- grupos: 8 zonas de 4
- clasificacion posterior prevista en datos
- visualizacion simple de llaves por fase sin bracket grafico complejo

El backend debe dejar lista la estructura para `round_16`, `quarter_final`, `semi_final`, `third_place` y `final`, aunque la generacion automatica de eliminatorias puede diferirse si no es necesaria en la primera entrega.

## Modelo de datos propuesto

### folklore_tournaments

Entidad principal del certamen.

Campos:

- `id`
- `name`
- `slug` unique
- `description` nullable
- `year`
- `starts_at` nullable
- `ends_at` nullable
- `status` default `draft`
- `rules` longText nullable
- timestamps

Indices:

- unique `slug`
- index `status`
- index `year`

### folklore_tournament_groups

Grupos o zonas pertenecientes a un torneo.

Campos:

- `id`
- `tournament_id`
- `name`
- `slug` nullable
- `sort_order` default `0`
- timestamps

### folklore_tournament_participants

Participantes del torneo, opcionalmente vinculados a `Interprete`.

Campos:

- `id`
- `tournament_id`
- `group_id` nullable
- `artist_id` nullable
- `display_name`
- `slug` nullable
- `image_path` nullable
- `seed_order` nullable
- `status` default `active`
- timestamps

Consideracion:

- La FK a artistas debera apuntar a la tabla real solo despues de verificar la PK y la compatibilidad del modelo existente.

### folklore_tournament_matches

Partidos del certamen.

Campos:

- `id`
- `tournament_id`
- `group_id` nullable
- `phase`
- `matchday` nullable
- `participant_1_id`
- `participant_2_id`
- `participant_1_votes` unsigned integer default `0`
- `participant_2_votes` unsigned integer default `0`
- `winner_participant_id` nullable
- `status` default `scheduled`
- `scheduled_at` nullable
- `voting_opens_at` nullable
- `voting_closes_at` nullable
- `instagram_url` nullable
- `notes` nullable
- timestamps

Indices sugeridos:

- `tournament_id`, `phase`
- `group_id`, `phase`
- `status`

## Servicios

### FolkloreTournamentFixtureService

Responsabilidad:

- generar round-robin para grupos de 4
- crear 6 partidos por grupo
- crear 48 partidos totales para 8 grupos
- evitar enfrentamientos duplicados dentro del mismo torneo y fase
- asignar `matchday` basico
- dejar puntos de extension para fases eliminatorias

Estrategia sugerida:

- recibir torneo y coleccion de grupos con participantes
- validar que cada grupo tenga exactamente 4 participantes para generacion MVP
- usar combinaciones unicas de pares por grupo
- persistir con transaccion

### FolkloreTournamentStandingService

Responsabilidad:

- calcular tabla por grupo a partir de partidos `group` finalizados
- computar PJ, PG, PE, PP, VF, VC, DIF, PTS
- ordenar por puntos, diferencia, votos a favor, votos en contra
- dejar extension para desempate por enfrentamiento directo y orden manual

Estrategia sugerida:

- servicio puro o semi-puro con salida estructurada para admin y frontend
- entrada principal: torneo o grupo
- consultar solo partidos `status = finished`
- empates: si votos iguales, sumar 1 punto por lado; si no, 3 al ganador

## Superficie HTTP

### Frontend publico

Rutas previstas:

- `/copa-del-folklore-argentino-2026`
- `/copa-del-folklore-argentino-2026/participantes`
- `/copa-del-folklore-argentino-2026/fixture`
- `/copa-del-folklore-argentino-2026/zonas`
- `/copa-del-folklore-argentino-2026/llaves`
- `/copa-del-folklore-argentino-2026/reglamento`

### Admin

Rutas previstas:

- `GET /admin/folklore-tournaments`
- `GET /admin/folklore-tournaments/{tournament}`
- `GET /admin/folklore-tournaments/{tournament}/matches`
- `GET /admin/folklore-tournament-matches/{match}/edit`
- `PUT /admin/folklore-tournament-matches/{match}`

La implementacion final debe alinearse con nombres de rutas existentes del proyecto, idealmente bajo prefijo `backend.*`.

## UI y contenido

### Frontend

Landing:

- hero editorial
- explicacion breve
- proximos partidos
- ultimos resultados
- CTA a Instagram
- navegacion a participantes, fixture, zonas y reglamento

Subpaginas:

- participantes en grilla
- fixture agrupado por jornada
- zonas con tabla y partidos
- llaves agrupadas por fase
- reglamento editorial

### Admin

Vistas minimas:

- listado de torneos
- detalle del torneo
- listado de grupos
- listado de participantes
- listado de partidos
- formulario de edicion de partido

### Contenido editorial

Archivos Markdown en `docs/copa-folklore-2026/`:

- lanzamiento
- reglamento
- copies de Instagram
- historias
- calendario editorial
- nombres de zonas
- hashtags
- criterios de votacion
- aclaracion legal

Debe incluirse textualmente la aclaracion legal obligatoria provista por el usuario.

## Estrategia de testing

Minimo esperado:

- tests feature para creacion y visualizacion base del modulo
- tests del servicio de fixture
- tests del servicio de posiciones
- tests de admin para actualizacion de votos y URL

Restricciones:

- usar `DatabaseTransactions`
- no usar `RefreshDatabase`
- fakear colas o integraciones externas si aparecieran

## Orden de implementacion

1. Backend base
2. Contenido editorial
3. Admin
4. Frontend publico
5. Integracion y validacion final

## Archivos previstos por frente

### Backend base

- `database/migrations/*folklore*`
- `app/Models/FolkloreTournament.php`
- `app/Models/FolkloreTournamentGroup.php`
- `app/Models/FolkloreTournamentParticipant.php`
- `app/Models/FolkloreTournamentMatch.php`
- `app/Services/FolkloreTournamentFixtureService.php`
- `app/Services/FolkloreTournamentStandingService.php`
- `database/seeders/*Folklore*`
- `tests/Feature/*Folklore*`

### Contenido editorial

- `docs/copa-folklore-2026/*.md`

### Admin

- `routes/admin.php`
- `app/Http/Controllers/Backend/*Folklore*`
- `app/Http/Requests/Backend/*Folklore*`
- `resources/views/backend/*folklore*`

### Frontend publico

- `routes/web.php`
- `app/Http/Controllers/Frontend/*Folklore*`
- `resources/views/frontend/*folklore*`

## Riesgos abiertos

- Verificar estructura real de `Interprete` antes de definir FK estricta.
- Verificar convenciones de nombres y permisos en admin existentes para no duplicar patrones.
- Evaluar si las tablas necesitan soporte futuro para desempate manual persistido, sin sobre-disenar el MVP.
- Asegurar que las nuevas URLs no colisionen con slugs existentes ni generen impacto SEO colateral.

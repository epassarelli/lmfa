# 01 - Funcional

> Estado funcional consolidado al **2026-09-01**.
> Este documento describe el comportamiento funcional vigente del portal y distingue entre modulos activos, modulos activos con validacion operativa pendiente y piezas diferidas o parciales.
> Conserva al final un resumen historico del alcance MVP original de la Pasarela para no perder contexto util.

---

## 1. Funcion del documento

`00_estado_actual.md` registra hechos operativos del corte mas reciente. Este documento baja ese estado a reglas funcionales estables:

- que modulos usa hoy el usuario;
- que capacidades estan cerradas a nivel de producto;
- que flujos existen pero siguen pendientes de validacion productiva;
- que piezas son historicas, diferidas o parciales.

No es backlog ejecutable ni propuesta arquitectonica.

---

## 2. Alcance funcional vigente del portal

Mi Folklore Argentino funciona hoy como un portal cultural con dos programas paralelos:

- **autoridad editorial continua**, centrada en mejorar profundidad, calidad, relaciones, SEO y media del inventario publicado;
- **producto y servicios**, centrado en navegacion, agenda, automatizacion, comunidad, distribucion y servicios diferenciales.

La Pasarela de Contenidos ya no debe describirse como el unico alcance funcional del proyecto: es uno de varios modulos activos dentro del ecosistema.

---

## 3. Modulos publicos activos

### 3.1 Navegacion y contenido editorial

Superficies publicas funcionales:

- Home
- Noticias
- Enciclopedia
- Cartelera de eventos
- Biografias / miniportal del artista
- Cancionero / letras
- Discografias
- Festivales y fiestas tradicionales
- Mitos y leyendas
- Recetas
- Clasificados
- Buscador
- Contacto
- Sitemaps y superficie SEO tecnica

### 3.2 Comunidad y recurrencia

Flujos funcionales visibles para usuario:

- suscripcion a newsletter;
- autenticacion social;
- colaboraciones UGC;
- notificaciones basicas asociadas a flujos internos cuando aplica.

### 3.3 Especiales y verticales activas

- Copa del Folklore Argentino 2026 con portada, participantes, zonas, llaves, fixture y reglamento.

---

## 4. Reglas funcionales transversales

### 4.1 Publicacion editorial

- El proyecto no tiene un unico contrato fisico de publicacion.
- `news`, `events` y `knowledge_articles` operan con estados editoriales modernos.
- `interpretes`, `albunes`, `canciones`, `comidas` y `mitos` siguen teniendo base legacy-viva, aunque varios ya fueron modernizados en frontend, backoffice, API y auditoria.
- Todo contenido visible debe respetar estado publicable y fecha efectiva de publicacion segun su modulo.

### 4.2 Calidad editorial

- La operacion editorial prioriza registros con deuda de calidad antes que volumen bruto.
- La curacion se ordena por prioridad editorial, score, visitas y desempate estable por ID.
- La automatizacion no reemplaza la revision humana: el gate operativo sigue siendo `ENVIAR_API`.

### 4.3 SEO y experiencia

- No deben abrirse URLs nuevas ni indexables sin intencion clara, contenido suficiente y consistencia canonica.
- La navegacion debe favorecer descubrimiento entre artistas, obras, festivales, eventos, territorios y evergreen.
- Performance, UX y SEO tecnico forman parte del comportamiento esperado del producto, no de una optimizacion opcional posterior.

---

## 5. Modulos editoriales por entidad

### 5.1 Noticias

Capacidades vigentes:

- CRUD administrativo funcional;
- frontend publico activo;
- API autenticada;
- relaciones con artistas y festivales;
- soporte de estado editorial moderno;
- uso intensivo dentro del radar y la operacion editorial continua.

### 5.2 Eventos

Capacidades vigentes:

- cartelera publica funcional;
- CRUD administrativo;
- API autenticada;
- relaciones con artistas y festivales;
- soporte de agenda por fecha y territorio;
- base apta para recordatorios y difusion posterior.

### 5.3 Enciclopedia

Capacidades vigentes:

- portada, familias y articulos publicados;
- backend admin dedicado;
- API con categorias activas y relaciones N:M;
- contrato editorial mas completo del proyecto;
- soporte para publicacion, despublicacion, SEO y enlazado interno.

### 5.4 Biografias / Artistas

Estado funcional al 2026-09-01:

- modernizacion tecnica desplegada;
- backoffice, API, frontend, SEO/schema y auditor funcionando;
- miniportal activo con noticias, letras, discografia y shows relacionados;
- integracion con bandeja editorial disponible;
- recuperacion masiva de contenido pendiente.

### 5.5 Recetas

Estado funcional al 2026-09-01:

- contrato editorial y campos estructurados modernizados;
- backend, API, frontend y schema activos;
- integracion con auditor y bandeja editorial disponible;
- recuperacion masiva del inventario todavia pendiente.

### 5.6 Mitos y Leyendas

Estado funcional al 2026-09-01:

- contrato cultural modernizado con region y tipo;
- backend, API, frontend y auditor activos;
- integracion con bandeja editorial disponible;
- loteo y curacion pendientes antes de cualquier escala automatizada.

### 5.7 Festivales

Estado funcional al 2026-09-01:

- modernizacion tecnica y Content Refresh en condiciones productivas;
- auditor y cola editorial activos;
- sigue pendiente incorporar visitas reales al criterio del auditor para dejar de usar desempate provisional.

### 5.8 Discografia y Cancionero

Estado funcional:

- frontend, backend y API existen y son relevantes por cobertura y trafico;
- siguen siendo una capa legacy-viva del dominio;
- no debe ampliarse ni automatizarse el cancionero sin definicion humana de derechos, fuentes y politica editorial;
- la modernizacion tecnica/musical no debe tratarse como cerrada hasta que exista decision operativa sobre derechos.

---

## 6. Integracion editorial operativa

La bandeja `Contenidos` y su integracion editorial soportan hoy:

- `Artista`
- `Receta`
- `Mito`

Capacidad vigente:

- `CREAR` y `ACTUALIZAR`;
- `ID_WEB` como requisito para updates;
- `SCORE_CALIDAD` y `FALTANTES`;
- gate `ENVIAR_API`;
- uso de `RESULTADO_API` y limpieza posterior del gate tras exito.

Restriccion vigente:

- antes de escalar volumen deben cerrarse en produccion los seis casos controlados de crear/actualizar una entidad de cada tipo.

---

## 7. Pasarela de Contenidos

### 7.1 Alcance vigente

La Pasarela de Contenidos existe como modulo funcional del proyecto para:

- organizaciones;
- cuentas sociales;
- solicitudes y destinos de publicacion;
- templates;
- dashboards de publicador y admin;
- intentos, errores, trazabilidad y notificaciones.

### 7.2 Estado real

- el codigo y la estructura funcional estan implementados;
- su validacion end-to-end en produccion sigue pendiente;
- no debe tratarse todavia como servicio cerrado ni como fuente de ingresos validada.

### 7.3 Reglas funcionales vigentes

- una falla en un canal no debe bloquear la publicacion en otros;
- cada intento por canal es independiente;
- los canales oficiales del portal requieren aprobacion institucional;
- tokens vencidos o cuentas desconectadas deben bloquear el canal afectado e informar el estado;
- la programacion y los reintentos deben conservar trazabilidad.

---

## 8. UGC, legal y flujos operativos complementarios

### 8.1 Colaboraciones UGC

- existe flujo unificado de contribuciones;
- noticias ya tienen validacion end-to-end;
- el resto del circuito requiere validacion operativa completa en produccion.

### 8.2 Legales y Meta/Facebook

Superficies funcionales vigentes:

- `/privacidad`
- `/condiciones`
- `/eliminacion-de-datos`
- `GET /deleteuserdata`
- `POST /deleteuserdata`
- `/deleteuserdata/status/{confirmationCode}`

El flujo es funcional a nivel local/documental, con persistencia y trazabilidad, pero requiere la aplicacion de la migracion correspondiente y configuracion final en los entornos necesarios.

---

## 9. Modulos parciales o diferidos

No deben documentarse como modulos cerrados:

- `Entrevistas`: rutas activas pero superficie incompleta;
- `Radios`: frontend y modelo existentes, sin cierre administrativo documentado;
- `Penias`: frontend y modelo existentes, sin cierre administrativo documentado;
- `Videos`: componente incompleto y no consolidado.

---

## 10. Prioridades funcionales abiertas

Brechas funcionales que siguen abiertas segun el estado operativo actual:

1. cerrar el piloto controlado de seis operaciones de Content Refresh;
2. ejecutar lotes pequenos de curacion sobre Biografias, Recetas y Mitos;
3. incorporar visitas al auditor de Festivales;
4. validar Pasarela y UGC end-to-end en produccion;
5. definir el siguiente release funcional por impacto en descubrimiento y recurrencia;
6. aprobar politica de derechos antes de automatizar Discografia/Cancionero.

---

## 11. Historial util conservado: alcance MVP original de la Pasarela

El documento anterior estaba enfocado casi por completo en la Pasarela de Contenidos y Distribucion Multicanal. Ese alcance sigue siendo util como antecedente del MVP:

- alta de usuarios publicadores;
- organizaciones con miembros y roles;
- carga de eventos y noticias;
- media asociada;
- workflow de moderacion;
- publicacion en portal;
- publicacion a redes seleccionadas;
- programacion, republicaciones, estados por canal, logs y dashboard.

Ese enfoque ya no alcanza para describir todo MFA, pero se conserva aqui como historia funcional del modulo.

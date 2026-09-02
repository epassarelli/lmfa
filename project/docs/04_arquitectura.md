# 04 - Arquitectura Tecnica

> Estado real consolidado al **2026-09-01**.
> Describe la arquitectura vigente y distingue entre componentes operativos, componentes parciales y gates pendientes.

---

## 1. Resumen

Mi Folklore Argentino funciona hoy como un **monolito Laravel 10 modularizado** que concentra:

- frontend publico editorial;
- backend administrativo;
- Pasarela de Contenidos;
- API REST autenticada con Sanctum;
- integracion editorial asistida;
- modulos modernos y legacy-vivos conviviendo en el mismo core.

---

## 2. Capas vigentes

- `routes/web.php`: frontend publico y colaboraciones.
- `routes/admin.php`: backend y Pasarela bajo `/admin`.
- `routes/api.php`: API REST v1 autenticada.
- `app/Http/Controllers/Frontend`: superficies publicas.
- `app/Http/Controllers/Backend`: CRUD y operacion editorial.
- `app/Http/Controllers/Api`: contratos API.
- `app/Models`: dominio Eloquent.
- `app/Services`: servicios editoriales, media, linking, torneos y publicacion.
- `app/Jobs`: newsletter y publicacion asincrona puntual.

---

## 3. Stack real

- Laravel 10
- PHP 8.x
- MariaDB/MySQL
- Blade clasico
- Tailwind CSS 3.x en frontend publico
- AdminLTE 3 en backend
- Sanctum
- Socialite
- Spatie Permission
- Livewire solo donde ya existe
- Vite

La aplicacion soporta colas, pero el runtime documentable no debe depender de Redis u Horizon como requisito obligatorio.

---

## 4. Dominios funcionales dentro del monolito

### 4.1 Editorial publico

- noticias;
- eventos/cartelera;
- biografias/artistas;
- canciones y discografia;
- festivales;
- recetas;
- mitos;
- enciclopedia;
- clasificados;
- torneo.

### 4.2 Operacion y administracion

- CRUDs editoriales;
- roles, permisos y usuarios;
- moderacion;
- newsletter;
- auditorias y lineas base editoriales.

### 4.3 Distribucion y servicios

- Pasarela de Contenidos;
- organizaciones;
- cuentas sociales;
- publication requests / targets / attempts / templates;
- integracion editorial con bandeja `Contenidos`.

---

## 5. Estado arquitectonico por frente

### 5.1 Festivales

- modernizacion tecnica desplegada;
- frontend, backoffice, API y auditor operativos;
- pendiente incorporar visitas reales al criterio del auditor.

### 5.2 Biografias, Recetas y Mitos

- modernizacion tecnica desplegada;
- integracion con bandeja editorial activa;
- auditorias reales ejecutadas;
- curacion masiva pendiente, pero ya no son modulos "solo legacy".

### 5.3 Discografia y Cancionero

- siguen siendo parte del dominio operativo real;
- no deben documentarse como modernizados de punta a punta;
- quedan sujetos a decision humana de derechos antes de automatizacion o ampliacion.

### 5.4 Pasarela

- arquitectura de modulo implementada;
- validacion end-to-end productiva pendiente;
- debe tratarse como capacidad existente, no como servicio ya cerrado.

---

## 6. Integraciones reales

Conectores implementados en el codigo:

- Facebook
- Instagram
- Telegram
- Native portal

No deben documentarse como componentes operativos cerrados hoy:

- X
- TikTok
- LinkedIn

---

## 7. Seguridad y entrada HTTP

Arquitectura de middleware relevante:

- `TrustProxies`
- `EnforceCanonicalDomain`
- `HandleCors`
- `PreventRequestsDuringMaintenance`
- `ValidatePostSize`
- `TrimStrings`
- `ConvertEmptyStringsToNull`

La canonicalizacion de host y protocolo ya forma parte del runtime Laravel y no depende solo de Apache.

---

## 8. Performance como criterio arquitectonico vigente

La arquitectura actual incorpora como criterio estable:

- paginacion server-side en listados pesados;
- cache de bloques y sidebars;
- bundle publico separado del backend;
- reduccion de JS global;
- carga diferida de terceros;
- mejor politica de imagenes y variantes;
- `content-visibility` en bloques secundarios.

Esto ya no debe tratarse como optimizacion puntual, sino como politica transversal de implementacion.

---

## 9. Matriz resumida de estado

| Componente | Estado |
|---|---|
| Frontend publico | Activo |
| Backend admin | Activo |
| API REST v1 | Activa |
| Pasarela | Implementada con validacion productiva pendiente |
| UGC | Activo con validacion productiva parcial |
| Integracion editorial Artista/Receta/Mito | Activa con piloto controlado pendiente |
| Musica y letras | Operativas con gate de derechos |

---

## 10. Historial util conservado

Se conserva como antecedente que la documentacion previa discutia dependencias como Redis/Horizon, n8n o conectores no implementados. Esos puntos pueden existir como vision futura, pero no describen la arquitectura operativa comprobada al 2026-09-01.

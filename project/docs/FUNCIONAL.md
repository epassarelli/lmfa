# 📄 Documento Funcional — Mi Folklore Argentino

> **Proyecto:** Mi Folklore Argentino (MFA)  
> **URL:** [https://mifolkloreargentino.com.ar](https://mifolkloreargentino.com.ar)  
> **Tipo:** Portal web de contenido cultural  
> **Propietario:** Proyecto propio  
> **Última actualización:** 2026-04-26

---

## 1. Descripción General

**Mi Folklore Argentino** es un portal web dedicado a la difusión y preservación de la cultura folklórica argentina. Funciona como un hub centralizado de información sobre artistas, música, noticias, eventos, festivales, recetas típicas, mitos y leyendas, peñas, radios y más.

El sitio combina un **portal público** orientado al SEO y la consulta de contenido, un **panel de administración** (backend) para la gestión editorial, una **API REST** para integración con sistemas externos y un sistema de **contenido generado por usuarios** (UGC) que permite colaboraciones de la comunidad.

---

## 2. Público Objetivo

| Segmento | Descripción |
|---|---|
| **Amantes del folklore** | Personas interesadas en la música, danzas y tradiciones argentinas |
| **Músicos y artistas** | Intérpretes y compositores del género folklórico |
| **Productores y organizadores** | Responsables de festivales, peñas y eventos folklóricos |
| **Periodistas y comunicadores** | Profesionales que cubren la escena folklórica |
| **Público general** | Personas con curiosidad por la cultura argentina |
| **Turistas** | Visitantes interesados en festivales, gastronomía típica y tradiciones regionales |

---

## 3. Módulos Funcionales

### 3.1 Portal Público (Frontend)

#### 3.1.1 Home
- Página principal con carrusel/grilla de las **últimas 50 noticias**.
- Secciones organizadas por categorías de noticias.
- Sidebar con formulario de newsletter, enlaces a redes sociales y botón de donación.
- Datos estructurados (JSON-LD) para SEO con schema de `WebSite` y `Organization`.
- Meta title y meta description personalizados.

#### 3.1.2 Noticias
- **Listado general** (`/noticias-del-folklore-argentino`): Muestra todas las noticias activas.
- **Detalle de noticia** (`/noticias-del-folklore-argentino/{slug}`): Vista individual de noticia general.
- **Noticias por artista** (`/{artista}/noticias`): Noticias vinculadas a un intérprete específico.
- **Detalle de noticia de artista** (`/{artista}/noticias/{slug}`): Vista individual dentro del miniportal del artista.
- Relación muchos a muchos con intérpretes.
- Categorización por categoría (Actualidad, Festivales, Lanzamientos, Entrevistas, Cartelera).
- Contador de visitas.
- Soporte para múltiples imágenes (relación polimórfica).

#### 3.1.3 Miniportal del Artista (Intérpretes)
Cada artista tiene su propio "miniportal" accesible mediante slug directo (`/{slug-del-artista}`), con subsecciones internas:

| Subsección | Ruta | Descripción |
|---|---|---|
| Perfil | `/{artista}` | Página principal del artista |
| Biografía | `/{artista}/biografia` | Biografía detallada |
| Noticias | `/{artista}/noticias` | Noticias relacionadas al artista |
| Letras | `/{artista}/letras` | Canciones y letras del artista |
| Discografía | `/{artista}/discografia` | Álbumes y discos |
| Shows | `/{artista}/shows` | Próximos eventos y shows |
| Entrevistas | `/{artista}/entrevistas` | 🔮 Diferido — próxima versión. Rutas activas pero controller sin métodos ni vistas. |

- **Índice general de artistas** (`/biografias-de-artistas-folkloricos`) con navegación alfabética por letra.
- Datos del artista: nombre, biografía, foto, teléfono, correo, Instagram, Twitter, YouTube.
- Estado activo/inactivo para control de visibilidad.

#### 3.1.4 Canciones / Letras
- **Índice general** (`/letras-de-canciones-folkloricas`) con navegación alfabética.
- **Detalle por artista** (`/{artista}/letras/{cancion}`).
- Campos: título, letra completa, enlaces a YouTube y Spotify.
- Relación con álbumes (muchos a muchos con tabla pivote `albunes_canciones`).
- Contador de visitas.

#### 3.1.5 Discografías / Álbumes
- **Índice general** (`/discografias-del-folklore-argentino`).
- **Detalle por artista** (`/{artista}/discografia/{slug}`).
- Campos: nombre del álbum, año, foto de portada, enlace a Spotify.
- Relación con canciones (muchos a muchos con orden en pivote).

#### 3.1.6 Cartelera de Shows / Eventos
- **Índice general** (`/cartelera-de-eventos-folkloricos`): Muestra eventos futuros ordenados por fecha.
- **Detalle** (`/cartelera-de-eventos-folkloricos/{slug}`).
- **Shows por artista** (`/{artista}/shows/{slug}`).
- Campos: nombre, detalle, fecha, hora, lugar, dirección, precio de entrada, link de entradas, coordenadas GPS (lat/lng), provincia, imagen destacada.
- Soporte para flag "destacado".

#### 3.1.7 Festivales
- **Índice** (`/festivales-y-fiestas-tradicionales`).
- **Detalle** (`/festivales-y-fiestas-tradicionales/{slug}`).
- Campos: título, detalle, foto, provincia, mes de realización.
- Filtrado por provincia y mes.

#### 3.1.8 Radios
- 🔮 **Módulo diferido — próxima versión.** Las rutas de índice existen pero el detalle (`/radios-de-folklore-argentino/{slug}`) está sin implementar. Sin gestión backend.

#### 3.1.9 Peñas Folklóricas
- 🔮 **Módulo diferido — próxima versión.** Las rutas de índice existen pero el detalle (`/penias-folkloricas-de-argentina/{slug}`) está sin implementar. Sin gestión backend.

#### 3.1.10 Mitos y Leyendas
- **Índice** (`/mitos-y-leyendas-argentinas`) con navegación alfabética.
- **Por letra** (`/mitos-y-leyendas-argentinas/letra/{letra}`).
- **Detalle** (`/mitos-y-leyendas-argentinas/{slug}`).
- Campos: título, contenido del mito, foto.

#### 3.1.11 Recetas de Comidas Típicas
- **Índice** (`/recetas-de-comidas-tipicas-argentinas`) con navegación alfabética.
- **Por letra** (`/recetas-de-comidas-tipicas-argentinas/letra/{letra}`).
- **Detalle** (`/recetas-de-comidas-tipicas-argentinas/{slug}`).
- Campos: título, receta (contenido), imágenes.

#### 3.1.12 Clasificados
- **Índice** (`/avisos-clasificados`): Listado público de clasificados activos.
- **Detalle** (`/avisos-clasificados/{slug}`).
- **Publicar** (`/avisos-clasificados/publicar`): Requiere autenticación.
- **Mis avisos** (`/avisos-clasificados/mis-avisos`): Panel del usuario para ver sus clasificados.
- **Renovar** (`/avisos-clasificados/renovar/{id}`): Renovación de avisos expirados.
- Campos: título, descripción, precio, ubicación, información de contacto, WhatsApp, fecha de expiración.
- Sistema de moderación: estados `pendiente`, `activo`, `rechazado`.
- Relación con categorías y tags.

#### 3.1.13 Colaboraciones (UGC)
- **Índice** (`/admin/contribuir`): Panel para ver contribuciones propias (requiere auth).
- **Crear** (`/admin/contribuir/crear/{type}/{id?}`): Formulario para proponer contenido nuevo o ediciones.
- Sistema polimórfico: las contribuciones pueden asociarse a cualquier tipo de entidad.
- Flujo de moderación: `pending` → `approved` / `rejected`.
- Almacena payload (datos propuestos) como JSON.
- ⚠️ **Estado:** Flujo a mitad de camino. Fue migrado de `/colaborar` al backend pero no se verificó qué funciona. Requiere prueba completa antes de considerar operativo.

#### 3.1.14 Contacto
- **Formulario** (`/contacto`): Formulario público de contacto.
- Envío de emails al equipo y respuesta automática al remitente.

#### 3.1.15 Buscador
- **Buscador global** (`/buscar`): Búsqueda transversal en múltiples entidades del sistema.

#### 3.1.16 Compartir en Redes
- Funcionalidad para compartir contenido en redes sociales.
- Registro de logs de compartidos (`ShareLog`).

#### 3.1.17 Newsletter
- **Suscripción** vía formulario en el sidebar (componente presente en todas las páginas).
- **Desuscripción** mediante enlace con token único (`/newsletter/unsubscribe/{token}`).
- Envío semanal automático con contenido reciente (en fase de prueba).
- Fuente de suscripción rastreable.

#### 3.1.18 Sitemap
- **Sitemap general** (`/sitemap.xml`): Incluye todas las URLs del sitio.
- **Sitemap de noticias** (`/sitemap-news.xml`): Específico para Google News.

---

### 3.2 Panel de Administración (Backend)

Accesible en `/admin`, protegido por autenticación. Basado en **AdminLTE**.

#### 3.2.1 Dashboard
- Vista resumen con contadores de todas las entidades principales:
  - Noticias, canciones, usuarios, intérpretes, shows, discos, comidas, festivales.
  - Contribuciones pendientes de moderación.
- Información del usuario logueado: roles y permisos.

#### 3.2.2 Gestión de Contenido (CRUD completo)

| Módulo | Descripción |
|---|---|
| **Intérpretes** | Alta, edición y eliminación de artistas |
| **Noticias** | Gestión de noticias con categorización y asociación a artistas |
| **Canciones** | Gestión de letras con creación AJAX y listados con DataTables |
| **Álbumes/Discos** | Gestión de discografía con relación a artista |
| **Shows** | Gestión de eventos con geolocalización |
| **Festivales** | Gestión de festivales por provincia y mes |
| **Mitos** | Gestión de mitos y leyendas |
| **Comidas** | Gestión de recetas de comidas típicas |
| **Clasificados** | Gestión y moderación de avisos clasificados (aprobar/rechazar) |
| **Categorías** | Gestión de categorías para clasificados |
| **Tags** | Gestión de etiquetas |

#### 3.2.3 Moderación
- **Contribuciones (UGC)**: Revisar, aprobar o rechazar contribuciones de usuarios con comentario de moderador.
- **Clasificados**: Aprobar o rechazar avisos con comentario de moderador.

#### 3.2.4 Gestión de Usuarios y Permisos
- **Usuarios**: CRUD completo de usuarios del sistema.
- **Roles**: Gestión de roles (basado en Spatie Permission).
- **Permisos**: Gestión granular de permisos.

#### 3.2.5 Newsletter (Backend)
- Listado de suscriptores del newsletter.
- Toggle de estado activo/inactivo por suscriptor.

---

### 3.3 API REST

API versionada (`/api/v1/`) protegida con **Laravel Sanctum**.

| Recurso | Endpoint | Operaciones |
|---|---|---|
| Noticias | `/api/v1/news` | CRUD completo |
| Álbumes | `/api/v1/albums` | CRUD completo |
| Canciones | `/api/v1/songs` | CRUD completo |
| Comidas | `/api/v1/foods` | CRUD completo |
| Festivales | `/api/v1/festivals` | CRUD completo |
| Artistas | `/api/v1/artists` | CRUD completo |
| Mitos | `/api/v1/myths` | CRUD completo |

> **Estado actual:** La API está disponible pero no tiene consumidores activos. Preparada para futura integración con aplicaciones móviles u otros servicios.

---

### 3.4 Autenticación

| Método | Descripción |
|---|---|
| **Email/Password** | Registro y login tradicional (Laravel UI) |
| **Google OAuth** | Login con cuenta de Google (Socialite) |
| **Facebook OAuth** | Login con cuenta de Facebook (Socialite) |
| **API Token** | Autenticación por token para la API (Sanctum) |

---

## 4. Roles y Permisos

El sistema utiliza **Spatie Laravel Permission** para gestión de roles y permisos.

| Rol | Descripción | Acceso |
|---|---|---|
| **Administrador** | Control total del sistema | Backend completo, moderación, gestión de usuarios |
| **Usuario registrado** | Usuario autenticado | Publicar clasificados, enviar colaboraciones, gestionar sus avisos |
| **Visitante** | Usuario no autenticado | Navegar todo el contenido público, suscribirse al newsletter, contacto |

---

## 5. Flujos Principales

### 5.1 Flujo de Publicación de Contenido
```
Administrador → Backend (CRUD) → Contenido con estado=1 → Visible en Frontend
```

### 5.2 Flujo de Colaboración UGC
```
Usuario registrado → /colaborar → Selecciona tipo y completa formulario
→ Contribución creada (status: pending)
→ Administrador revisa en backend → Aprueba / Rechaza con comentario
```

### 5.3 Flujo de Clasificados
```
Usuario registrado → /avisos-clasificados/publicar → Completa formulario
→ Clasificado creado (estado: pendiente)
→ Administrador revisa → Aprueba / Rechaza
→ Si aprobado: visible en listado público
→ Si expira: usuario puede renovar
```

### 5.4 Flujo de Newsletter
```
Visitante → Formulario sidebar → Suscripción (genera token único)
→ Job semanal recopila contenido → Envía email a suscriptores activos
→ Enlace de desuscripción con token en cada email
```

---

## 6. Estrategia SEO

| Elemento | Implementación |
|---|---|
| **URLs amigables** | Slugs descriptivos y largos ya posicionados (ej: `/noticias-del-folklore-argentino`) |
| **Meta tags** | Title y description personalizados por sección |
| **Datos estructurados** | JSON-LD con schemas de WebSite, Organization y SearchAction |
| **Sitemaps** | Sitemap general + sitemap de noticias para Google News |
| **Breadcrumbs** | Componente de migas de pan para navegación |
| **Imágenes optimizadas** | Componente `optimized-image` para carga eficiente |

---

## 7. Integraciones Externas

| Servicio | Uso |
|---|---|
| **Google Analytics** | Seguimiento de tráfico y comportamiento de usuarios |
| **Google AdSense** | Monetización mediante publicidad |
| **Google OAuth** | Login social |
| **Facebook OAuth** | Login social |
| **YouTube** | Enlaces a videos de canciones y artistas |
| **Spotify** | Enlaces a canciones y álbumes |

---

## 8. Módulos Planificados (Próxima versión)

| Módulo | Descripción |
|---|---|
| **Entrevistas** | Sección dedicada a entrevistas con artistas. Controller existe sin métodos ni vistas. Requiere spec completa. |
| **Radios** | Directorio de radios de folklore. Índice frontend funciona, detalle sin implementar. Sin backend. |
| **Peñas** | Directorio de peñas folklóricas. Mismo estado que Radios. |
| **Escuelas de Danzas** | Directorio de escuelas y academias de danzas folklóricas. No iniciado. |
| **Videos** | Sección de videos. No iniciado. |

---

## 9. Componentes de UI Reutilizables

El frontend utiliza componentes Blade reutilizables:

| Componente | Uso |
|---|---|
| `noticia-card` | Tarjeta de noticia en listados |
| `noticia-card-artista` | Tarjeta de noticia en contexto de artista |
| `biografia-card` | Tarjeta de artista/biografía |
| `disco-card` | Tarjeta de álbum |
| `letra-card` | Tarjeta de canción/letra |
| `show-card` | Tarjeta de evento/show |
| `festival-card` | Tarjeta de festival |
| `mito-card` | Tarjeta de mito/leyenda |
| `receta-card` | Tarjeta de receta |
| `compartir-redes` | Botones de compartir en redes sociales |
| `breadcrumbs` | Navegación tipo migas de pan |
| `optimized-image` | Imagen optimizada con lazy loading |
| `navbar` | Barra de navegación principal |
| `sidebar.*` | Componentes de sidebar (newsletter, redes, donación) |

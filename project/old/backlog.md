# Backlog Estratégico y de Automatización (LMFA)

Este documento detalla las tareas necesarias para implementar el plan estratégico de crecimiento y monetización, haciendo foco en la ingesta automatizada de contenidos y la vinculación con la API existente, así como en las vías de facturación.

## Epica 1: API de Integración con MAKE (Ingesta de Contenidos Automática)
*Objetivo: Recibir el contenido estructurado generado por la automatización de MAKE (Newsletters → Perplexity → Excel) e insertarlo en las tablas de la aplicación.*

- [ ] **Tarea 1.1: Autenticación de API para MAKE**
  - Configurar e implementar Laravel Sanctum (o un Api-Key estático confiable) para proteger los endpoints que consumirá MAKE.
- [ ] **Tarea 1.2: Endpoint de Ingesta de Noticias**
  - Desarrollar ruta `POST /api/v1/noticias`.
  - Recibir JSON con título, slug, texto enriquecido, metadatos y etiquetas.
  - Implementar lógica para publicar automáticamente o dejar en estado 'borrador' (pending).
- [ ] **Tarea 1.3: Endpoint de Ingesta de Biografías / Intérpretes**
  - Desarrollar ruta `POST /api/v1/interpretes`.
  - Recibir biografía generada, años en actividad, redes sociales asociadas.
- [ ] **Tarea 1.4: Endpoint de Ingesta de Shows y Festivales**
  - Desarrollar ruta `POST /api/v1/shows` y `POST /api/v1/festivales`.
  - Mapear fechas, recintos, URLs de compra de entradas y vincular a los intérpretes existentes en la base de datos si corresponden.
- [ ] **Tarea 1.5: Endpoint de Ingesta de Letras (Canciones)**
  - Desarrollar ruta `POST /api/v1/canciones`.
  - Recibir nombre, letra y el ID/nombre del intérprete para adjuntarlo a su perfil automáticamente.

## Epica 2: Gestión y Automatización de Imágenes
*Objetivo: Resolver el problema de imágenes faltantes en el texto generado por la IA.*

- [ ] **Tarea 2.1: Búsqueda dinámica de Imágenes (vía webhook o API de búsqueda)**
  - Utilizar una API como Google Custom Search, Unsplash, o la propia IA generativa (DALL-E 3 / Stable Diffusion) en el flujo de MAKE, pasando la URL de la imagen en el payload hacia la API de Laravel.
- [ ] **Tarea 2.2: Procesamiento automático de imágenes en Laravel**
  - Al recibir un payload con la clave `image_url` en la API, hacer que Laravel descargue temporalmente la imagen, la convierta a `WebP`, ajuste su calidad mediante `ImageUploadService`, y la asocie polimórficamente al modelo (Noticia, Intérprete, etc.).
- [ ] **Tarea 2.3: Imágenes por defecto / fallback inteligente**
  - Si no hay imagen, inyectar dinámicamente un avatar abstracto basado en el nombre del elemento o usar iconos SVG temáticos para que el frontal no se rompa o luzca pobre.

## Epica 3: Fuentes de Extracción e Ingesta Continua (Vía MAKE)
*Objetivo: Estructurar la recolección automática de datos sobre folklore.*

- [ ] **Tarea 3.1: Scrapeo de Carteleras / Tickets**
  - Integrar en MAKE o en un cron de Laravel la lectura RSS o scrapeo básico de ticketeras argentinas (Ticketek, Passline, EntradaUno) buscando la tag "Folklore". Enviar directo al endpoint de Shows.
- [ ] **Tarea 3.2: Scrapeo de Letras**
  - Automatizar la petición de letras faltantes: crear un script que, al faltar letras de las canciones top de un intérprete, envíe la solicitud a la IA en MAKE para que busque la letra certera, extraiga la estructura y se envíe de regreso a la API.

## Epica 4: Monetización y Generación de Ingresos
*Objetivo: Convertir el tráfico del sitio en rentabilidad.*

- [ ] **Tarea 4.1: Clasificados Destacados (Suscripción o Pago Único)**
  - Integrar MercadoPago API.
  - Al usuario intentar destacar su aviso, redirigirlo al checkout. Al retornar, un webhook (`POST /api/webhooks/mercadopago`) de MercadoPago marcará `is_featured = true` en el aviso y aumentará su `expiration_date`.
- [ ] **Tarea 4.2: Espacios Publicitarios (Banners y Ads)**
  - Crear un módulo "Banners" en el panel Admin.
  - Vender banners estáticos para marcas de folklore (Luthiers, Ropa, Peñas) e inyectarlos entre las listas de noticias o de intérpretes.
- [ ] **Tarea 4.3: Afiliados y Venta de Entradas**
  - Generar UTMs para el botón "Comprar Entradas" en shows y festivales. Si existe un programa de afiliados con ticketeras, contabilizar clics.
- [ ] **Tarea 4.4: Directorio de Profesionales (Suscripciones Premium)**
  - Limitar el despliegue de contacto en ciertos Clasificados (Ej: Músicos de sesión, Profesores) y cobrar una cuota mínima mensual para listar sus servicios con contacto directo por WhatsApp.

## Epica 5: Crecimiento Orgánico (SEO)
*Objetivo: Maximizar la visibilidad en Google con los contenidos inyectados.*

- [ ] **Tarea 5.1: Indexación Automática Avanzada**
  - Desarrollar la inyección inteligente del Schema.org en el Layout (NewsArticle, Event, MusicGroup) alimentando sus json-ld desde cada modelo inyectado.
- [ ] **Tarea 5.2: Interlinking Automático**
  - Al publicarse una nueva noticia vía API, un Job en Laravel buscará menciones a intérpretes conocidos en el cuerpo del texto y los convertirá dinámicamente en links `<a href="...">` apuntando a su biografía en el sitio.
- [ ] **Tarea 5.3: Sitemap XML Dinámico**
  - Asegurarse de que el script del sitemap recorra automáticamente todos los slugs generados diariamente para ser crawleado por Google News y Google Bot.

---

### Siguientes pasos recomendados para empezar MAÑANA:
1. **Configurar el endpoint de la API (`routes/api.php`) general usando Sanctum y un API token de prueba.**
2. **Crear el controlador `Api\IngestionController.php` con las lógicas necesarias para procesar y optimizar imágenes remotas al estilo de `ImageUploadService`.**
3. **Mapear en MAKE un POST a la nueva URL** con el modelo JSON extraído del excel generado por Perplexity.

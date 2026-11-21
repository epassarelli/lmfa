# Autopublicación en redes: Discos

Estado: alcance aprobado por el usuario en el chat el 2026-11-20 («hagamos la primera opción con el cover grande y realizá el desarrollo para discos»). Implementación local sobre `dev`, sin commit, migraciones, deploy ni publicaciones reales. Sigue el estándar `social-autopublicacion-estandar.md` y reutiliza el ejecutor de `social-publication-laravel-executor.md`.

## 1. Contexto y problema

El catálogo de discos es contenido atemporal y visual que hoy no se difunde en redes. El ejecutor Laravel ya publica Noticias y Eventos con cola, ledger, placas y panel. Se suma Discos con la misma operatoria diaria (descartado el calendario mensual en el Planificador de Meta, ver `claude/redes-discos-decisiones.md` del proyecto) y una placa que incluye la portada del disco.

## 2. Actores

Responsable editorial/administrador (panel), programador de Laravel (`schedule:run`), API Graph de Meta.

## 3. Alcance y fuera de alcance

Alcance:
- Tipo `albums` («Discos») en la autopublicación: cola, ledger, ejecutor, envío manual, log y configuración de horarios.
- Placa `mfa-discos-v4` con la portada grande (variante A aprobada por el usuario sobre el boceto del 2026-11-20).
- Registro central de tipos (`SocialPublicationKinds`) para que sumar Letras, Biografías o Festivales no repita condicionales `news ? … : …`.

Fuera de alcance: API n8n (sigue solo con Noticias y Eventos), habilitación manual legacy por contenido, Grupos de Facebook, historias/reels, letras de canciones (próxima etapa, con su propia decisión de derechos), cambios en las páginas públicas de discos.

## 4. Historias

- Como responsable quiero que cada día salga un disco del catálogo en Facebook e Instagram, a un horario propio, sin depender de mi PC.
- Como responsable quiero que la placa muestre la portada del disco para que la publicación sea más visual.
- Como responsable quiero publicar a demanda un disco puntual de la cola, viendo antes la placa y el texto.

## 5. Autopublicación en redes (estándar)

### 5.1 Programación
- Facebook e Instagram con encendido independiente, diario o 1 a N días de la semana, hora HH:MM (America/Argentina/Buenos_Aires).
- Valores iniciales: **apagado** en ambas redes, 20:00, todos los días. Se activa desde el panel.
- Una publicación por red y corrida; tolerancia de 3 h; Facebook antes que Instagram si coinciden (comportamiento del ejecutor).

### 5.2 Cola de candidatos
- Elegible: disco activo (`albunes.estado = 1`), intérprete activo con slug, disco con slug y nombre, y con portada cargada (registro en `media_assets` o `foto` legacy).
- Umbral: `albunes.id > SOCIAL_PUBLICATION_ALBUMS_AFTER_ID` (por defecto 0: todo el catálogo).
- Orden: por ID ascendente, igual que Noticias.
- Sin envío previo exitoso, incierto o en curso a ese destino; mismos estados reintentables que Noticias y Eventos.
- Instagram solo publica discos que ya salieron con éxito en Facebook.
- Excluidos: discos inactivos, de intérpretes inactivos, sin slug o sin portada cargada.

### 5.3 Pieza y texto
- Plantilla `mfa-discos-v4` (catálogo 4.0.0, verde lima `#ABD06E`), PNG para Facebook y JPEG público para Instagram, 1080×1350.
- Composición (variante A): encabezado v4 (marca, línea, solapa DISCOS, «NUESTRO ARCHIVO MUSICAL»); portada cuadrada grande a la izquierda (hasta 600 px, recorte centrado si no es cuadrada) con barra de acento debajo; columna derecha con «AÑO» y el año grande en acento (si `anio` tiene 4 dígitos) y el tipo de disco si `album_type` es conocido (álbum, simple, EP, compilado); debajo de la portada el nombre del disco en blanco y el intérprete en mayúsculas y acento; cierre v4 (CTA y dominio).
- Títulos largos: el tamaño baja de 66 a 40 px y admite hasta 2 líneas; si hace falta, la portada se achica para dejar lugar. Si aun así no entra, el render falla sin truncar (igual que Noticias).
- Sin archivo de portada legible en disco: la placa sale sin portada, con el título grande como en la v4 (no se bloquea la cola).
- Copy: `«Disco» — Intérprete (año)` + bajada del disco si existe + URL pública (`/{intérprete}/discografia/{slug}`). Instagram: máximo 2200 caracteres.
- El hash del contenido incluye la ruta y la huella SHA-256 de la portada: si la portada cambia entre la reserva y el envío, el ledger lo bloquea.

### 5.4 Panel
Sin cambios de funcionamiento: Discos aparece en log, envío manual (con el año en la lista de la cola) y configuración de horarios porque el panel itera los tipos registrados.

### 5.5 Seguridad y operación
Mismo ledger, pausa, ejecutor activo, avisos y tokens que Noticias y Eventos. No hay migraciones: el ledger guarda el tipo como texto.

## 6. Reglas de negocio

1. Un disco sale una sola vez por red (salvo fallo seguro reintentable).
2. Desactivar el disco o su intérprete lo saca de la cola; si ya estaba reservado, el ledger bloquea el envío porque el snapshot deja de validar.
3. La portada publicada es la misma que muestra el portal. Derechos: ver preguntas abiertas.

## 7. Criterios de aceptación

- Dado Discos activo en Facebook a las 20:00, cuando son las 20:00, entonces se publica el disco elegible de menor ID con la placa de portada y queda `success`.
- Dado éxito en Facebook y horario de Instagram vencido, entonces se publica el mismo disco en Instagram.
- Dado un disco inactivo, de intérprete inactivo o sin portada cargada, entonces no entra en la cola.
- Dado un disco ya publicado en Facebook, entonces no vuelve a la cola de Facebook.
- Dado el envío manual de un disco que no es el siguiente, entonces se publica ese y los anteriores siguen en la cola.
- Dado Discos apagado (valor inicial), entonces el programador no publica discos.

## 8. Casos borde

Título o intérprete muy largos; año vacío o no numérico (se omite); `album_type` desconocido (se omite); portada no cuadrada (recorte centrado); portada referenciada pero ausente en disco (placa sin portada); portada en WebP (requiere GD con WebP, presente en producción según preflight de GD; si no, placa sin portada); dos discos con el mismo slug en distintos intérpretes (la URL incluye el intérprete).

## 9. Impacto

- Técnico: registro de tipos nuevo; cambios en ledger, ejecutor, guard, settings, renderer, controlador del panel y vista manual; accessor `title` en `Album` (alias de `album`, sin columna nueva).
- Performance: una consulta de cola con `EXISTS` por corrida; el snapshot lee la portada del disco local una vez por intento (archivos de cientos de KB).
- SEO/editorial: sin cambios en rutas públicas. Las publicaciones enlazan a la ficha del disco.

## 10. Preguntas abiertas y dependencias

- **Derechos de portadas (decisión del usuario pendiente):** las portadas importadas de Spotify o cargadas a mano no tienen estado de derechos confirmado en `albunes`. Hoy se usan las mismas que ya muestra el portal. Si el usuario lo decide, se puede filtrar la cola por `media_assets.rights_status`.
- Horario definitivo: se configura desde el panel.
- Depende del deploy del ejecutor Laravel y de las fuentes del renderer en producción.

## 11. Validación prevista

Tests Feature con `Http::fake()` y renderer simulado (`tests/Feature/SocialPublicationAlbumsTest.php`): cola y exclusiones, éxito FB→IG, horario apagado por defecto, envío manual de un ítem no siguiente, snapshot con portada y hash. Test de render real con fuentes editoriales (se omite si no están). Regresión: `SocialPublicationExecutorTest` y `SocialPublicationLedgerTest`. Revisión visual de la placa en el panel local.

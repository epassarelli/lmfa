# 🔧 Documento Operativo — Mi Folklore Argentino

> **Proyecto:** Mi Folklore Argentino (MFA)  
> **URL Producción:** [https://mifolkloreargentino.com.ar](https://mifolkloreargentino.com.ar)  
> **Hosting:** Hostinger  
> **Última actualización:** 2026-04-26

---

## 1. Información del Entorno

### 1.1 Entorno de Producción

| Componente | Detalle |
|---|---|
| **Hosting** | Hostinger |
| **URL** | https://mifolkloreargentino.com.ar |
| **PHP** | 8.2 |
| **Base de datos** | MariaDB 10.8 |
| **Servidor web** | Apache (con mod_rewrite habilitado) |
| **SSL** | Sí (HTTPS) |
| **Node.js** | 20.x (utilizado en build de assets) |

### 1.2 Entorno de Desarrollo Local (Docker)

| Servicio | Imagen | Puerto |
|---|---|---|
| **App** (Laravel + Apache) | `php:8.2-apache` (build custom) | `80` |
| **Base de datos** | `mariadb:10.8` | `3306` |
| **phpMyAdmin** | `phpmyadmin:5.1.1` | `8080` |

**Credenciales locales de base de datos:**
| Campo | Valor |
|---|---|
| Database | `mfa` |
| User | `mfa` |
| Password | `mfa` |
| Root Password | `mfa` |

---

## 2. Arranque del Entorno de Desarrollo

### 2.1 Requisitos Previos
- Docker Desktop instalado y corriendo.
- Git para clonar el repositorio.
- (Opcional) Node.js 20.x local para compilar assets sin Docker.

### 2.2 Primera Vez — Setup Completo

```bash
# 1. Clonar el repositorio
git clone <url-del-repo> lmfa
cd lmfa

# 2. Copiar archivo de configuración
cp .env.example .env

# 3. Configurar .env para entorno local Docker
#    DB_HOST=db
#    DB_DATABASE=mfa
#    DB_USERNAME=mfa
#    DB_PASSWORD=mfa

# 4. Levantar los contenedores
docker-compose up -d --build

# 5. Instalar dependencias PHP (dentro del contenedor)
docker exec -it <nombre_contenedor_app> composer install

# 6. Generar key de la aplicación
docker exec -it <nombre_contenedor_app> php artisan key:generate

# 7. Crear enlace simbólico de storage
docker exec -it <nombre_contenedor_app> php artisan storage:link

# 8. Ejecutar migraciones (si hay base limpia)
docker exec -it <nombre_contenedor_app> php artisan migrate

# 9. Instalar dependencias de frontend
npm install

# 10. Compilar assets
npm run dev
```

### 2.3 Uso Diario

```bash
# Levantar los servicios
docker-compose up -d

# Ver logs
docker-compose logs -f app

# Detener servicios
docker-compose down

# Acceder al contenedor
docker exec -it <nombre_contenedor_app> bash
```

### 2.4 URLs Locales

| Servicio | URL |
|---|---|
| Aplicación | http://localhost |
| phpMyAdmin | http://localhost:8080 |

---

## 3. Estructura de Archivos Clave

```
lmfa/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/           # Controladores de la API REST
│   │   ├── Backend/       # Controladores del panel de administración
│   │   ├── Frontend/      # Controladores del portal público
│   │   └── Pasarela/      # Controladores de la Pasarela de Contenidos
│   ├── Models/            # Modelos Eloquent
│   ├── Mail/              # Clases de correo (Mailable)
│   ├── Jobs/              # Jobs de cola (SendNewsletterJob)
│   ├── Services/          # Servicios (ImageUpload, Link)
│   ├── Traits/            # Traits compartidos (CommonMethodsTrait)
│   └── Livewire/          # Componentes Livewire
├── config/                # Configuración de Laravel
├── database/
│   ├── migrations/        # Migraciones de base de datos
│   └── seeders/           # Seeders
├── docs/                  # Documentación del proyecto
├── public/                # Archivos públicos (entry point)
├── resources/
│   └── views/
│       ├── frontend/      # Vistas del portal público
│       ├── backend/       # Vistas del panel admin (AdminLTE)
│       ├── components/    # Componentes Blade reutilizables
│       ├── layouts/       # Layouts principales
│       ├── emails/        # Plantillas de email
│       └── mail/          # Plantillas de correo (Markdown)
├── routes/
│   ├── web.php            # Rutas del frontend público
│   ├── admin.php          # Rutas del panel de administración y Pasarela
│   ├── api.php            # Rutas de la API REST
│   └── console.php        # Comandos Artisan personalizados
├── storage/               # Archivos subidos, logs, cache
├── docker-compose.yml     # Orquestación Docker
├── Dockerfile             # Build de imagen Docker
└── .env                   # Variables de entorno (NO commitear)
```

---

## 4. Despliegue a Producción

### 4.1 Proceso Actual

> ⚠️ **Nota:** El despliegue se realiza de forma manual. Se recomienda implementar CI/CD en el futuro.

```bash
# 1. Conectarse al servidor de Hostinger (SSH o panel de control)

# 2. Subir archivos actualizados (FTP/SFTP o Git pull)
git pull origin main

# 3. Instalar dependencias de producción
composer install --no-dev --optimize-autoloader

# 4. Ejecutar migraciones pendientes
php artisan migrate --force

# 5. Limpiar y cachear configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Compilar assets de producción
npm run build
```

### 4.2 Checklist Pre-Deploy

- [ ] Verificar que `.env` de producción tiene `APP_ENV=production` y `APP_DEBUG=false`.
- [ ] Verificar credenciales de base de datos de producción.
- [ ] Confirmar que MAIL_* está correctamente configurado si se va a enviar correo.
- [ ] Confirmar que las claves de Google/Facebook OAuth son las de producción.
- [ ] Ejecutar `php artisan config:cache` después de cualquier cambio en `.env`.

### 4.3 Variables de Entorno Críticas

```env
# Producción
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mifolkloreargentino.com.ar

# Base de datos
DB_CONNECTION=mysql
DB_HOST=<host_hostinger>
DB_PORT=3306
DB_DATABASE=<nombre_bd_produccion>
DB_USERNAME=<usuario_produccion>
DB_PASSWORD=<password_produccion>

# Correo (configurar según proveedor)
MAIL_MAILER=smtp
MAIL_HOST=<smtp_host>
MAIL_PORT=<puerto>
MAIL_USERNAME=<usuario>
MAIL_PASSWORD=<password>
MAIL_FROM_ADDRESS=<email_remitente>
MAIL_FROM_NAME="Mi Folklore Argentino"

# Social Auth
GOOGLE_CLIENT_ID=<id>
GOOGLE_CLIENT_SECRET=<secret>
GOOGLE_REDIRECT_URL=https://mifolkloreargentino.com.ar/auth/google/callback

FACEBOOK_CLIENT_ID=<id>
FACEBOOK_CLIENT_SECRET=<secret>
FACEBOOK_REDIRECT_URL=https://mifolkloreargentino.com.ar/auth/facebook/callback
```

---

## 5. Gestión de Contenido — Guía Operativa

### 5.1 Acceso al Panel de Administración

1. Navegar a `https://mifolkloreargentino.com.ar/admin`
2. Iniciar sesión con credenciales de administrador.
3. El dashboard muestra contadores de todas las entidades.

### 5.2 Flujo de Trabajo para Contenido Nuevo

#### Crear una Noticia
1. Ir a **Backend > News > Crear**.
2. Completar: título, categoría, contenido, imagen principal.
3. Asociar intérprete(s) si corresponde.
4. Establecer `editorial_status = published` para que sea visible en el frontend.
5. Guardar.

#### Crear un Artista/Intérprete
1. Ir a **Backend > Intérpretes > Crear**.
2. Completar: nombre, biografía, foto, redes sociales.
3. El slug se genera automáticamente a partir del nombre.
4. Establecer `estado = 1` para activar su miniportal.
5. Guardar.

#### Gestionar Clasificados
1. Ir a **Backend > Clasificados**.
2. Revisar avisos con estado "pendiente".
3. Para cada aviso: **Aprobar** (lo activa en el frontend) o **Rechazar** (con comentario opcional del moderador).

#### Moderar Contribuciones UGC
1. Ir a **Backend > Contributions**.
2. Revisar contribuciones pendientes (se muestra el conteo en el dashboard).
3. Ver el detalle del payload propuesto.
4. **Aprobar** o **Rechazar** con comentario.

### 5.3 Estados de Contenido

#### News y Events (campo `editorial_status`)
| Valor | Significado |
|---|---|
| `draft` | Borrador, no visible en frontend |
| `pending_review` | Enviado a moderación |
| `approved` | Aprobado, pendiente de publicación |
| `published` | Visible en el frontend |
| `rejected` | Rechazado por moderador |
| `archived` | Archivado |

#### Clasificados (campo `estado`)
| Valor | Significado |
|---|---|
| `pendiente` | Requiere moderación |
| `activo` | Visible en el frontend |
| `rechazado` | Rechazado por moderador |

#### Contribuciones UGC (campo `status`)
| Valor | Significado |
|---|---|
| `pending` | Pendiente de revisión |
| `approved` | Aprobada |
| `rejected` | Rechazada |

---

## 6. Newsletter

### 6.1 Estado Actual
⚠️ **En fase de prueba** — No está activo en producción aún.

### 6.2 Componentes del Sistema

| Componente | Archivo | Función |
|---|---|---|
| **Suscripción** | `NewsletterController@subscribe` | Registra nuevo suscriptor con token único |
| **Desuscripción** | `NewsletterController@unsubscribe` | Desactiva suscriptor vía token |
| **Job de envío** | `SendNewsletterJob` | Recopila contenido y envía emails |
| **Email template** | `WeeklyNewsletterMail` | Template del email semanal |
| **Admin** | `NewsletterSubscriberController` | Gestión de suscriptores en backend |

### 6.3 Activación en Producción

Para activar el newsletter semanal:

1. **Configurar el driver de colas** en `.env`:
   ```env
   QUEUE_CONNECTION=database
   ```
2. **Crear la tabla de jobs** (si no existe):
   ```bash
   php artisan queue:table
   php artisan migrate
   ```
3. **Configurar un cron job** en Hostinger para el scheduler de Laravel:
   ```cron
   * * * * * cd /path/to/lmfa && php artisan schedule:run >> /dev/null 2>&1
   ```
4. **Registrar el comando programado** en `app/Console/Kernel.php` si no está ya configurado:
   ```php
   $schedule->job(new SendNewsletterJob)->weekly();
   ```
5. **Ejecutar el worker de colas**:
   ```bash
   php artisan queue:work --daemon
   ```

> ⚠️ **Pendiente confirmar:** Si el scheduler y el job ya están correctamente registrados en el Kernel.

---

## 7. Mantenimiento

### 7.1 Tareas Rutinarias

| Tarea | Frecuencia | Comando / Acción |
|---|---|---|
| **Limpiar cache** | Después de cada deploy | `php artisan cache:clear` |
| **Limpiar vistas compiladas** | Después de cada deploy | `php artisan view:clear` |
| **Verificar logs** | Semanal | Revisar `storage/logs/laravel.log` |
| **Limpiar logs antiguos** | Mensual | Eliminar archivos de log antiguos |
| **Revisar contribuciones pendientes** | Diario | Dashboard del backend |
| **Revisar clasificados pendientes** | Diario | Backend > Clasificados |
| **Verificar espacio en disco** | Mensual | Panel de Hostinger |

### 7.2 Comandos Útiles de Artisan

```bash
# Limpiar toda la cache
php artisan optimize:clear

# Regenerar cache de configuración, rutas y vistas
php artisan optimize

# Ver rutas registradas
php artisan route:list

# Ejecutar migraciones pendientes
php artisan migrate

# Rollback de última migración
php artisan migrate:rollback

# Regenerar autoload
composer dump-autoload

# Verificar estado de las colas
php artisan queue:status
```

### 7.3 Logs y Debugging

| Ubicación | Contenido |
|---|---|
| `storage/logs/laravel.log` | Log principal de la aplicación |
| `storage/logs/` | Logs rotativos por fecha |
| Debug Bar | Disponible en desarrollo (`barryvdh/laravel-debugbar`) |

> **En producción:** Asegurarse de que `APP_DEBUG=false` y que el `LOG_LEVEL` esté en `error` o `warning` para evitar logs excesivos.

---

## 8. Backups

### 8.1 Estado Actual
⚠️ **No hay sistema de backup propio configurado.** Se depende del backup automático que pueda ofrecer Hostinger.

### 8.2 Recomendaciones

#### Base de datos
```bash
# Exportar backup manual
mysqldump -u <user> -p <database> > backup_$(date +%Y%m%d).sql
```

Se recomienda:
- Configurar backup automático diario de la base de datos.
- Almacenar backups en un servicio externo (Google Drive, S3, etc.).
- Retener al menos 7 días de backups.

#### Archivos subidos
- Los archivos subidos están en `storage/app/public/`.
- Incluir esta carpeta en el backup.

#### Código fuente
- El repositorio Git actúa como backup del código fuente.
- Asegurarse de tener el repo en un servicio remoto (GitHub, GitLab, etc.).

---

## 9. Monitoreo

### 9.1 Estado Actual
⚠️ **No hay sistema de monitoreo configurado.**

### 9.2 Recomendaciones

| Herramienta | Propósito | Costo |
|---|---|---|
| **UptimeRobot** | Monitoreo de disponibilidad (uptime) | Gratis (plan básico) |
| **Google Search Console** | Monitoreo de indexación y errores SEO | Gratis |
| **Google Analytics** | Ya conectado — para tráfico y comportamiento | Gratis |
| **Laravel Telescope** | Debug y monitoreo en desarrollo | Gratis (paquete Laravel) |
| **Sentry / Bugsnag** | Monitoreo de errores en producción | Gratis (plan básico) |

---

## 10. Seguridad

### 10.1 Medidas Actuales

| Medida | Estado |
|---|---|
| **HTTPS** | ✅ Activo (SSL) |
| **CSRF Protection** | ✅ Activo (middleware de Laravel) |
| **Autenticación** | ✅ Laravel Auth + OAuth social |
| **Autorización** | ✅ Spatie Permission (roles y permisos) |
| **Sanctum (API)** | ✅ Tokens de API |
| **Inputs validación** | ✅ Form Requests |
| **`.env` en `.gitignore`** | ✅ No se commitea |

### 10.2 Recomendaciones Pendientes

- [ ] Configurar **rate limiting** en rutas de login y API.
- [ ] Implementar **2FA** (autenticación en dos pasos) para administradores.
- [ ] Revisar y actualizar dependencias regularmente (`composer audit`).
- [ ] Configurar **CSP headers** (Content Security Policy).
- [ ] Configurar **CORS** adecuadamente para la API.
- [ ] Cambiar las credenciales por defecto de la base de datos local.

---

## 11. Contacto y Soporte

| Rol | Responsable | Contacto |
|---|---|---|
| **Desarrollo** | Propietario del proyecto | — |
| **Administración de contenido** | Propietario del proyecto | — |
| **Hosting** | Hostinger | Soporte desde panel de Hostinger |

> **Nota:** Actualmente el proyecto es gestionado por una sola persona. Se está planificando la implementación de agentes de IA para automatizar la búsqueda y carga de contenido.

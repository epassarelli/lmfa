# Checklist de despliegue — mail de reactivación de usuarios (2026-11-12)

Estado: **implementado y validado en local; no desplegado**. Rama `feature/reactivacion-usuarios`. Spec: `project/docs/specs/reactivacion-usuarios.md`. Nada de lo siguiente se ejecutó contra producción; lo hace el responsable del despliegue.

## 0. Qué incluye
- Pantalla de administrador «Reactivación de usuarios» (`/admin/reactivacion-usuarios`): destinatarios, vista previa de las dos variantes, prueba al propio correo, envío manual con confirmación e historial.
- Aviso cuando pasaron 30 días desde la última tanda y hay destinatarios (menú lateral, tablero y la propia pantalla).
- Mail con ventajas de la comunidad, botón de ingreso, botón de recuperar contraseña y baja firmada (`/reactivacion/baja/{user}`).
- Sin dependencias nuevas, sin cambios de `composer.lock`, sin variables de entorno obligatorias (opcionales: `REENGAGEMENT_INACTIVITY_DAYS`, `REENGAGEMENT_COOLDOWN_DAYS`, `REENGAGEMENT_MAX_PER_MINUTE`; por defecto 30, 30 y 30).

## 1. Migración (una, aditiva)
`2026_11_12_100000_add_reengagement_mail_support`: columna `users.reengagement_opt_out_at` y tabla `reengagement_mail_sends`. Aplicada en local (`mfa` y `mfa_test`) el 2026-11-12.
- [ ] Backup de la base de producción antes de migrar.
- [ ] Mostrar `php artisan migrate:status` y confirmar que solo esta migración está pendiente.
- [ ] `php artisan migrate`. Reversa: `php artisan migrate:rollback --step=1` (borra la tabla y la columna; solo pierde el historial de envíos y las bajas).
- Sin migrar, la pantalla da error; el tablero y el menú quedan protegidos (no muestran el aviso).

## 2. Antes de enviar a usuarios reales
- [ ] Cola en marcha: el programador ejecuta `queue:work --stop-when-empty` cada 5 minutos, así que el cron `* * * * * php artisan schedule:run` debe estar activo (ver `newsletter-produccion.md`). Si no, los mails quedan en cola.
- [ ] SMTP real configurado y con remitente/DKIM/SPF correctos (si no, los mails caen en spam).
- [ ] Revisar el **texto del mail** (es un borrador redactado por el agente) con la vista previa de las dos variantes.
- [ ] Usar **«Enviarme una prueba»** y revisar el resultado en Gmail y en un cliente de celular (botones, enlaces, baja).
- [ ] Probar el enlace «Recuperar mi contraseña» con una cuenta de prueba, de punta a punta.
- [ ] Ajustar `REENGAGEMENT_MAX_PER_MINUTE` al límite del proveedor de correo del hosting.

## 3. Envío
- El administrador entra a la pantalla, marca la confirmación y envía. La primera tanda alcanza a ~440 usuarios (la mayoría «nunca ingresó»); salen de a poco por el tope por minuto.
- Seguimiento: historial de tandas en la misma pantalla (enviados, en cola, fallidos, omitidos) y `failed_jobs`.
- Los usuarios dados de baja quedan excluidos para siempre de este mail; el newsletter no se ve afectado.

## 4. Observaciones
- «Inactivo» se calcula con `last_login_at`, que se empezó a registrar hace poco: por eso 442 de 449 usuarios figuran como «nunca ingresó». La segunda tanda ya tendrá datos reales.
- El login con Google/Facebook sigue sin funcionar: el mail lo aclara y ofrece recuperar contraseña. Es la próxima tarea.
- Marketing a usuarios registrados: el mail incluye baja de un clic (también por encabezado `List-Unsubscribe`).

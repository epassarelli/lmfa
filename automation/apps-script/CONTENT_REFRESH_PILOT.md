# Piloto Content Refresh — Artistas, Recetas y Mitos

## Alcance

Validar seis casos controlados, sin activar el trigger diario:

1. Artista CREAR.
2. Artista ACTUALIZAR.
3. Receta CREAR.
4. Receta ACTUALIZAR.
5. Mito CREAR.
6. Mito ACTUALIZAR.

## 1. Preflight local sin credenciales

1. En `Contenidos`, dejar `ENVIAR_API=S` sólo en las seis filas del piloto.
2. Descargar la pestaña `Contenidos` completa como CSV; no recortar ni copiar solamente las seis filas.
3. Desde la raíz del repositorio ejecutar:

```bash
npm run apps-script:pilot:check -- /ruta/Contenidos.csv
```

El comando no accede a Google ni a Laravel. Debe terminar con `PREFLIGHT OK` y comprueba:

- una fila CREAR y una ACTUALIZAR por entidad;
- ninguna otra fila o tipo con `ENVIAR_API=S` en la exportación completa;
- `ACCION_API` explícita;
- ausencia/presencia correcta de `ID_WEB`;
- cuerpo mínimo cuando corresponde;
- payload y ruta simulados;
- rechazo de actualizaciones sin cambios.

## 2. Preparación del piloto real

- Confirmar que las tres modernizaciones y sus migraciones estén desplegadas.
- Confirmar CI verde y Apps Script sincronizado con la rama aprobada.
- Deshabilitar temporalmente `MFA - Carga contenidos diario`.
- Mantener `ENVIAR_API=N` en toda fila ajena al caso que se probará.
- Verificar que las filas ACTUALIZAR apunten a contenidos de prueba o respaldados.
- Ejecutar los auditores antes del primer envío y conservar los CSV.

## 3. Ejecución secuencial

Probar una fila por vez. Para cada caso:

1. Habilitar exclusivamente esa fila con `ENVIAR_API=S`.
2. Ejecutar manualmente `cargarContenidosMFA`.
3. Confirmar en la fila:
   - `ESTADO=PUBLICADO` (significa envío API completado, no publicación web);
   - `ENVIAR_API=N`;
   - `ID_WEB` correcto;
   - `RESULTADO_API=CREADO_DRAFT` o `ACTUALIZADO_API`;
   - `ERROR_API` vacío;
   - `FECHA_ENVIO_API` informada.
4. Revisar el registro en backoffice y comprobar que siga inactivo cuando sea CREAR.
5. En ACTUALIZAR, comprobar que no cambiaron propietario, visitas, estado ni campos omitidos.
6. No habilitar el siguiente caso hasta cerrar el anterior.

Orden recomendado: primero los tres CREAR y luego los tres ACTUALIZAR sobre registros controlados.

## 4. Cierre y rollback operativo

- Ante cualquier 401, 403, 404, 409, 422 o respuesta inesperada: detener el piloto y mantener el trigger deshabilitado.
- No reintentar cambiando `ENVIAR_API` sin entender `ERROR_API` y revisar el backoffice.
- Ejecutar nuevamente los tres auditores y comparar los CSV antes/después.
- Los `CREAR` quedan inactivos, por lo que el piloto debe auditar todos los estados y comprobar directamente los seis `ID_WEB`; no usar `--active` para esta evidencia.
- Reactivar el trigger sólo con seis casos correctos, backoffice verificado y gate de release aprobado.

Comandos de auditoría:

```bash
php artisan mfa:artists:audit --csv=storage/app/audits/artistas-piloto-antes.csv
php artisan mfa:recipes:audit --csv=storage/app/audits/recetas-piloto-antes.csv
php artisan mfa:myths:audit --csv=storage/app/audits/mitos-piloto-antes.csv

# Repetir después de las seis operaciones con nombres distintos:
php artisan mfa:artists:audit --csv=storage/app/audits/artistas-piloto-despues.csv
php artisan mfa:recipes:audit --csv=storage/app/audits/recetas-piloto-despues.csv
php artisan mfa:myths:audit --csv=storage/app/audits/mitos-piloto-despues.csv
```

En los CSV `despues`, localizar los seis `ID_WEB` informados por la hoja y registrar su score y faltantes. La comparación global sirve como control adicional, pero no reemplaza esa verificación dirigida.

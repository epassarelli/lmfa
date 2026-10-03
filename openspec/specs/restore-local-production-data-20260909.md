# Restaurar datos locales desde backup productivo

## Autorizacion
2026-09-09: el usuario autoriza explicitamente pisar la BDD local tras aprobar conservar esquema y migraciones locales y excluir sesiones/tokens productivos.

## Alcance
Reemplazar datos de tablas locales excepto migrations, desde storage/app/local-backups/u376128922_mifolkarg.sql. Respaldo previo local guardado y no vacio. Sin acceso ni cambios a produccion. No ejecutar DDL del dump, migraciones ni crear otra base.

## Compatibilidad
Omitir noticias legacy (no existe localmente; news si existe). Para shows conservar columnas comunes; las adicionales quedan disponibles en el dump original. Vaciar datos demo al reemplazar. Excluir sesiones, tokens personales, recuperacion de contrasenas, cuentas sociales y colas de trabajo; limpiar remember_token y secretos 2FA de usuarios importados.

## Validacion
Preparar plan local ignorado por Git, con INSERT exclusivamente y columnas verificadas contra information_schema. Ejecutar primero en una transaccion revertida, comprobando cantidades y todas las claves foraneas; ante error revertir e investigar. Aplicar solo tras validacion, en una transaccion. Conservar migrations identicas y verificar conteos y respuesta HTTP local. No ejecutar suite de tests sobre datos recuperados. Sin cambios de queries ni assets de la aplicacion.

## Resultado
Aplicado el 2026-09-09 en mfa del servicio db local. Primera prueba revertida por shows.detalle NULL (origen nullable, destino NOT NULL); adaptacion a texto vacio sin agregar contenido. Segunda prueba correcta; aplicacion confirmada con conteos exactos en todas las tablas importadas, 87 relaciones FK sin huerfanos y 81 migraciones locales identicas. No se ejecuto DDL.

Home, login, noticias, recetas y festivales HTTP 200; home caliente TTFB 0.490 s. Cache de aplicacion limpiada. Backups, scripts de recuperacion y resultados permanecen ignorados por Git en storage/app/local-backups. No se importaron archivos de imagen (el dump contiene solo BD). No se verifico login interactivo.

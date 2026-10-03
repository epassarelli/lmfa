## Context

Vite genera nombres con hash y un `manifest.json` que relaciona entradas fuente con CSS, JavaScript y fuentes finales. `public/build` está ignorado por Git salvo el manifiesto histórico, CI ejecuta el build pero descarta el directorio al terminar, y producción no tiene NPM. Por eso una transferencia parcial puede dejar el manifiesto apuntando a archivos inexistentes y romper toda la presentación.

## Goals / Non-Goals

**Goals:**

- detectar manifiestos ausentes, inválidos, inseguros o con archivos faltantes;
- ejecutar la validación de forma idéntica localmente y en CI;
- entregar todo `public/build` como un único artefacto de CI;
- dejar un procedimiento corto y reversible para servidores sin Node.

**Non-Goals:**

- desplegar automáticamente a producción;
- instalar Node en el servidor;
- versionar assets compilados con hash en Git;
- cambiar la configuración o las entradas de Vite.

## Decisions

1. Se usará un script Node sin dependencias adicionales. Node ya es requisito del build y las APIs estándar bastan para leer JSON, resolver rutas y verificar archivos.
2. El validador rechazará rutas absolutas o que escapen de `public/build`, además de comprobar la existencia de `file`, `css` y `assets` declarados por el manifiesto. Así el mismo script cubre integridad y transferencia segura.
3. CI ejecutará `npm run build:verify` inmediatamente después de `npm run build` y publicará `public/build` mediante `actions/upload-artifact`. El artefacto será de corta retención y no contendrá `.env`, `vendor` ni `node_modules`.
4. Las pruebas usarán `node:test` y directorios temporales, evitando servicios externos y escrituras persistentes.

## Risks / Trade-offs

- [El artefacto de CI no incluye el resto de la aplicación] → documentar que se superpone `public/build` al deploy de código existente.
- [Un manifiesto válido no garantiza que el servidor web sirva los archivos] → mantener un smoke HTTP posterior a la carga.
- [El upload aumenta ligeramente tiempo y almacenamiento de CI] → retención corta y alcance exclusivo a `public/build`.
- [Assets viejos pueden quedar en producción] → la unidad de transferencia es la carpeta completa; la limpieza remota sigue siendo manual y explícita.

## Migration Plan

1. Incorporar validador, pruebas y scripts NPM.
2. Integrar validación y publicación del artefacto en CI.
3. Ejecutar build, pruebas y validación local.
4. Adoptar el artefacto en el próximo deploy; ante problemas, conservar el `public/build` anterior y restaurarlo.

## Open Questions

Ninguna para este alcance. La automatización de transferencia al hosting queda fuera de esta tarea porque requeriría credenciales y autorización adicional.

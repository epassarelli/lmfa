## 1. Relevamiento y contrato de Drive

- [x] 1.1 Identificar la pestaña operativa real del backlog MFA en Google Drive y confirmar los encabezados mínimos para selección, reclamo y cierre.
- [x] 1.2 Relevar la documentación mínima relacionada en `project/docs` para obtener reglas de alcance, validación y frenado humano.
- [x] 1.3 Verificar la vía ejecutable real de `codex exec` en el host actual o registrar el bloqueo técnico si sigue devolviendo `Acceso denegado`.
- [x] 1.4 Listar los archivos exactos a tocar antes de implementar.

## 2. Núcleo del orquestador

- [x] 2.1 Implementar configuración declarativa por proyecto con URL/ID de Drive, mapeo de columnas, ventanas horarias, límites y comandos de validación.
- [x] 2.2 Implementar el adaptador de Google Drive para leer filas, evaluar elegibilidad y actualizar estados con evidencia.
- [x] 2.3 Implementar el selector determinístico de tareas elegibles con soporte de `IA_AUTONOMA` e `IA_CON_VALIDACION`.
- [x] 2.4 Implementar el lock local por repositorio y el reclamo remoto seguro de tarea.

## 3. Pipeline de ejecución

- [x] 3.1 Implementar el flujo de `dry-run` de punta a punta sin mutaciones permanentes.
- [x] 3.2 Implementar el flujo real de ejecución para una sola tarea con obtención de contexto, límite de alcance y registro de archivos afectados.
- [x] 3.3 Integrar pruebas, lint y controles configurables como etapa obligatoria antes del cierre.
- [x] 3.4 Implementar la continuación automática hacia la siguiente tarea respetando tiempo máximo, cantidad máxima y necesidad de autorización humana.

## 4. Revisión y resultados

- [x] 4.1 Implementar un revisor independiente del diff y del criterio de terminado.
- [x] 4.2 Registrar resultados, errores y evidencias en logs estructurados por corrida.
- [x] 4.3 Actualizar Drive a `Hecha`, `En revisión` o `Bloqueada` con motivo y evidencia consistente.

## 5. Documentación, legado y automatización local

- [x] 5.1 Documentar la ejecución manual mediante `codex exec`, incluyendo `dry-run` y manejo del bloqueo detectado en Windows.
- [x] 5.2 Crear el script de instalación para Programador de tareas de Windows sin activarlo.
- [x] 5.3 Marcar `project/docs/backlog.json` explícitamente como legado sin eliminarlo.

## 6. Validación

- [x] 6.1 Agregar pruebas automatizadas del selector, locks, `dry-run`, transición de estados y revisor independiente.
- [x] 6.2 Ejecutar la suite focalizada del orquestador y registrar bloqueos.
- [x] 6.3 Probar primero un circuito completo en `dry-run`.
- [x] 6.4 Ejecutar luego una única tarea segura sin despliegue ni acciones destructivas, sólo después de la aprobación de esta spec.

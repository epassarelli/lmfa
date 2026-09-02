# FUENTES_CANONICAS

> Indice operativo para humanos y agentes.
> Ultima actualizacion: 2026-09-01.

---

## 1. Precedencia

1. instrucciones explicitas del usuario;
2. `AGENTS.md`;
3. cambios OpenSpec aprobados o en ejecucion bajo `openspec/changes/*`;
4. documentacion canonica del repo en `project/docs/*`;
5. fuentes operativas externas en Google Drive;
6. documentacion historica o legacy.

Notas:

- en este repo no existe `openspec/project.md`;
- el backlog diario activo vive en Drive;
- Git sigue mandando sobre estado, vision, funcional, arquitectura, modelo de datos, migraciones, API y gobernanza documental.

---

## 2. Lectura minima al iniciar sesion

1. `AGENTS.md`
2. `project/docs/FUENTES_CANONICAS.md`
3. `project/docs/00_estado_actual.md`
4. el cambio OpenSpec puntual si existe
5. solo los documentos adicionales que el alcance real exija

---

## 3. Documentos canonicos en Git

- `project/docs/00_estado_actual.md`
- `project/docs/00_vision.md`
- `project/docs/01-funcional.md`
- `project/docs/02_modelo_datos.md`
- `project/docs/03_backlog_mvp.md`
- `project/docs/04_arquitectura.md`
- `project/docs/05_migraciones.md`
- `project/docs/06_endpoints_api.md`
- `project/docs/07_hoja_de_ruta.md`
- `project/docs/08_inventario_tecnico_legacy.md`
- `project/docs/09_matriz_canonica_entidades_relaciones.md`
- `project/docs/10_auditoria_contratos_editoriales.md`

---

## 4. Fuentes operativas externas

- Google Sheets `Backlog Asistente ChatGPT`, pestana `Backlog`
- Google Sheets `Backlog Asistente ChatGPT`, pestana `Inventario fuentes`
- documentos editoriales o metricos de Drive referenciados por la hoja de ruta o el backlog maestro

Regla:

- Git manda sobre decisiones funcionales, tecnicas y estructurales;
- Drive manda sobre prioridad diaria, seguimiento corto y coordinacion operativa.

---

## 5. Referencias legacy y aclaraciones

- `project/docs/backlog.json` es legado estructurado e historico;
- `01_funcional.md` debe interpretarse como referencia legacy a `01-funcional.md`;
- `00_estado_actual.md` registra hechos comprobados del ultimo corte operativo y prevalece frente a resumentes historicos desactualizados;
- los documentos de integracion editorial y rollout deben leerse como soporte operativo complementario, no como fuente superior al estado actual.

---

## 6. Contraste documental vigente

Contraste base de esta actualizacion:

- ruta local: `C:\proyectos\lmfa`
- rama observada: `main`
- fecha de contraste: `2026-09-01`

Nota:

- `00_estado_actual.md` estaba modificado en el worktree al iniciar esta revision y se trato como fuente operativa sin sobreescribirlo.

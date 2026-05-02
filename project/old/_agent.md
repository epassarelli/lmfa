# agent.md

## 1. Rol del agente
Sos el agente técnico principal del proyecto **Pasarela de Contenidos** dentro del portal Mi Folklore Argentino. Tu objetivo es avanzar de forma autónoma, ordenada y segura sobre el backlog del proyecto, implementando tareas, generando pruebas y dejando trazabilidad suficiente para revisión humana mínima.

---

## 2. Fuentes de verdad — orden de lectura al iniciar sesión

Lee estos archivos **en este orden** al comenzar cada sesión:

1. `/memory/MEMORY.md` → reglas vigentes y estado del proyecto
2. `docs/00_estado_actual.md` → estado real del código y la BD (fuente de verdad operativa)
3. `/project/backlog.json` → tareas pendientes y dependencias
4. `project/specs/_global_rules.md` → restricciones globales de implementación (OpenSpec)

Si una tarea tiene `doc_refs`, leer esos archivos **solo antes de implementar esa tarea concreta**.
Los docs `01_funcional.md` a `05_migraciones.md` son contexto histórico; consultar solo si hay ambigüedad funcional.

---

## 3. Regla de consumo de contexto

No releas todo el proyecto en cada ciclo. Trabaja así:
1. Leer `00_estado_actual.md` y `backlog.json`
2. Identificar la próxima tarea elegible
3. Consultar solo los archivos referenciados en `doc_refs`
4. Implementar con mínima lectura adicional
5. Al terminar: actualizar backlog + actualizar `00_estado_actual.md` + commit

---

## 4. Cómo elegir la próxima tarea

Tomar la tarea que cumpla todas estas condiciones:
- `status = pending`
- todas sus `dependencies` están en `done`
- prioridad más alta disponible
- en empate: menor orden de aparición en el backlog

---

## 5. Estados válidos del backlog

- `pending` / `in_progress` / `blocked` / `done` / `needs_review`

No inventar estados nuevos.

---

## 6. Flujo de trabajo obligatorio por tarea

1. Cambiar estado a `in_progress` en backlog.json
2. Analizar alcance exacto leyendo solo lo necesario
3. **Escribir spec** en `project/specs/<id>_<slug>.md` (copiar `_template.md`) y presentarlo al usuario — **no implementar hasta recibir aprobación**
4. Implementar estrictamente lo descrito en el spec aprobado
5. Crear o actualizar tests (ver sección 9)
6. Ejecutar pruebas y el checklist de validación manual del spec
7. Cambiar estado a `done` (o `needs_review` si requiere aprobación humana)
8. Actualizar `backlog.json` con `summary` y `tests`
9. Actualizar `docs/00_estado_actual.md`
10. Commit con formato de convención

---

## 7. Estrategia de ramas — REGLA FIJA

**Trabajar siempre en la rama activa del proyecto.**

- Al iniciar sesión: leer el `gitStatus` para conocer la rama actual
- Si existe una rama de trabajo activa (ej. `pasarelaContenidos`), continuar ahí
- Solo crear ramas nuevas si el usuario lo pide **explícitamente**
- La convención de ramas `feature/<task-id>-<slug>` aplica solo si el usuario lo solicita

---

## 8. Base de datos — REGLAS CRÍTICAS (no negociables)

### El agente NUNCA puede:
- Ejecutar `docker exec ... mysql ... < archivo.sql` sin autorización explícita del usuario
- Ejecutar `php artisan migrate` sin mostrar primero `migrate:status` y esperar confirmación
- Usar `RefreshDatabase` en tests (causa crasheos de MariaDB en Docker/Windows)
- Crear bases de datos adicionales (no existe `mfa_testing`)

### El agente PUEDE (solo lectura):
- `docker exec lmfa-db-1 mysql ... -e "SHOW TABLES;"`
- `docker exec lmfa-db-1 mysql ... -e "DESCRIBE tabla;"`
- `docker exec lmfa-db-1 mysql ... -e "SELECT COUNT(*) FROM tabla;"`
- `php artisan migrate:status` (solo lectura)

### Cuando se necesite modificar la BD:
1. Crear el archivo `.sql` con los cambios (usando `IF NOT EXISTS` / `IF EXISTS`)
2. Presentarlo al usuario con descripción clara
3. Esperar que el usuario lo ejecute por el método que prefiera

### Restauración de estructura tras crash:
```
docker exec -i lmfa-db-1 mysql -umfa -pmfa mfa < database/setup_pasarela_tables.sql
docker exec -i lmfa-db-1 mysql -umfa -pmfa mfa < database/patch_missing_columns.sql
docker exec -i lmfa-db-1 mysql -umfa -pmfa mfa < database/patch_news_columns.sql
```
(Ejecutar solo si el usuario lo autoriza)

---

## 9. Testing — REGLAS FIJAS

- **SIEMPRE** usar `DatabaseTransactions` sobre la BD `mfa`
- **NUNCA** usar `RefreshDatabase` — crashea MariaDB en Docker/Windows con ALTER TABLE
- Tests en `tests/Feature/Pasarela/`
- Si un test existente usa `RefreshDatabase`, cambiarlo a `DatabaseTransactions` inmediatamente
- Usar `Queue::fake()` para tests con jobs, `Http::fake()` para conectores externos
- Mínimo: un test feature por historia de usuario

---

## 10. Convención de commits

```
feat(<task-id>): descripción breve
fix(<task-id>): descripción breve
refactor(<task-id>): descripción breve
test(<task-id>): descripción breve
```

Incluir siempre: `Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>`

---

## 11. Cuándo frenar y pedir intervención humana

Detenerse si:
- Falta definición funcional que impacta datos, UX o reglas de negocio
- Una tarea contradice documentos existentes
- Una integración externa requiere credenciales
- Hay riesgo de afectar datos del usuario
- Hay múltiples caminos con impacto arquitectónico significativo
- Dos fallos consecutivos del mismo problema de infraestructura

En esos casos: marcar tarea como `blocked`, registrar motivo, proponer alternativas.

---

## 12. Criterios técnicos

- Laravel 10/11, PHP 8.2+
- Colas: `QUEUE_CONNECTION=database` (no Redis hasta que escale)
- Migraciones nuevas: siempre con guards `IF NOT EXISTS` / `Schema::hasColumn()`
- Conectores desacoplados en `app/Services/Connectors/`
- Auditoría: usar `AuditLog::log()` en acciones críticas

---

## 13. Al finalizar cada sesión (obligatorio)

1. Actualizar `status`, `summary` y `tests` en `backlog.json` para todas las tareas tocadas
2. Actualizar `docs/00_estado_actual.md` con el estado real del código y la BD
3. Actualizar `memory/project_estado_actual.md`
4. Hacer commit de todos los cambios

---

## 14. Qué no hacer

- No releer todos los documentos en cada ciclo
- No cambiar arquitectura sin justificarlo
- No mezclar varias tareas no relacionadas
- No dar una tarea por finalizada sin validación mínima
- No inventar credenciales, endpoints o reglas no documentadas
- No ejecutar SQL modificatorio en el contenedor sin autorización

---

## 15. Meta del proyecto

Construir una pasarela de contenidos donde artistas, productoras, peñas y organizadores puedan cargar eventos y noticias, publicarlas en el portal y distribuirlas a redes sociales mediante un flujo moderado, trazable, escalable y automatizable.

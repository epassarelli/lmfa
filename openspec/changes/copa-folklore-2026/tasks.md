# Tareas: copa-folklore-2026

## Estado general

Pendiente de aprobacion por el usuario. No implementar hasta aprobacion explicita.

## Frente 1: Backend base

- [ ] Inspeccionar modelo real `Interprete`, su tabla y convenciones de relacion.
- [ ] Diseñar migraciones para torneos, grupos, participantes y partidos.
- [ ] Definir modelos Eloquent y relaciones.
- [ ] Crear seeder inicial de torneo, grupos y 32 participantes.
- [ ] Implementar `FolkloreTournamentFixtureService`.
- [ ] Implementar `FolkloreTournamentStandingService`.
- [ ] Crear tests de fixture, posiciones y persistencia basica del modulo.
- [ ] Validar que no se introducen cambios de BD fuera del alcance del modulo.

## Frente 2: Contenido editorial / marketing

- [ ] Crear carpeta `docs/copa-folklore-2026/`.
- [ ] Redactar lanzamiento editorial.
- [ ] Redactar reglamento con la clausula legal obligatoria.
- [ ] Redactar copies para Instagram e historias.
- [ ] Proponer nombres de zonas, hashtags y calendario editorial inicial.
- [ ] Documentar criterios de votacion y aclaraciones legales.

## Frente 3: Panel administrativo

- [ ] Relevar patrones existentes de rutas, controllers, requests y vistas backend.
- [ ] Crear listado y detalle de torneos.
- [ ] Crear vistas de grupos, participantes y partidos.
- [ ] Crear formulario de edicion de partido.
- [ ] Permitir carga manual de votos, URL de Instagram, estado y ganador.
- [ ] Probar flujo admin con validaciones y permisos existentes.

## Frente 4: Frontend publico

- [ ] Relevar patrones actuales de controllers y vistas frontend con Tailwind.
- [ ] Crear landing del torneo.
- [ ] Crear pagina de participantes.
- [ ] Crear pagina de fixture agrupado por jornada.
- [ ] Crear pagina de zonas con tabla y partidos.
- [ ] Crear pagina de llaves por fase en formato simple.
- [ ] Crear pagina de reglamento.
- [ ] Validar que el frontend no usa Bootstrap y no rompe rutas existentes.

## Integracion y cierre

- [ ] Conectar frontend y admin al modelo definido por backend.
- [ ] Ejecutar tests aplicables del modulo.
- [ ] Validar checklist funcional completo del pedido.
- [ ] Actualizar `project/docs/00_estado_actual.md` si corresponde.
- [ ] Preparar resumen final con archivos, rutas, migraciones, seeders, validacion, riesgos y siguientes pasos.

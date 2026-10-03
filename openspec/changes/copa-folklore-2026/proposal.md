# Cambio: copa-folklore-2026

## Objetivo

Incorporar una nueva seccion editorial y participativa llamada `Copa del Folklore Argentino 2026` dentro de Mi Folklore Argentino, con soporte para torneo por grupos, cruces eliminatorios, carga manual de resultados desde Instagram y visualizacion publica del certamen.

## Problema actual

El sitio no cuenta con un modulo de torneo tematico que permita:

- organizar participantes en grupos y cruces
- registrar partidos y resultados
- calcular posiciones por zona
- publicar fixture y tablas en frontend
- administrar votos y enlaces de Instagram desde el panel
- sostener una campana editorial asociada al torneo

El pedido original combina backend, admin, frontend y contenido. Sin una spec unica y trazable, la implementacion correria el riesgo de mezclar responsabilidades, redefinir el modelo de datos en varias capas y romper convenciones del proyecto.

## Alcance incluido

- Crear una nueva propuesta OpenSpec para el modulo `Copa del Folklore Argentino 2026`.
- Definir un modelo de datos propio del torneo bajo propiedad exclusiva del frente Backend base.
- Contemplar torneo inicial de 32 interpretes, 8 zonas de 4 participantes y fase eliminatoria posterior.
- Permitir carga manual de votos, ganador y URL de Instagram por partido.
- Exponer paginas publicas del torneo en el frontend del sitio.
- Exponer administracion basica del torneo en el panel existente.
- Crear documentacion editorial inicial en `docs/copa-folklore-2026/`.
- Incluir validaciones y tests minimos acordes al riesgo.

## Fuera de alcance

- Integracion automatica con Instagram o cualquier otra red social.
- Uso de marcas oficiales, nombres protegidos, logos o estetica asociada a FIFA.
- Cambios en Pasarela de Contenidos o acoplamiento con ese modulo.
- Bracket visual avanzado en el MVP.
- Nuevo sistema de permisos si el actual alcanza.
- Creacion automatica de ramas o ejecucion de migraciones sin autorizacion explicita.

## Archivos afectados

- `openspec/changes/copa-folklore-2026/proposal.md`
- `openspec/changes/copa-folklore-2026/design.md`
- `openspec/changes/copa-folklore-2026/tasks.md`
- `app/Models/*` relacionados al torneo
- `app/Services/*` para fixture y posiciones
- `database/migrations/*` del modulo
- `database/seeders/*` del modulo
- `routes/web.php`
- `routes/admin.php`
- `app/Http/Controllers/Frontend/*`
- `app/Http/Controllers/Backend/*`
- `resources/views/frontend/*` del modulo
- `resources/views/backend/*` del modulo
- `tests/Feature/*`
- `docs/copa-folklore-2026/*`
- `project/docs/00_estado_actual.md` si el modulo queda implementado

## Reglas funcionales

### Caso 1
Dado un torneo en estado draft o active
Cuando un visitante ingresa a `/copa-del-folklore-argentino-2026`
Entonces debe ver la presentacion del torneo, proximos partidos, ultimos resultados y accesos a las secciones del modulo.

### Caso 2
Dado un torneo con 8 grupos y 32 participantes
Cuando el sistema genera el fixture de grupos
Entonces debe crear 6 partidos por grupo y 48 partidos en total sin duplicados.

### Caso 3
Dado un partido de grupos finalizado
Cuando el servicio de posiciones procesa la tabla
Entonces debe computar PJ, PG, PE, PP, votos a favor, votos en contra, diferencia y puntos usando solo partidos `phase = group` y `status = finished`.

### Caso 4
Dado un administrador autenticado
Cuando edita un partido desde el panel
Entonces debe poder cargar votos, URL de Instagram, estado del partido y ganador manual en caso de empate.

### Caso 5
Dado un interprete sugerido para el seed inicial
Cuando exista correspondencia en la tabla real `interpretes`
Entonces el participante debe vincularse mediante `artist_id`; si no existe, debe persistirse con `artist_id` nullable y `display_name`.

## Reglas tecnicas

- No implementar hasta que esta spec quede aprobada por el usuario.
- El frontend publico debe usar Tailwind CSS 3.x, no Bootstrap, porque Bootstrap contradice el stack oficial del proyecto.
- El panel admin debe reutilizar AdminLTE 3, middleware y permisos existentes.
- Solo Backend base puede definir y poseer el modelo de datos del torneo.
- No modificar tablas existentes salvo necesidad justificada y explicitada en la implementacion.
- No ejecutar migraciones ni SQL modificatorio sin autorizacion del usuario.
- Si se necesita soporte con artistas, adaptarse al modelo real `Interprete` y no inventar nuevas entidades.
- Mantener las rutas existentes sin cambios colaterales ni impacto SEO fuera del nuevo modulo.

## Validacion

- Verificar que la propuesta cubre backend, admin, frontend y contenido sin superponer propiedad de datos.
- Confirmar que el stack de frontend publico queda alineado con Tailwind y no con Bootstrap.
- Confirmar que la relacion con artistas usa `Interprete` si existe match.
- Confirmar que el flujo no requiere integracion automatica con Instagram.
- Confirmar que la implementacion posterior podra validarse con tests y checklist funcional.

## Riesgos

- El pedido original solicita Bootstrap en frontend publico, lo cual contradice el stack del proyecto; se corrige a Tailwind en esta spec.
- Puede haber diferencias entre nombres sugeridos y registros reales en `interpretes`, por lo que el seed debe tolerar asociaciones parciales.
- El calculo de desempates puede requerir orden manual cuando no alcance la informacion automatica.
- El alcance es amplio y debe ejecutarse en etapas para evitar una entrega dificil de revisar.

## Criterio de aceptacion

El cambio se considera listo para implementar cuando la spec aprobada deja definidos el alcance por frentes, el modelo de datos propietario del backend, las restricciones de stack, el flujo manual de resultados y el plan de trabajo trazable hasta validacion final.

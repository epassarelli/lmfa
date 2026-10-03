# Tareas: enforce-body-headings-no-h1

## Estado general

Propuesta creada. Pendiente de aprobación e implementación.

## Frente 1: Relevamiento final

- [ ] Confirmar todas las entidades activas con campos body-equivalentes enriquecidos
- [ ] Confirmar endpoints API de escritura realmente activos para cada entidad alcanzada
- [ ] Confirmar cómo se inicializa Summernote en Eventos
- [ ] Confirmar cómo `x-textarea` activa CKEditor en Intérpretes
- [ ] Listar archivos exactos a tocar antes de implementar

## Frente 2: Normalización compartida

- [ ] Reutilizar o generalizar el sanitizador introducido en Evergreen
- [ ] Definir traits o helpers reutilizables para normalizar `body`, `noticia`, `biografia`, `mito` y `receta`
- [ ] Evitar duplicación innecesaria entre requests y servicios

## Frente 3: Backend y API

- [ ] Aplicar normalización en requests backend de Noticias, Eventos, Festivales, Intérpretes, Mitos y Comidas
- [ ] Aplicar normalización en requests API equivalentes donde exista escritura
- [ ] Preservar el comportamiento ya validado de Enciclopedia/Evergreen
- [ ] Reforzar servicios/controladores donde la persistencia actual lo justifique

## Frente 4: UI de editores

- [ ] Aplicar un perfil compartido de CKEditor sin `h1`
- [ ] Asignar ese perfil a Noticias, Festivales, Enciclopedia, Intérpretes, Mitos y Comidas
- [ ] Ajustar Summernote en Eventos para no ofrecer `h1`
- [ ] Verificar que `h2` y `h3` sigan disponibles

## Frente 5: Testing y validación

- [ ] Agregar tests de formularios/scripts para confirmar ausencia de `h1` en UI
- [ ] Agregar tests backend de normalización `h1 -> h2` por entidad representativa
- [ ] Agregar tests API donde aplique
- [ ] Ejecutar formatter
- [ ] Ejecutar suite focalizada de tests
- [ ] Actualizar `project/docs/00_estado_actual.md` si el cambio queda implementado

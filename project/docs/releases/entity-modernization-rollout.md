# Rollout por oleadas - Modernizacion de entidades MFA

> Documento historico-operativo alineado al 2026-09-01.
> Conserva el orden de despliegue y aclara que ya fue desplegado tecnicamente y que sigue pendiente a nivel operativo/editorial.

---

## Resumen

El programa de modernizacion se desplego por oleadas independientes:

1. Biografias / Artistas
2. Recetas
3. Mitos y Leyendas
4. Integracion editorial Artista + Receta + Mito
5. Discografia / Cancionero

La regla de no mezclar varias entidades en una sola ventana de release sigue siendo valida como historial util.

---

## Estado por oleada al 2026-09-01

### Oleada 1 - Biografias / Artistas

- desplegada tecnicamente;
- backend, API, frontend, SEO/schema y auditor operativos;
- pendiente la curacion masiva posterior.

### Oleada 2 - Recetas

- desplegada tecnicamente;
- contrato editorial y campos estructurados activos;
- pendiente la recuperacion masiva del inventario.

### Oleada 3 - Mitos y Leyendas

- desplegada tecnicamente;
- contrato cultural y auditor operativos;
- pendiente la recuperacion masiva del inventario.

### Oleada 4 - Integracion editorial

- desplegada tecnicamente;
- Artista, Receta y Mito ya soportados por la bandeja `Contenidos` y Apps Script;
- siguen pendientes los seis casos controlados de CREAR/ACTUALIZAR en produccion antes de reactivar flujo automatico de volumen.

### Oleada 5 - Discografia / Cancionero

- no debe tratarse como frente cerrado a nivel operativo;
- permanece condicionado por la politica de derechos;
- no habilitar automatizacion de letras ni enriquecimiento textual sin gate humano explicito.

---

## Criterio de cierre actualizado

La modernizacion tecnica ya puede leerse como desplegada en Festivales, Biografias, Recetas y Mitos.

Todavia no debe considerarse completamente cerrada en terminos operativos mientras sigan pendientes:

1. piloto controlado de seis operaciones de Content Refresh;
2. lotes editoriales iniciales con medicion antes/despues;
3. incorporacion de visitas al auditor de Festivales;
4. definicion de derechos para musica y letras.

---

## Historial util conservado

Se conserva como antecedente valido:

- el despliegue por oleadas;
- la recomendacion de prechecks de branch, `git status` y `migrate:status`;
- la separacion entre cierre tecnico del release y recuperacion editorial posterior.

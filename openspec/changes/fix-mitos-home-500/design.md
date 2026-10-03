## Context

La ruta publica `/mitos-y-leyendas-argentinas` usa `MitosController@index` para armar la home del modulo. Hoy el metodo construye claves de cache con una variable `$letra` que no existe en ese contexto, lo que puede disparar un error en tiempo de ejecucion antes del render. Durante la revision tambien se detecto una referencia a `route('mito.show', ...)` en un componente lateral, mientras que la ruta declarada en `routes/web.php` es `mitos.show`.

## Goals / Non-Goals

**Goals:**
- Eliminar la causa directa del `500` en la home publica de mitos.
- Mantener intactas las URLs publicas y el contenido esperado de la portada.
- Corregir el enlace lateral relacionado para evitar errores de generacion de URL si el componente se renderiza.
- Dejar cobertura automatizada suficiente para detectar una regresion de este flujo.

**Non-Goals:**
- No redisenar la home de mitos.
- No cambiar reglas editoriales, consultas de contenido ni schema de base de datos.
- No tocar otras secciones publicas salvo lo estrictamente relacionado con el modulo de mitos.

## Decisions

1. Usar claves de cache estables para la home de mitos.
Se reemplazaran las claves que interpolan `$letra` por claves fijas del indice, alineadas con el comportamiento real del metodo `index()`.

Alternativa considerada:
- Definir una variable artificial para conservar el prefijo `letter`.
Se descarta porque agrega ruido semantico y no representa el comportamiento del indice principal.

2. Corregir el nombre de ruta del componente lateral.
El componente debe usar `mitos.show`, que es la ruta publica existente y coherente con el resto del modulo.

Alternativa considerada:
- Crear una ruta alias `mito.show`.
Se descarta porque agrega deuda y multiplica nombres de ruta para el mismo recurso.

3. Validar con test feature enfocado en la URL publica.
La mejor proteccion es un test que solicite la home de mitos y confirme respuesta `200`, evitando depender solo de revision manual.

Alternativa considerada:
- Solo checklist manual.
Se descarta como proteccion principal porque este tipo de regression es facil de reintroducir.

## Risks / Trade-offs

- [Caches viejas en produccion] -> Mitigacion: usar nuevas claves o limpiar cache de aplicacion en el despliegue si fuera necesario.
- [El componente lateral no se usa hoy en esa home] -> Mitigacion: igual se corrige porque el bug sigue latente y la solucion es de bajo riesgo.
- [Falta de fixtures de mitos en tests] -> Mitigacion: crear datos minimos dentro del test siguiendo las practicas actuales del proyecto.

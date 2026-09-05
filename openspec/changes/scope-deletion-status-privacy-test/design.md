## Context

La ficha publica se renderiza dentro del layout general, que puede incorporar
scripts con caracteres `@`. El modelo almacena `external_user_hash` y
opcionalmente `user_id`, datos que nunca deben aparecer en la respuesta.

## Goals / Non-Goals

**Goals:**
- Probar directamente la ausencia de datos sensibles de la solicitud.

**Non-Goals:**
- Modificar la vista, el controlador o scripts globales.

## Decisions

- La prueba usara valores centinela para hash e ID y verificara su ausencia.
  Esto conserva la garantia de privacidad sin acoplarla al HTML compartido.

## Risks / Trade-offs

- [Un nuevo campo sensible podria no estar cubierto] -> La prueba documenta los
  dos campos persistidos actualmente prohibidos por el contrato de la vista.

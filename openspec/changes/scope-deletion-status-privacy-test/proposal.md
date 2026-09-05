## Why

La prueba de estado de eliminacion falla por contenido global legitimo del
layout, no por una exposicion de datos personales. La cobertura debe verificar
los campos realmente sensibles que el endpoint tiene prohibido renderizar.

## What Changes

- Sustituir la asercion global sobre `@` por aserciones contra el hash externo
  y el identificador interno de usuario.

## Capabilities

### New Capabilities
- `deletion-status-privacy-test`: garantia verificable de que la pagina publica
  de estado no expone identificadores sensibles persistidos.

### Modified Capabilities

- Ninguna.

## Impact

- Cobertura de `MetaDataDeletionFlowTest`; no cambia la respuesta publica ni la
  integracion con Meta.

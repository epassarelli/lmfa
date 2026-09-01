# Integracion editorial - Artistas, Recetas y Mitos

> Estado operativo alineado al 2026-09-01.

---

## Objetivo

Extender la bandeja `Contenidos` y el cargador Apps Script para soportar:

- descubrimiento;
- curacion;
- `ACCION_API=CREAR/ACTUALIZAR`;
- gate `ENVIAR_API`;
- `ID_WEB` obligatorio para updates;
- `SCORE_CALIDAD` y `FALTANTES`.

---

## Tipos soportados

- Artista
- Receta
- Mito

Estado:

- los tres tipos ya estan soportados en la integracion;
- la escalada de volumen sigue bloqueada hasta cerrar el piloto controlado de seis casos en produccion.

---

## Dependencias ya cumplidas

- modernizacion de Biografias/Artistas desplegada;
- modernizacion de Recetas desplegada;
- modernizacion de Mitos desplegada;
- bandeja `Contenidos` y Apps Script preparados para estos tipos.

---

## Reglas operativas

- `CREAR` no puede incluir `ID_WEB`;
- `ACTUALIZAR` exige `ID_WEB`;
- los updates pueden ser parciales;
- `ENVIAR_API=S` es el unico permiso de envio;
- tras exito debe persistirse `RESULTADO_API` y resetearse `ENVIAR_API=N`;
- no se habilitan migraciones ni deploys desde Apps Script.

Prioridad editorial:

1. prioridad de deuda;
2. menor `SCORE_CALIDAD`;
3. mayores visitas dentro del mismo nivel;
4. ID como desempate estable.

---

## Gate vigente

Antes de reactivar automatizacion con volumen:

1. crear y actualizar un Artista;
2. crear y actualizar una Receta;
3. crear y actualizar un Mito;
4. validar cada caso en Sheet y backoffice;
5. contrastar auditoria antes/despues.

Hasta ese cierre, la integracion debe tratarse como activa pero todavia en piloto controlado.

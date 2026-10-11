## 1. Persistencia (migración sujeta a confirmación)
- [x] 1.1 Migración reversible: `instagram`, `facebook`, `contact_stage`, `last_contacted_at`, `contact_notes`.
- [x] 1.2 `migrate:status` mostrado y confirmación humana recibida; migración aplicada sola en la base local.

## 2. Dominio y backend
- [x] 2.1 Modelo: fillable, casts, accessors de URL de Instagram/Facebook.
- [x] 2.2 Config de etapas.
- [x] 2.3 Request: validación de redes, etapa, fecha y notas.
- [x] 2.4 Controlador: filtro por etapa, `markContacted`, `export` CSV de contactables.
- [x] 2.5 Rutas (`organizations/export`, `organizations/{organization}/contacted`).

## 3. Vistas
- [x] 3.1 Formulario: redes, etapa, último contacto, notas.
- [x] 3.2 Listado: etapa, enlaces a redes, filtro, botón "Registrar contacto" y "Exportar contactables".

## 4. Demo y tests
- [x] 4.1 Seeder demo: dos organizaciones en contacto por redes.
- [x] 4.2 Tests: etapa/validación, enlaces, registrar contacto, filtro, export (exclusiones, inyección de fórmulas, acceso).

## 5. Cierre
- [x] 5.1 CHANGELOG, estado actual y revisión de performance.

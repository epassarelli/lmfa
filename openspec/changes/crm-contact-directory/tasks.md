## 1. Relevamiento

- [x] 1.1 Inventariar datos de contacto existentes en solo lectura (interpretes 445 / 63 con contacto; resto de entidades vacías; festivales sin campos).
- [ ] 1.2 Confirmar convenciones de permisos (spatie), layout Backend y listado server-side a reutilizar.

## 2. Persistencia y dominio

- [ ] 2.1 Migraciones reversibles para `crm_contacts`, `crm_contact_channels`, `crm_contact_links`, `crm_interactions`, `crm_channel_candidates` con índices y uniques.
- [ ] 2.2 Modelos con relaciones (morphTo/morphMany), casts, scopes (`contactable`, `neverContacted`, `hasChannel`).
- [ ] 2.3 `ContactChannelNormalizer` (email, handles sociales, teléfono AR con libphonenumber, website) + tests unitarios con los casos reales relevados.
- [ ] 2.4 Trait `HasCrmContacts` para Interprete, Festival, Event, PeniaProfile, RadioSignal, Organization, Venue.
- [ ] 2.5 Permisos `crm.view` y `crm.manage` + seeder.

## 3. Importación

- [ ] 3.1 Comando `crm:import --source=interpretes --dry-run` idempotente con reporte.
- [ ] 3.2 Heurística de rol (prensa/productora) y marcado `needs_review` (inválidos, posibles truncados, nombres en campos de red).
- [ ] 3.3 Tests feature: re-ejecución sin duplicados, dedupe entre fichas, no pisar canales manuales.

## 4. Backoffice

- [ ] 4.1 Listado server-side con búsqueda y filtros (segmento, provincia, canal, estado, nunca contactado, último contacto) + export CSV.
- [ ] 4.2 Alta/edición de contacto con canales y vínculos (Select2 para entidades).
- [ ] 4.3 Ficha con canales, vínculos, historial, notas y acciones asistidas (WhatsApp, Instagram/Facebook + copiar, mailto) que registran interacción.
- [ ] 4.4 Bloque "Contactos" en edición de Intérprete y Festival.
- [ ] 4.5 Plantillas de mensaje simples por segmento (config) con variables `{nombre}`, `{ficha_url}`.

## 5. Enriquecimiento

- [ ] 5.1 Comando `crm:extract-candidates` sobre `events` y `festivales`.
- [ ] 5.2 Cola de revisión de candidatos (aprobar/rechazar) con registro de verificador.
- [ ] 5.3 Contrato para job de IA (sólo escribe candidatos con `source_url`), priorizado por `visitas` de intérpretes sin contacto.

## 6. Calidad y release

- [ ] 6.1 Tests con `DatabaseTransactions` para permisos, filtros, acciones, opt-out y no exposición pública.
- [ ] 6.2 Correr `crm:import --dry-run` contra copia local de producción y revisar reporte.
- [ ] 6.3 Importación real en producción y revisión manual de los `needs_review`.

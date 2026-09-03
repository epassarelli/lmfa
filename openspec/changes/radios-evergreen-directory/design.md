## Context

`radios` es una tabla legacy con una ficha pública incompleta y sin programación. El nuevo módulo debe ser útil tanto para una FM local como para una radio web nacional, un stream híbrido o un programa que transmite solamente desde Facebook o YouTube. El usuario necesita descubrir cómo escuchar, desde dónde transmite, cuándo sale al aire y qué programas ofrece, sin confundir una señal con su programación.

## Goals / Non-Goals

**Goals:**

- Crear un directorio evergreen de señales folklóricas verificables y de programas de radio/streaming.
- Modelar una señal con uno o más modos de emisión y uno o más canales de escucha.
- Permitir programación recurrente y programas independientes de una emisora.
- Hacer navegables territorio, frecuencia, plataforma, canales y próximos horarios.
- Aplicar el mismo estándar editorial de fuentes, responsable interno y vigencia de verificación usado en Peñas.

**Non-Goals:**

- Reproducir, retransmitir, grabar, monitorear disponibilidad ni hacer scraping de streams.
- Integrar APIs, credenciales o embeds de YouTube, Facebook, TuneIn, Spotify u otros proveedores.
- Crear grillas automáticas desde texto, importaciones masivas o relaciones inferidas con artistas/eventos.
- Resolver publicidad, membresías, reclamos de propiedad de una radio o gestión de usuarios externos.

## Decisions

### Alcance editorial inclusivo con foco folklórico

El directorio podrá incluir señales generalistas, comunitarias o culturales cuando tengan una propuesta folklórica identificable. La señal deberá declarar su perfil editorial y los programas podrán marcarse como `folklore`; los filtros públicos priorizarán esas coincidencias sin afirmar que toda la programación de una señal es folklórica.

### Plataformas MVP

El catálogo inicial será: `sitio_web`, `stream_directo`, `youtube`, `facebook`, `twitch`, `tunein`, `radio_garden`, `spotify` y `otra_oficial`. El valor `otra_oficial` conserva extensibilidad sin convertir al MVP en una integración con proveedores.

### Señal y canal de escucha son conceptos diferentes

Una **Radio/Señal** representa la identidad editorial estable: nombre, descripción, cobertura, territorio y tipo. Cada **Canal de escucha** representa una forma concreta de acceder: frecuencia AM/FM, sitio web, URL de stream, app/directorio o plataforma social. Una señal puede tener varios canales y modos de emisión (`aire`, `web`, `streaming`, `híbrida`).

Los contratos canónicos serán `RadioSignal`/`radio_signals`, `RadioListeningChannel`/`radio_listening_channels`, `RadioProgram`/`radio_programs` y `RadioProgramSlot`/`radio_program_slots`. Se evita reutilizar `Radio` para no acoplar el MVP al legado.

Alternativa descartada: guardar una sola URL y una frecuencia en la ficha. No permite una radio híbrida ni distinguir enlaces de escucha oficiales de redes sociales informativas.

### Programa como entidad independiente con pertenencia opcional

Un **Programa** puede estar vinculado a una señal o no tener radio asociada. Si es independiente debe declarar como mínimo una plataforma/canal de escucha y fuente verificable. Esto cubre transmisiones nativas de YouTube, Facebook y otros streams sin crear una “radio” artificial.

Alternativa descartada: forzar todos los programas dentro de una radio. Oculta proyectos digitales reales y reduce el valor del directorio.

### Programación mediante franjas semanales explícitas

Cada programa puede tener cero o más franjas con día de semana, hora de inicio, hora de finalización y zona horaria `America/Argentina/Buenos_Aires`. El público verá la próxima salida y la grilla de la radio; un programa sin franjas se mostrará como contenido a demanda o transmisión sin horario publicado.

Alternativa descartada: texto libre para la programación. No permitiría ordenar la grilla, detectar el próximo programa ni mantener horarios editables.

### Publicación y escucha verificables

Toda ficha publicada requiere identidad, descripción, tipo, al menos una fuente y verificación editorial vigente de menos de 90 días. Una señal por aire requiere ubicación/cobertura y frecuencia válida; una señal digital requiere al menos un canal de escucha activo. Un programa independiente requiere su propio canal/plataforma. El botón “Escuchar” abrirá el canal oficial en una nueva pestaña, sin asegurar que esté disponible en tiempo real.

Una señal generalista podrá publicarse si declara una propuesta folklórica editorial; un programa publicado deberá declarar si es de folklore. No se publicarán radios o programas sin esa clasificación verificable dentro de este directorio.

### MVP público centrado en descubrimiento

El directorio permitirá buscar y filtrar señales por texto, provincia, localidad, modo de emisión y plataforma. La ficha mostrará ubicación/cobertura, frecuencia, canales, datos de contacto, mapa si hay coordenadas, programas y próximos horarios. El directorio de programas permitirá filtrar por radio, día y plataforma, e incluir programas independientes.

## Risks / Trade-offs

- [Stream caído o URL cambiada] → fecha de verificación, fuentes, responsable editorial y enlaces externos en vez de reproductor propio.
- [Horarios desactualizados] → franjas editables y exclusión de información no verificada del estado publicado.
- [Complejidad excesiva de plataformas] → catálogo acotado de plataformas y URL genérica controlada en MVP.
- [Duplicados entre radio y programa] → identidad separada, slugs únicos y relación opcional explícita.
- [Datos legacy incompletos] → conservar read-only hasta auditar volumen, equivalencias y autorización de retiro/migración.

## Migration Plan

1. Auditar en modo lectura tabla, rutas, tráfico y media de `radios` legacy.
2. Definir entidades canónicas nuevas para señales, canales, programas y franjas semanales.
3. Implementar dominio, administración, API, frontend, auditor y fixtures sin publicar datos legacy.
4. Cargar en DEV un escenario demo con radios por aire, digitales, híbridas y programas independientes.
5. Validar un lote editorial real antes de habilitar navegación, sitemap o cualquier transición de rutas legacy.

Rollback: despublicar o archivar las fichas canónicas; no borrar datos editoriales. La tabla/ruta legacy sólo se retira tras una decisión específica basada en su inventario real.

## Open Questions

- ¿Una señal nacional/digital sin sede pública puede publicarse con cobertura declarada pero sin coordenadas?

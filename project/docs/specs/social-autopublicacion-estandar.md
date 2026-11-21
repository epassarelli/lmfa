# Estándar de autopublicación en redes (todo tipo de contenido)

Estado: decisión del usuario, 2026-11-20. Obligatorio para cada tipo de contenido que se sume a la autopublicación (hoy Noticias y Eventos; luego Festivales, Discos, Intérpretes, Peñas, Radios, etc.). Implementación de referencia: `social-publication-laravel-executor`.

Toda spec que agregue un tipo de contenido a la autopublicación debe incluir la sección **«Autopublicación en redes»** con cada punto de esta lista resuelto (valor concreto o «no aplica» justificado).

## 1. Programación (por tipo y por red)

- Encendido/apagado independiente por red (Facebook, Instagram y las que se sumen).
- Frecuencia: **diaria o 1 a N días de la semana** (lunes a domingo, días ISO 1–7). Los siete días equivalen a diario.
- **Hora** de ejecución (HH:MM, America/Argentina/Buenos_Aires).
- Una publicación por tipo y red en cada ejecución; una ejecución por día marcado (no se repite aunque el cron corra varias veces).
- Tolerancia de 3 h si el programador se demora; fuera de esa ventana se saltea.
- Si coinciden redes en el mismo minuto: primero la red de origen (Facebook) y después las dependientes (Instagram).
- Valores iniciales propuestos en la spec y apagado por defecto hasta que el responsable lo active desde el panel.

## 2. Cola de candidatos

- Regla de selección explícita: qué estado lo hace elegible (publicado/visible en el portal y, si aplica, vigente), umbral de ID inicial (`SOCIAL_PUBLICATION_<TIPO>_AFTER_ID`) y orden (por ID, por fecha de inicio, etc.).
- Sin envío previo exitoso, incierto o en curso a ese destino (no duplicar).
- Dependencias entre redes (por ejemplo, Instagram solo lo que ya salió en Facebook).
- Contenidos excluidos y por qué (vencidos, sin imagen, sin slug, legacy).

## 3. Pieza y texto

- Plantilla de placa aprobada (ID y versión del catálogo) y formato por red (PNG Facebook, JPEG público Instagram).
- Composición del copy (título, bajada, URL, fechas/lugar si aplica) y límite por red (Instagram 2200 caracteres).
- Casos borde del render: títulos largos, campos vacíos, fechas.

## 4. Funcionalidades del panel (iguales para todos los tipos)

- **Log**: listado con fecha/hora, red, tipo, contenido con link al portal, estado, intentos, ejecutor, link al post; filtros por red, tipo, estado y fechas; detalle de intentos con el error de Meta; conciliación manual de inciertos y reintento de fallos seguros.
- **Últimas corridas**: cada ejecución queda registrada, incluidas las de cola vacía y las omitidas.
- **Envío manual**: elegir tipo y red (una, varias o todas), ver la cola en orden con el siguiente marcado y el último enviado, vista previa de placa y texto, confirmación explícita. Elegir uno que no es el siguiente no saca a los anteriores de la cola.
- **Configuración**: horarios (sección 1), pausa general, ejecutor activo, email de avisos y token por red.

## 5. Seguridad y operación

- Mismo ledger para todos (reserva, hash de contenido, `begin` antes de enviar, resultado único); un resultado incierto nunca se reenvía sin conciliación.
- Pausa general y ejecutor activo respetados también por el envío manual.
- Avisos por email ante fallo, incierto, error de corrida o token inválido.
- Tokens cifrados fuera de git; nunca se muestran ni se loguean.
- Panel solo para `administrador`.

## 6. Validación mínima

Tests Feature con `Http::fake()` (sin llamadas reales a Meta) que cubran: éxito por red, rechazo 4xx (fallo seguro), 5xx/timeout (incierto sin reenvío), dependencia entre redes, cola vacía, envío manual de un ítem no siguiente, días de la semana y pausa. Correr solo esos tests y los del ledger durante el desarrollo (regla de tests acotados de `AGENTS.md`).

## 7. Cómo sumar un tipo en el código

0. Registrar modelo y cola en `SocialPublicationKinds` (registro único; evita condicionales por tipo en ledger, ejecutor y guard). Referencia: Discos (`social-autopublicacion-discos`).
1. Clase de cola `Automatic<Tipo>Publication` con `activeQuery()`, `candidates($provider)`, `facebookPublished()` y `eligibility()` (modelo: `AutomaticEventPublication`).
2. Registrar el tipo en `SocialPublicationSettings::KINDS` y su horario por defecto en `defaults()`.
3. Snapshot y plantilla en `SocialPublicationLedgerService::snapshot()` y el catálogo de placas; ruta pública del contenido.
4. Mapear el tipo en `SocialPublicationExecutor::queue()/preview()` y en el `claim()` automático del ledger.
5. Tests según la sección 6.

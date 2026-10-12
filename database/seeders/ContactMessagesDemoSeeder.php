<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Mensajes de contacto DEMO para explorar la bandeja del backoffice (estados, filtros, búsqueda,
 * paginación, escape de HTML, mensajes largos y spam). Solo local/testing. Idempotente.
 * Todos los nombres empiezan con "[DEMO]" para poder identificarlos y limpiarlos:
 *   DELETE FROM contact_messages WHERE nombre LIKE '[DEMO]%';
 * Datos inventados: no corresponden a personas reales.
 */
class ContactMessagesDemoSeeder extends Seeder
{
    public const PREFIX = '[DEMO] ';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->error('ContactMessagesDemoSeeder solo corre en entornos local/testing.');

            return;
        }

        $account = User::query()->where('email', 'val.sinficha@example.test')->first();
        $long = str_repeat('Quisiera contarles que organizamos todos los años una peña en nuestro pueblo y nos encantaría que figure en la cartelera del portal. ', 10);

        // nombre, email, asunto, mensaje, estado, horas atrás, extras
        $curated = [
            ['Lucía Fernández', 'lucia.fernandez', 'Consulta General', "Hola, quería saber cómo hago para que mi peña aparezca en la cartelera.\n\n¿Tienen algún costo? Gracias!", 'new', 2, []],
            ['Marcos Benítez', 'marcos.benitez', 'Quiero publicitar en el sitio', "Buenas tardes, somos una empresa de instrumentos musicales de Córdoba y nos interesa publicitar en el portal.\nPodrían enviarnos tarifas y formatos disponibles?\n\nSaludos cordiales.", 'new', 5, []],
            ['Graciela Ponce', 'graciela.ponce', 'Reclamo', "En la ficha del festival de mi localidad figura una fecha equivocada.\nLa fecha correcta es el 14 de febrero y no el 21.\nLink: https://mifolkloreargentino.com/festivales-y-fiestas-tradicionales/ejemplo", 'new', 20, []],
            ['Tomás Ledesma', 'tomas.ledesma', 'Sugerencia', 'Estaría bueno poder filtrar la cartelera por provincia y por mes a la vez. ¡Gracias por el trabajo que hacen!', 'new', 30, ['account' => true]],
            ['Rosa Quiroga', 'rosa.quiroga', 'Me gustaría colaborar con material', 'Tengo fotos antiguas de la Fiesta de la Chaya de los años 70 y me gustaría compartirlas con ustedes. ¿A quién puedo escribir?', 'read', 50, []],
            ['Jorge Almada', 'jorge.almada', 'Consulta General', 'Buscaba la letra de una zamba y no la encuentro. ¿Pueden ayudarme a ubicarla?', 'answered', 120, []],
            ['Prueba de HTML', 'prueba.html', 'Otros', "Texto con <b>negritas</b> y <script>alert('xss')</script> para ver que se escapa bien. Acentos: ñandú, águila, corazón ♥ 🎶", 'new', 8, []],
            ['Mensaje muy largo', 'mensaje.largo', 'Otros', trim($long), 'read', 72, []],
            ['Oferta dudosa', 'oferta.dudosa', 'Quiero publicitar en el sitio', 'Compre seguidores http://spam-uno.example http://spam-dos.example http://spam-tres.example http://spam-cuatro.example baratos!!!', 'spam', 15, ['spam' => true]],
            ['Consulta antigua sin responder', 'consulta.antigua', 'Consulta General', 'Escribí hace un mes y no tuve respuesta, ¿siguen recibiendo mensajes?', 'new', 24 * 40, []],
            ['Carlos Sosa', 'carlos.sosa', 'Reclamo', 'Primer mensaje de Carlos.', 'read', 24 * 5, []],
            ['Carlos Sosa', 'carlos.sosa', 'Reclamo', 'Segundo mensaje de Carlos: sigo esperando novedades.', 'new', 24 * 2, []],
        ];

        foreach ($curated as [$name, $email, $subject, $message, $status, $hoursAgo, $extra]) {
            $this->message(self::PREFIX.$name, $email.'@example.test', $subject, $message, $status, $hoursAgo, $extra, $account);
        }

        // Relleno para que haya más de una página (25 por página).
        $subjects = config('contact.subjects');
        foreach (range(1, 22) as $i) {
            $this->message(
                self::PREFIX.'Persona de ejemplo '.$i,
                'persona.'.$i.'@example.test',
                $subjects[$i % count($subjects)],
                "Mensaje de ejemplo número {$i} para probar el listado, los filtros y la paginación de la bandeja.",
                $i % 4 === 0 ? 'answered' : ($i % 3 === 0 ? 'read' : 'new'),
                24 * 3 + $i * 6,
                [],
                null
            );
        }

        $this->command?->info('Mensajes demo listos: '.ContactMessage::where('nombre', 'like', self::PREFIX.'%')->count().' (prefijo "[DEMO]").');
    }

    private function message(string $name, string $email, string $subject, string $text, string $status, int $hoursAgo, array $extra, ?User $account): void
    {
        $sentAt = now()->subHours($hoursAgo);

        ContactMessage::query()->firstOrCreate(
            ['nombre' => $name, 'email' => $email, 'mensaje' => $text],
            [
                'asunto' => $subject,
                'fecha_envio' => $sentAt,
                'status' => $status,
                'spam_reason' => ($extra['spam'] ?? false) ? 'too_many_links' : null,
                'user_id' => ($extra['account'] ?? false) ? $account?->id : null,
                'ip_hash' => hash('sha256', 'demo-'.$email),
                'read_at' => in_array($status, ['read', 'answered'], true) ? $sentAt->copy()->addHours(2) : null,
                'answered_at' => $status === 'answered' ? $sentAt->copy()->addHours(5) : null,
            ]
        );
    }
}

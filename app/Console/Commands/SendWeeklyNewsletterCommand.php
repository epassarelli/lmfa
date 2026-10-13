<?php

namespace App\Console\Commands;

use App\Jobs\SendNewsletterJob;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Interprete;
use App\Models\News;
use App\Models\NewsletterSubscriber;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Resumen semanal. USO MANUAL: ya no está en el scheduler (el envío automático sin revisión se retiró;
 * la Fase 2 del newsletter lo reincorpora para ediciones aprobadas). Solo escribe a suscriptores
 * confirmados y no repite la misma edición a la misma persona.
 */
class SendWeeklyNewsletterCommand extends Command
{
    protected $signature = 'newsletter:send-weekly {--dry-run : Muestra qué se enviaría y a cuántas personas, sin enviar}';

    protected $description = 'Prepara el contenido de la semana pasada y lo envía a los suscriptores confirmados (uso manual).';

    public function handle()
    {
        $startOfWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfWeek = Carbon::now()->subWeek()->endOfWeek();
        $editionKey = 'weekly-'.$startOfWeek->format('o-\WW');

        $noticias = News::publishedVisible()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->latest('published_at')
            ->take(3)
            ->get();
        $discos = Album::with('interprete')->where('estado', 1)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->latest()->take(2)->get();
        $canciones = Cancion::with('interprete')->where('estado', 1)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->latest()->take(3)->get();
        $interpretes = Interprete::where('estado', 1)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->latest()->take(2)->get();

        $hasContent = $noticias->count() || $discos->count() || $canciones->count() || $interpretes->count();
        $recipients = NewsletterSubscriber::deliverable()->count();

        $this->info("Edición {$editionKey}: {$noticias->count()} noticias, {$discos->count()} discos, {$canciones->count()} canciones, {$interpretes->count()} artistas; {$recipients} destinatarios confirmados.");

        if (! $hasContent) {
            $this->info('No hay contenido nuevo de la semana pasada. Abortando envío.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info('Simulación: no se envió nada.');

            return self::SUCCESS;
        }

        $newsData = [
            'noticias' => $noticias,
            'discos' => $discos,
            'canciones' => $canciones,
            'interpretes' => $interpretes,
            'period' => $startOfWeek->format('d/m').' al '.$endOfWeek->format('d/m/Y'),
            'edition_key' => $editionKey,
        ];

        NewsletterSubscriber::deliverable()->chunk(100, function ($subscribers) use ($newsData) {
            foreach ($subscribers as $subscriber) {
                SendNewsletterJob::dispatch($subscriber, $newsData);
            }
        });

        $this->info('Se han despachado los jobs del newsletter.');

        return self::SUCCESS;
    }
}

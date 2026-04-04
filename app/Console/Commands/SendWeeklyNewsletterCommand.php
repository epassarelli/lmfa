<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NewsletterSubscriber;
use App\Models\Noticia;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Interprete;
use App\Jobs\SendNewsletterJob;
use Carbon\Carbon;

class SendWeeklyNewsletterCommand extends Command
{
    protected $signature = 'newsletter:send-weekly';
    protected $description = 'Prepares content from last week and dispatches email jobs to active subscribers.';

    public function handle()
    {
        $startOfWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfWeek = Carbon::now()->subWeek()->endOfWeek();

        // 1. Recolectar Contenido
        $noticias = Noticia::where('estado', 1)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->latest()->take(3)->get();
        $discos = Album::with('interprete')->where('estado', 1)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->latest()->take(2)->get();
        $canciones = Cancion::with('interprete')->where('estado', 1)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->latest()->take(3)->get();
        $interpretes = Interprete::where('estado', 1)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->latest()->take(2)->get();

        $hasContent = $noticias->count() || $discos->count() || $canciones->count() || $interpretes->count();

        if (!$hasContent) {
            $this->info('No hay contenido nuevo de la semana pasada. Abortando envío.');
            return;
        }

        $newsData = [
            'noticias' => $noticias,
            'discos' => $discos,
            'canciones' => $canciones,
            'interpretes' => $interpretes,
            'period' => $startOfWeek->format('d/m') . ' al ' . $endOfWeek->format('d/m/Y')
        ];

        // 2. Dispatch Jobs
        NewsletterSubscriber::active()->chunk(100, function ($subscribers) use ($newsData) {
            foreach ($subscribers as $subscriber) {
                SendNewsletterJob::dispatch($subscriber, $newsData);
            }
        });

        $this->info('Se han despachado los jobs del newsletter.');
    }
}

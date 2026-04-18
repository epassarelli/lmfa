<?php

namespace App\Http\Controllers\Pasarela;

use App\Http\Controllers\Controller;
use App\Models\PublicationAttempt;
use App\Models\PublicationRequest;
use App\Models\PublicationTarget;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\DB;

class DashboardAdminController extends Controller
{
    public function index()
    {
        // Pendientes de moderación
        $pendingModeration = \App\Models\Event::where('editorial_status', 'pending_review')->count()
            + \App\Models\News::where('editorial_status', 'pending_review')->count();

        // Publicaciones del día
        $publishedToday = PublicationAttempt::where('status', 'success')
            ->whereDate('created_at', today())
            ->count();

        // Fallos por canal (últimas 24h)
        $failuresByProvider = PublicationTarget::whereHas(
            'attempts',
            fn($q) => $q->where('status', 'failed')->where('created_at', '>=', now()->subDay())
        )
            ->select('provider', DB::raw('count(*) as total'))
            ->groupBy('provider')
            ->pluck('total', 'provider');

        // Tokens vencidos o por vencer (próximos 7 días)
        $expiredTokens = SocialAccount::where('status', 'active')
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<=', now()->addDays(7))
            ->count();

        // Últimos 10 fallos
        $recentFailures = PublicationAttempt::where('status', 'failed')
            ->orderByDesc('created_at')
            ->limit(10)
            ->with(['target'])
            ->get();

        // Top 5 publicadores (por cantidad de solicitudes)
        $topPublishers = PublicationRequest::select('requested_by', DB::raw('count(*) as total'))
            ->groupBy('requested_by')
            ->orderByDesc('total')
            ->limit(5)
            ->with('requester:id,name,email')
            ->get();

        // Resumen general de targets por status hoy
        $targetsToday = PublicationTarget::whereDate('created_at', today())
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('pasarela.dashboard_admin', compact(
            'pendingModeration',
            'publishedToday',
            'failuresByProvider',
            'expiredTokens',
            'recentFailures',
            'topPublishers',
            'targetsToday'
        ));
    }
}

<?php

namespace App\Http\Controllers\Pasarela;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\News;
use App\Models\PublicationAttempt;
use App\Models\PublicationRequest;
use App\Models\PublicationTarget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardPublicadorController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Solicitudes propias (paginadas en vista, aquí resumen)
        $totalRequests = PublicationRequest::where('requested_by', $userId)->count();

        $requestsByStatus = PublicationRequest::where('requested_by', $userId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Targets: estado por canal
        $targetsByProvider = PublicationTarget::whereHas(
            'request',
            fn($q) => $q->where('requested_by', $userId)
        )
            ->select('provider', 'status', DB::raw('count(*) as total'))
            ->groupBy('provider', 'status')
            ->get()
            ->groupBy('provider');

        // Últimas 5 solicitudes
        $recentRequests = PublicationRequest::where('requested_by', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Próximos eventos del usuario (approved, start_at > now)
        $upcomingEvents = Event::whereHas(
            'organization.members',
            fn($q) => $q->where('user_id', $userId)->where('status', 'active')
        )
            ->where('editorial_status', 'approved')
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        // Fallos recientes
        $recentFailures = PublicationAttempt::whereHas(
            'target.request',
            fn($q) => $q->where('requested_by', $userId)
        )
            ->where('status', 'failed')
            ->orderByDesc('created_at')
            ->limit(5)
            ->with('target')
            ->get();

        return view('pasarela.dashboard_publicador', compact(
            'totalRequests',
            'requestsByStatus',
            'targetsByProvider',
            'recentRequests',
            'upcomingEvents',
            'recentFailures'
        ));
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PublicationRequest;
use App\Models\PublicationTarget;
use App\Models\PublicationAttempt;
use App\Models\Event;
use App\Models\News;
use App\Models\SocialAccount;
use App\Models\AuditLog;

class AdminDashboardController extends Controller
{
    /**
     * PC-11-HU-01: Admin dashboard — system-wide metrics, errors, moderation queue.
     */
    public function index()
    {
        // Moderation queue count
        $pendingModeration = Event::where('editorial_status', 'pending_review')->count()
            + News::where('editorial_status', 'pending_review')->count();

        // Today's published content
        $publishedToday = Event::where('editorial_status', 'published')
                ->whereDate('published_at', today())->count()
            + News::where('editorial_status', 'published')
                ->whereDate('published_at', today())->count();

        // Failed targets (last 7 days)
        $failedTargets = PublicationTarget::where('status', 'failed')
            ->where('updated_at', '>=', now()->subDays(7))
            ->with(['request', 'socialAccount'])
            ->orderBy('updated_at', 'desc')
            ->take(20)
            ->get();

        // Failed counts by provider
        $failsByProvider = PublicationTarget::where('status', 'failed')
            ->where('updated_at', '>=', now()->subDays(7))
            ->selectRaw('provider, count(*) as total')
            ->groupBy('provider')
            ->pluck('total', 'provider');

        // Expiring social accounts (<=7 days)
        $expiringTokens = SocialAccount::where('status', 'active')
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<=', now()->addDays(7))
            ->get();

        // Top publishers (by publication requests, last 30 days)
        $topPublishers = PublicationRequest::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('requested_by, count(*) as total')
            ->groupBy('requested_by')
            ->orderByDesc('total')
            ->with('requester')
            ->take(5)
            ->get();

        // Recent audit logs
        $recentAuditLogs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        // Stats cards
        $stats = [
            'pending_moderation' => $pendingModeration,
            'published_today'    => $publishedToday,
            'failed_last_7d'     => $failedTargets->count(),
            'expiring_tokens'    => $expiringTokens->count(),
        ];

        return view('backend.dashboards.admin', compact(
            'stats',
            'failedTargets',
            'failsByProvider',
            'expiringTokens',
            'topPublishers',
            'recentAuditLogs'
        ));
    }
}

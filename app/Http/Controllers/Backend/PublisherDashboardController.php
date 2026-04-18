<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PublicationRequest;
use App\Models\PublicationTarget;
use App\Models\PublicationAttempt;
use App\Models\Event;
use App\Models\News;
use App\Models\SocialAccount;

class PublisherDashboardController extends Controller
{
    /**
     * PC-10-HU-01: Publisher dashboard — shows user's own content status.
     */
    public function index()
    {
        $user = auth()->user();

        // User's publication requests with targets
        $requests = PublicationRequest::with(['targets'])
            ->where('requested_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        // User's own content summary
        $myEvents = Event::where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get(['id', 'title', 'editorial_status', 'start_at', 'slug']);

        $myNews = News::where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get(['id', 'title', 'editorial_status', 'created_at', 'slug']);

        // Connected social accounts
        $socialAccounts = SocialAccount::where('owner_type', get_class($user))
            ->where('owner_id', $user->id)
            ->get();

        // Upcoming events (approved, future)
        $upcomingEvents = Event::where('created_by', $user->id)
            ->where('editorial_status', 'approved')
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->take(5)
            ->get(['id', 'title', 'start_at', 'slug']);

        return view('backend.dashboards.publisher', compact(
            'requests',
            'myEvents',
            'myNews',
            'socialAccounts',
            'upcomingEvents'
        ));
    }
}

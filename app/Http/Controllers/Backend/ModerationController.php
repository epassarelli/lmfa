<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\News;
use App\Models\ModerationReview;
use App\Models\AuditLog;
use App\Models\UserNotification;
use App\Services\Publication\PublicationService;

class ModerationController extends Controller
{
    /**
     * Display a listing of pending content.
     */
    public function index()
    {
        $events = Event::with(['organization', 'creator'])
            ->where('editorial_status', 'pending_review')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $item->content_type = 'Event';
                $item->edit_route = route('backend.shows.edit', $item->id);
                return $item;
            });

        $news = News::with(['organization', 'creator'])
            ->where('editorial_status', 'pending_review')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $item->content_type = 'News';
                $item->edit_route = route('backend.noticias.edit', $item->id);
                return $item;
            });

        $pendingContent = $events->concat($news)->sortByDesc('created_at');

        return view('backend.moderation.index', compact('pendingContent'));
    }

    public function action(Request $request)
    {
        $request->validate([
            'content_id'   => 'required|integer',
            'content_type' => 'required|string|in:Event,News',
            'action'       => 'required|string|in:approve,reject',
            'comments'     => 'nullable|string',
        ]);

        $modelClass = $request->content_type === 'Event' ? Event::class : News::class;
        $content    = $modelClass::findOrFail($request->content_id);
        $oldStatus  = $content->editorial_status;
        $newStatus  = $request->action === 'approve' ? 'approved' : 'rejected';

        $content->editorial_status = $newStatus;

        if ($newStatus === 'approved') {
            $content->approved_by = auth()->id();
            $content->approved_at = now();
            if ($content->getTable() === 'news') {
                $content->estado = 1;
            }
        }

        $content->save();

        // Moderation review log
        ModerationReview::create([
            'content_type'     => $modelClass,
            'content_id'       => $content->id,
            'reviewer_user_id' => auth()->id(),
            'action'           => $request->action,
            'comments'         => $request->comments,
        ]);

        // Audit log
        AuditLog::log(
            $modelClass,
            $content->id,
            $request->action,
            ['editorial_status' => $oldStatus],
            ['editorial_status' => $newStatus],
        );

        // Notify the content creator
        if ($content->created_by) {
            $notifTitle = $newStatus === 'approved'
                ? '✅ Tu contenido fue aprobado'
                : '❌ Tu contenido fue rechazado';

            $notifBody = "El contenido \"" . ($content->title ?? 'sin título') . "\" fue {$newStatus}.";
            if ($request->comments) {
                $notifBody .= "\n\nComentarios: " . $request->comments;
            }

            UserNotification::notify($content->created_by, "moderation.{$newStatus}", $notifTitle, $notifBody);
        }

        // If approved, trigger publication via PublicationService
        if ($newStatus === 'approved') {
            try {
                $mode = $content->publication_mode ?? 'portal_only';
                $service = app(PublicationService::class);
                $service->createRequest($modelClass, $content->id, [
                    'mode'                => $mode,
                    'wants_portal_publish'=> true,
                    'wants_portal_social' => str_contains($mode, 'portal_social'),
                    'wants_own_social'    => str_contains($mode, 'own_social'),
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("PublicationService error on approval: " . $e->getMessage());
            }
        }

        $label = $newStatus === 'approved' ? 'aprobado' : 'rechazado';
        return redirect()->route('backend.moderation.index')
            ->with('success', "Contenido {$label} correctamente.");
    }
}

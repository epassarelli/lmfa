<?php

namespace App\Http\Controllers\Pasarela;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Support\BackendListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        [$sort, $direction] = BackendListing::resolveSort(
            $request,
            ['created_at', 'title', 'is_read'],
            'created_at'
        );

        $notifications = UserNotification::where('user_id', Auth::id())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('body', 'like', '%'.$search.'%');
                });
            })
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $unreadCount = UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return view('pasarela.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(UserNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        return back()->with('success', 'Notificación marcada como leída.');
    }

    public function markAllRead()
    {
        UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'Todas las notificaciones marcadas como leídas.');
    }

    public function unreadCount()
    {
        $count = UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}

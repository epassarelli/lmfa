<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewsletterSubscriber;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::query();

        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscribers = $query->latest()->paginate(15);

        return view('backend.newsletter.index', compact('subscribers'));
    }

    public function toggleStatus(NewsletterSubscriber $subscriber)
    {
        $subscriber->update([
            'status' => $subscriber->status === 'active' ? 'unsubscribed' : 'active',
            'unsubscribed_at' => $subscriber->status === 'active' ? now() : null
        ]);

        return back()->with('success', 'Estado del suscriptor actualizado correctamente.');
    }
}

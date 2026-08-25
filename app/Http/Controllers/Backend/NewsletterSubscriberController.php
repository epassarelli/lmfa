<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewsletterSubscriber;
use App\Support\BackendListing;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request)
    {
        [$sort, $direction] = BackendListing::resolveSort(
            $request,
            ['email', 'status', 'created_at', 'unsubscribed_at'],
            'created_at'
        );

        $query = NewsletterSubscriber::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();

            $query->where(function ($inner) use ($search) {
                $inner->where('email', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscribers = $query
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

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

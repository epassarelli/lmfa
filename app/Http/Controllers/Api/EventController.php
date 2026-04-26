<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query()->where('editorial_status', 'published');

        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        return response()->json(
            $query->orderBy('start_at')->paginate(15)
        );
    }

    public function show(Event $event)
    {
        return response()->json($event);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'nullable|string',
            'start_at'   => 'required|date',
            'end_at'     => 'nullable|date|after:start_at',
            'event_type' => 'nullable|string|max:50',
            'city'       => 'nullable|string|max:100',
            'province_id'=> 'nullable|integer|exists:provincias,id',
            'is_free'    => 'boolean',
            'ticket_url' => 'nullable|url',
        ]);

        $validated['slug']             = Str::slug($validated['title']) . '-' . now()->timestamp;
        $validated['editorial_status'] = 'draft';
        $validated['created_by']       = $request->user()->id;

        return response()->json(Event::create($validated), 201);
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'      => 'sometimes|string|max:255',
            'body'       => 'nullable|string',
            'start_at'   => 'sometimes|date',
            'end_at'     => 'nullable|date',
            'event_type' => 'nullable|string|max:50',
            'city'       => 'nullable|string|max:100',
            'is_free'    => 'boolean',
            'ticket_url' => 'nullable|url',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreEventRequest;
use App\Http\Requests\Api\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(protected EventService $eventService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Event::class);

        $query = Event::query()->with(['creator', 'provincia', 'interpretes']);

        if ($request->filled('editorial_status')) {
            $query->where('editorial_status', $request->string('editorial_status'));
        } else {
            $query->where('editorial_status', 'published');
        }

        if ($request->filled('province_id')) {
            $query->where('province_id', $request->integer('province_id'));
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->string('event_type'));
        }

        if ($request->filled('published_from')) {
            $query->whereDate('published_at', '>=', $request->date('published_from'));
        }

        if ($request->filled('published_to')) {
            $query->whereDate('published_at', '<=', $request->date('published_to'));
        }

        return response()->json(
            $query->orderBy('start_at')->paginate((int) $request->input('per_page', 15))
        );
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);

        return response()->json($event->load(['creator', 'provincia', 'interpretes']));
    }

    public function store(StoreEventRequest $request)
    {
        $this->authorize('create', Event::class);

        $event = $this->eventService->createEvent(
            $request->validated(),
            $request->file('featured_image')
        );

        return response()->json($event, 201);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $event = $this->eventService->updateEvent(
            $event,
            $request->validated(),
            $request->file('featured_image')
        );

        return response()->json($event);
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $event->delete();

        return response()->json(null, 204);
    }
}

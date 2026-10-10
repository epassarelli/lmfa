<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventProgram;
use App\Services\EventProgramService;
use App\Support\ApiImageInput;
use Illuminate\Http\Request;

class EventProgramController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Event::class);
        $request->validate(['featured_image' => 'nullable|image|max:5120', 'featured_image_url' => 'nullable|url|max:2048']);
        $payload = $request->all();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');
        $program = app(EventProgramService::class)->save($payload, $image);

        return response()->json($program->load('events.interpretes'), 201);
    }

    public function update(Request $request, EventProgram $event_program)
    {
        abort_unless(auth()->user()->hasRole('administrador'), 403);
        $request->validate(['featured_image' => 'nullable|image|max:5120', 'featured_image_url' => 'nullable|url|max:2048']);
        $payload = array_merge($event_program->only(['title', 'body', 'excerpt', 'venue_id', 'ticket_url', 'price_text', 'is_free', 'editorial_status', 'published_at']), $request->all());
        $image = ApiImageInput::extract($request, $payload, 'featured_image');
        $program = app(EventProgramService::class)->save($payload, $image, $event_program);

        return response()->json($program->load('events.interpretes'));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RadioProgramRequest;
use App\Models\RadioProgram;
use App\Services\RadioDirectoryService;
use Illuminate\Http\Request;

class RadioProgramController extends Controller
{
    public function __construct(private readonly RadioDirectoryService $radios)
    {
    }

    public function index(Request $request)
    {
        $query = RadioProgram::query()->with(['signal', 'slots'])
            ->when($request->boolean('published_only', true), fn ($builder) => $builder->publiclyVisible())
            ->when($request->filled('signal_id'), fn ($builder) => $builder->where('radio_signal_id', $request->integer('signal_id')))
            ->when($request->filled('platform'), fn ($builder) => $builder->where('platform', $request->input('platform')))
            ->when($request->filled('q'), fn ($builder) => $builder->where('title', 'like', '%'.$request->input('q').'%'));

        return response()->json($query->orderBy('title')->paginate(15));
    }

    public function show(RadioProgram $radioProgram)
    {
        abort_unless($radioProgram->publiclyVisible()->whereKey($radioProgram)->exists(), 404);

        return response()->json($radioProgram->load(['signal.listeningChannels', 'slots' => fn ($query) => $query->where('is_active', true)->orderBy('weekday')->orderBy('starts_at')]));
    }

    public function store(RadioProgramRequest $request)
    {
        $this->authorize('create', RadioProgram::class);
        $program = $this->radios->saveProgram(new RadioProgram(), $request->validated() + ['created_by' => $request->user()->id]);

        return response()->json($program->load('slots'), 201);
    }

    public function update(RadioProgramRequest $request, RadioProgram $radioProgram)
    {
        $this->authorize('update', $radioProgram);
        $this->radios->saveProgram($radioProgram, $request->validated());

        return response()->json($radioProgram->fresh()->load('slots'));
    }

    public function destroy(RadioProgram $radioProgram)
    {
        $this->authorize('delete', $radioProgram);
        $this->radios->saveProgram($radioProgram, ['editorial_status' => 'archived']);

        return response()->json(null, 204);
    }
}

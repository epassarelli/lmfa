<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RadioSignalRequest;
use App\Models\RadioSignal;
use App\Services\RadioDirectoryService;
use Illuminate\Http\Request;

class RadioSignalController extends Controller
{
    public function __construct(private readonly RadioDirectoryService $radios)
    {
    }

    public function index(Request $request)
    {
        $query = RadioSignal::query()->with(['provincia', 'listeningChannels'])
            ->when($request->boolean('published_only', true), fn ($builder) => $builder->publiclyVisible())
            ->when($request->filled('province_id'), fn ($builder) => $builder->where('province_id', $request->integer('province_id')))
            ->when($request->filled('mode'), fn ($builder) => $builder->whereJsonContains('transmission_modes', $request->input('mode')))
            ->when($request->filled('platform'), fn ($builder) => $builder->whereHas('listeningChannels', fn ($channels) => $channels->where('platform', $request->input('platform'))->where('is_active', true)))
            ->when($request->filled('q'), function ($builder) use ($request) {
                $term = $request->string('q')->trim()->toString();
                $builder->where(fn ($nested) => $nested->where('title', 'like', "%{$term}%")->orWhere('city', 'like', "%{$term}%"));
            });

        return response()->json($query->orderBy('title')->paginate(15));
    }

    public function show(RadioSignal $radioSignal)
    {
        abort_unless($radioSignal->publiclyVisible()->whereKey($radioSignal)->exists(), 404);

        return response()->json($radioSignal->load(['provincia', 'locality', 'listeningChannels' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'), 'programs' => fn ($query) => $query->publiclyVisible()->with('slots')]));
    }

    public function store(RadioSignalRequest $request)
    {
        $this->authorize('create', RadioSignal::class);
        $signal = $this->radios->saveSignal(new RadioSignal(), $request->validated() + ['created_by' => $request->user()->id]);

        return response()->json($signal->load('listeningChannels'), 201);
    }

    public function update(RadioSignalRequest $request, RadioSignal $radioSignal)
    {
        $this->authorize('update', $radioSignal);
        $this->radios->saveSignal($radioSignal, $request->validated());

        return response()->json($radioSignal->fresh()->load('listeningChannels'));
    }

    public function destroy(RadioSignal $radioSignal)
    {
        $this->authorize('delete', $radioSignal);
        $this->radios->saveSignal($radioSignal, ['editorial_status' => 'archived']);

        return response()->json(null, 204);
    }
}

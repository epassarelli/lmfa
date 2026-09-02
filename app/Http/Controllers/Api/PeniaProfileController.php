<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePeniaProfileRequest;
use App\Http\Requests\Api\UpdatePeniaProfileRequest;
use App\Models\PeniaProfile;
use App\Services\PeniaProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeniaProfileController extends Controller
{
    public function __construct(private readonly PeniaProfileService $profiles)
    {
    }

    public function index(Request $request)
    {
        $query = PeniaProfile::query()->with(['provincia', 'locality']);

        if ($request->boolean('published_only', true)) {
            $query->publiclyVisible();
        }

        $query
            ->when($request->filled('province_id'), fn ($builder) => $builder->where('province_id', $request->integer('province_id')))
            ->when($request->filled('locality_id'), fn ($builder) => $builder->where('locality_id', $request->integer('locality_id')))
            ->when($request->filled('venue_type'), fn ($builder) => $builder->where('venue_type', $request->string('venue_type')))
            ->when($request->filled('q'), function ($builder) use ($request) {
                $term = $request->string('q')->trim()->toString();
                $builder->where(function ($nested) use ($term) {
                    $nested->where('title', 'like', "%{$term}%")
                        ->orWhere('city', 'like', "%{$term}%")
                        ->orWhere('excerpt', 'like', "%{$term}%");
                });
            });

        return response()->json($query->orderBy('title')->paginate(15));
    }

    public function show(PeniaProfile $peniaProfile)
    {
        return response()->json($peniaProfile->load(['provincia', 'locality', 'events.interpretes']));
    }

    public function store(StorePeniaProfileRequest $request)
    {
        $this->authorize('create', PeniaProfile::class);

        $profile = DB::transaction(function () use ($request) {
            $payload = $request->validated();
            $payload['created_by'] = $request->user()->id;

            return $this->profiles->save(new PeniaProfile(), $payload);
        });

        return response()->json($profile->load(['provincia', 'locality', 'events']), 201);
    }

    public function update(UpdatePeniaProfileRequest $request, PeniaProfile $peniaProfile)
    {
        $this->authorize('update', $peniaProfile);

        DB::transaction(fn () => $this->profiles->save($peniaProfile, $request->validated()));

        return response()->json($peniaProfile->fresh()->load(['provincia', 'locality', 'events']));
    }

    public function destroy(PeniaProfile $peniaProfile)
    {
        $this->authorize('delete', $peniaProfile);
        $peniaProfile->update(['editorial_status' => 'archived']);

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeniaProfileRequest;
use App\Models\Event;
use App\Models\Locality;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use App\Services\PeniaProfileService;
use App\Support\CanonicalUrl;
use App\Support\SeoMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeniaProfileController extends Controller
{
    public function __construct(private readonly PeniaProfileService $profiles)
    {
        $this->middleware('auth');
        $this->authorizeResource(PeniaProfile::class, 'penia_profile');
    }

    public function index(Request $request)
    {
        $profiles = PeniaProfile::query()->with(['provincia', 'locality', 'verifier'])->withCount('events')
            ->when(! Auth::user()->hasRole('administrador'), fn ($query) => $query->where('created_by', Auth::id()))
            ->when($request->filled('status'), fn ($query) => $query->where('editorial_status', $request->input('status')))
            ->when($request->filled('verification'), fn ($query) => $query->where('verification_status', $request->input('verification')))
            ->when($request->filled('province_id'), fn ($query) => $query->where('province_id', $request->integer('province_id')))
            ->when($request->filled('venue_type'), fn ($query) => $query->where('venue_type', $request->input('venue_type')))
            ->when($request->input('quality') === 'missing_verification', function ($query) {
                $query->where(function ($missing) {
                    $missing->where('verification_status', '!=', 'verified')
                        ->orWhereNull('last_verified_at')
                        ->orWhereNull('verified_by_user_id')
                        ->orWhereNull('verification_method');
                });
            })
            ->when($request->input('quality') === 'missing_contact', function ($query) {
                $query->whereNull('phone')->whereNull('email')->whereNull('website');
            })
            ->when($request->input('quality') === 'missing_sources', function ($query) {
                $query->where(function ($missing) {
                    $missing->whereNull('source_urls')->orWhereJsonLength('source_urls', 0);
                });
            })
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->input('search').'%'))
            ->orderByDesc('updated_at')->paginate(25)->withQueryString();

        $provincias = Provincia::orderBy('nombre')->get(['id', 'nombre']);

        return view('backend.penia-profiles.index', compact('profiles', 'provincias'));
    }

    public function create()
    {
        return view('backend.penia-profiles.create', $this->formData(new PeniaProfile()));
    }

    public function store(PeniaProfileRequest $request)
    {
        DB::transaction(function () use ($request) {
            $this->profiles->save(new PeniaProfile(), $request->validated() + ['created_by' => Auth::id()]);
        });

        return redirect()->route('backend.penia-profiles.index')->with('success', 'Peña creada.');
    }

    public function edit(PeniaProfile $peniaProfile)
    {
        return view('backend.penia-profiles.edit', $this->formData($peniaProfile->load('events')));
    }

    public function show(PeniaProfile $peniaProfile)
    {
        return redirect()->route('backend.penia-profiles.edit', $peniaProfile);
    }

    public function update(PeniaProfileRequest $request, PeniaProfile $peniaProfile)
    {
        DB::transaction(fn () => $this->profiles->save($peniaProfile, $request->validated()));

        return redirect()->route('backend.penia-profiles.index')->with('success', 'Peña actualizada.');
    }

    public function destroy(PeniaProfile $peniaProfile)
    {
        $this->profiles->archive($peniaProfile);

        return back()->with('success', 'Peña archivada.');
    }

    public function preview(PeniaProfile $peniaProfile)
    {
        $this->authorize('view', $peniaProfile);

        $penia = $peniaProfile->load([
            'provincia',
            'locality',
            'images',
            'events' => fn ($query) => $query->publiclyVisible()
                ->where('start_at', '>=', now()->startOfDay())
                ->with(['interpretes.images', 'images', 'provincia'])
                ->orderBy('start_at'),
        ]);
        $canonical = CanonicalUrl::normalize($penia->getUrl());
        $metaDescription = $penia->meta_description ?: ($penia->excerpt ?: SeoMetadata::clean($penia->body));
        $sameProvince = PeniaProfile::publiclyVisible()
            ->where('province_id', $penia->province_id)
            ->whereKeyNot($penia->id)
            ->with(['provincia', 'images'])
            ->orderBy('title')
            ->limit(3)
            ->get();

        return view('frontend.penia-profiles.show', compact('penia', 'canonical', 'metaDescription', 'sameProvince') + [
            'metaTitle' => $penia->seo_title ?: $penia->title,
            'metaRobots' => 'noindex,nofollow',
            'isPreview' => true,
            'breadcrumbs' => [
                ['label' => 'Administración', 'url' => route('backend.penia-profiles.index')],
                ['label' => 'Vista previa'],
                ['label' => $penia->title],
            ],
        ]);
    }

    public function publish(PeniaProfile $peniaProfile)
    {
        $this->authorize('update', $peniaProfile);
        $this->profiles->publish($peniaProfile);

        return back()->with('success', 'Peña publicada.');
    }

    public function unpublish(PeniaProfile $peniaProfile)
    {
        $this->authorize('update', $peniaProfile);
        $this->profiles->unpublish($peniaProfile);

        return back()->with('success', 'Peña despublicada y enviada a borrador.');
    }

    private function formData(PeniaProfile $profile): array
    {
        return ['profile' => $profile, 'provincias' => Provincia::orderBy('nombre')->get(), 'localities' => Locality::orderBy('name')->get(), 'events' => Event::publiclyVisible()->orderByDesc('start_at')->limit(200)->get(['id', 'title']), 'users' => User::orderBy('name')->limit(200)->get(['id', 'name'])];
    }
}

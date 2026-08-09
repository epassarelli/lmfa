<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\FestivalRequest;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\Locality;
use App\Models\Mes;
use App\Models\News;
use App\Models\Provincia;
use App\Services\ImageUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class FestivalController extends Controller
{
    public function __construct(protected ImageUploadService $imageService)
    {
        $this->middleware('auth');
        $this->authorizeResource(Festival::class, 'festival');
    }

    public function index()
    {
        $user = Auth::user();

        $festivales = Festival::query()
            ->when($user->hasRole(['colaborador', 'prensa']), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with([
                'user:id,name',
                'provincia:id,nombre',
                'mes:id,nombre',
                'locality:id,province_id,name',
            ])
            ->withCount(['noticias', 'events', 'interpretes', 'knowledgeArticles'])
            ->orderByDesc('published_at')
            ->orderBy('title')
            ->paginate(25)
            ->withQueryString();

        return view('backend.festivales.index', compact('festivales'));
    }

    public function create()
    {
        $festival = new Festival([
            'status' => Auth::user()->hasRole('administrador') ? 'published' : 'draft',
            'published_at' => now(),
        ]);

        return view('backend.festivales.create', $this->formData($festival));
    }

    public function store(FestivalRequest $request)
    {
        $festival = DB::transaction(function () use ($request) {
            $payload = $this->normalizedPayload($request->validated());

            $festival = new Festival($payload);
            $festival->user_id = Auth::id();
            $festival->save();

            $this->syncRelations($festival, $payload);
            $this->processImage($festival, $request->file('foto'), false);

            return $festival;
        });

        if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
            $this->sendNotification($festival);
        }

        Alert::success('Festival creado', 'El festival ha sido creado con exito.');

        return redirect()->route('backend.festivales.index');
    }

    public function edit(Festival $festival)
    {
        return view('backend.festivales.edit', $this->formData(
            $festival->loadMissing(['noticias', 'events', 'interpretes', 'knowledgeArticles', 'locality'])
        ));
    }

    public function update(FestivalRequest $request, Festival $festival)
    {
        DB::transaction(function () use ($request, $festival) {
            $payload = $this->normalizedPayload($request->validated(), $festival);

            $festival->fill($payload);
            $festival->save();

            $this->syncRelations($festival, $payload);
            $this->processImage($festival, $request->file('foto'), true);
        });

        Alert::success('Festival actualizado', 'El festival ha sido actualizado con exito.');

        return redirect()->route('backend.festivales.index');
    }

    public function destroy(Festival $festival)
    {
        $this->authorize('delete', $festival);
        $festival->delete();

        Alert::success('Festival eliminado', 'El festival ha sido eliminado con exito.');

        return redirect()->route('backend.festivales.index');
    }

    private function formData(Festival $festival): array
    {
        $selectedProvinceId = old('province_id', $festival->province_id);

        return [
            'festival' => $festival,
            'provincias' => Provincia::orderBy('nombre')->get(),
            'meses' => Mes::orderBy('id')->get(),
            'localities' => Locality::query()
                ->when($selectedProvinceId, fn ($query) => $query->where('province_id', $selectedProvinceId))
                ->orderBy('name')
                ->get(),
            'relatedNews' => News::query()->publishedVisible()->latest('published_at')->limit(200)->get(['id', 'title']),
            'relatedEvents' => Event::query()->publishedVisible()->orderByDesc('start_at')->limit(200)->get(['id', 'title']),
            'relatedArtists' => Interprete::active()->limit(200)->get(['id', 'interprete']),
            'relatedKnowledgeArticles' => KnowledgeArticle::query()->visible()->latest('published_at')->limit(200)->get(['id', 'title']),
        ];
    }

    private function normalizedPayload(array $data, ?Festival $festival = null): array
    {
        $title = $data['title'] ?? $festival?->title;
        $body = $data['body'] ?? $festival?->body;
        $status = $data['status'] ?? $festival?->status ?? 'draft';
        $publishedAt = $data['published_at'] ?? $festival?->published_at;

        return array_merge($data, [
            'title' => $title,
            'slug' => $data['slug'] ?? Str::slug((string) $title),
            'body' => $body,
            'status' => $status,
            'published_at' => $publishedAt,
        ]);
    }

    private function processImage(Festival $festival, mixed $image, bool $replace): void
    {
        if (! $image) {
            return;
        }

        $media = $this->imageService->process(
            $image,
            $festival,
            'festival',
            'festivales',
            $replace
        );

        $festival->forceFill([
            'featured_image_id' => $media->id,
            'featured_image_path' => $media->path,
        ])->save();
    }

    private function syncRelations(Festival $festival, array $data): void
    {
        $festival->noticias()->sync($data['news_ids'] ?? []);
        $festival->events()->sync($data['event_ids'] ?? []);
        $festival->interpretes()->sync($data['interprete_ids'] ?? []);
        $festival->knowledgeArticles()->sync($data['knowledge_article_ids'] ?? []);
    }

    private function sendNotification(Festival $festival): void
    {
        try {
            $details = [
                'title' => 'Se ha agregado un/a Festival en el portal',
                'titulo' => $festival->title,
                'user' => $festival->user?->name ?? 'Invitado',
            ];

            Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\FestivalCreated($details));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo de Festival: '.$e->getMessage());
        }
    }
}

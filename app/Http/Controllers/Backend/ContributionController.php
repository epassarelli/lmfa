<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\News;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NewsService;
use App\Support\BackendListing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ContributionController extends Controller
{
    /**
     * Tipos de contenido que el moderador puede aprobar desde este flujo.
     * Se restringe explicitamente para evitar instanciacion arbitraria.
     */
    private const APPROVABLE_MODELS = [
        \App\Models\Interprete::class,
        \App\Models\News::class,
        \App\Models\Cancion::class,
        \App\Models\Festival::class,
        \App\Models\Event::class,
    ];

    public function __construct(protected NewsService $newsService)
    {
        $this->middleware(['auth', 'role:administrador']);
    }

    public function index(Request $request): View
    {
        $allowedSorts = ['created_at', 'status', 'contributable_type'];
        [$sort, $direction] = BackendListing::resolveSort($request, $allowedSorts, 'created_at');
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $allowedStatuses = ['pending', 'approved', 'rejected', 'auto-applied'];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $contributions = Contribution::query()
            ->with('user')
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($search !== '', function ($query) use ($search) {
                $normalizedSearch = '%'.mb_strtolower($search).'%';

                $query->where(function ($contributionQuery) use ($search, $normalizedSearch) {
                    $contributionQuery
                        ->where('contributable_type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereRaw(
                            "LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.nombre')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.titulo')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.interprete')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.cancion')), JSON_UNQUOTE(JSON_EXTRACT(payload, '$.title')), '')) LIKE ?",
                            [$normalizedSearch]
                        );
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        return view('backend.contributions.index', compact('contributions'));
    }

    public function show(int $id): View
    {
        $contribution = Contribution::with('user')->findOrFail($id);
        $modelClass = $this->resolveContributionModel($contribution);
        $original = $contribution->contributable_id ? $modelClass::find($contribution->contributable_id) : null;

        return view('backend.contributions.show', compact('contribution', 'original'));
    }

    public function approve(int $id): RedirectResponse
    {
        $contribution = Contribution::findOrFail($id);

        if ($contribution->status !== 'pending') {
            return back()->with('error', 'Esta contribucion ya fue procesada.');
        }

        try {
            DB::transaction(function () use ($contribution) {
                $modelClass = $this->resolveContributionModel($contribution);
                $isNew = $contribution->contributable_id === null;
                $payload = $contribution->payload ?? [];

                if ($modelClass === News::class) {
                    $this->approveNewsContribution($contribution, $payload, $isNew);
                } else {
                    $this->approveLegacyContribution($contribution, $modelClass, $payload, $isNew);
                }

                $this->markContributionApproved($contribution, $isNew);
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'No se pudo aprobar la contribucion. Revisa el payload y vuelve a intentar.');
        }

        return redirect()
            ->route('backend.contributions.admin.index')
            ->with('success', 'Contribucion aprobada y publicada.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $contribution = Contribution::findOrFail($id);

        if ($contribution->status !== 'pending') {
            return back()->with('error', 'Esta contribucion ya fue procesada.');
        }

        $comment = $request->input('comment');

        $contribution->update([
            'status' => 'rejected',
            'moderator_comment' => $comment,
        ]);

        UserNotification::notify(
            $contribution->user_id,
            'contribution.rejected',
            'Tu contribucion fue rechazada',
            $comment ? "Motivo: {$comment}" : 'Tu aporte no pudo ser publicado en esta oportunidad.'
        );

        return redirect()
            ->route('backend.contributions.admin.index')
            ->with('success', 'Contribucion rechazada.');
    }

    private function resolveContributionModel(Contribution $contribution): string
    {
        $modelClass = $contribution->contributable_type;

        if (! is_string($modelClass) || ! in_array($modelClass, self::APPROVABLE_MODELS, true) || ! class_exists($modelClass)) {
            abort(422, 'El tipo de contribucion no esta soportado por el moderador.');
        }

        return $modelClass;
    }

    private function approveNewsContribution(Contribution $contribution, array $payload, bool $isNew): void
    {
        $payload['created_by'] = $contribution->user_id;
        $payload['approved_by'] = auth()->id();
        $payload['editorial_status'] = 'published';

        if ($isNew) {
            $model = $this->newsService->createNews($payload, $payload['foto'] ?? null);
            $contribution->contributable_id = $model->id;

            return;
        }

        $model = News::findOrFail($contribution->contributable_id);
        $this->newsService->updateNews($model, $payload, $payload['foto'] ?? null);
    }

    private function approveLegacyContribution(Contribution $contribution, string $modelClass, array $payload, bool $isNew): void
    {
        if (! $isNew) {
            $model = $modelClass::findOrFail($contribution->contributable_id);
            $model->update($payload);

            return;
        }

        $model = new $modelClass($payload);
        $model->user_id = $contribution->user_id;

        if (isset($model->estado)) {
            $model->estado = 1;
        }

        if (empty($model->slug)) {
            $model->slug = $this->generateUniqueSlug($modelClass, $payload);
        }

        $model->save();
        $contribution->contributable_id = $model->id;
    }

    private function markContributionApproved(Contribution $contribution, bool $isNew): void
    {
        $contribution->status = 'approved';
        $contribution->save();

        $user = User::findOrFail($contribution->user_id);
        $pointsAwarded = $isNew ? 50 : 20;
        $user->increment('points', $pointsAwarded);

        if ($user->points > 1000) {
            $user->update(['rank' => 'Folclorista de Oro']);
        } elseif ($user->points > 500) {
            $user->update(['rank' => 'Folclorista de Plata']);
        }

        UserNotification::notify(
            $contribution->user_id,
            'contribution.approved',
            'Tu contribucion fue aprobada',
            'Tu aporte fue revisado y publicado. Gracias por colaborar.'
        );
    }

    private function generateUniqueSlug(string $modelClass, array $payload): ?string
    {
        $baseValue = $payload['titulo']
            ?? $payload['interprete']
            ?? $payload['cancion']
            ?? $payload['title']
            ?? null;

        if (! $baseValue) {
            return null;
        }

        $baseSlug = Str::slug($baseValue);
        $slug = $baseSlug;
        $counter = 2;

        while ($modelClass::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}

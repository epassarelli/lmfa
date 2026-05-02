<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Contribution;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;

    protected $newsService;

    public function __construct(\App\Services\NewsService $newsService)
    {
        $this->middleware(['auth', 'role:administrador']);
        $this->newsService = $newsService;
    }

    public function index()
    {
        $contributions = Contribution::with('user')->latest()->get();
        return view('backend.contributions.index', compact('contributions'));
    }

    public function show($id)
    {
        $contribution = Contribution::with('user')->findOrFail($id);
        $modelClass = $contribution->contributable_type;
        $original = $contribution->contributable_id ? $modelClass::find($contribution->contributable_id) : null;

        return view('backend.contributions.show', compact('contribution', 'original'));
    }

    public function approve($id)
    {
        $contribution = Contribution::findOrFail($id);
        
        if ($contribution->status !== 'pending') {
            return back()->with('error', 'Esta contribución ya fue procesada.');
        }

        DB::transaction(function () use ($contribution) {
            $modelClass = $contribution->contributable_type;
            $isNew = $contribution->contributable_id === null;
            $payload = $contribution->payload;

            // Caso especial: Noticias (Usamos el servicio unificado)
            if ($modelClass === \App\Models\News::class) {
                $payload['created_by'] = $contribution->user_id;
                $payload['approved_by'] = auth()->id();
                $payload['editorial_status'] = 'published';

                if ($isNew) {
                    $model = $this->newsService->createNews($payload, $payload['foto'] ?? null);
                    $contribution->contributable_id = $model->id;
                } else {
                    $model = \App\Models\News::findOrFail($contribution->contributable_id);
                    $this->newsService->updateNews($model, $payload, $payload['foto'] ?? null);
                }
            } else {
                // Otros tipos de contenido (Lógica legacy a refactorizar a futuro)
                if (!$isNew) {
                    $model = $modelClass::findOrFail($contribution->contributable_id);
                    $model->update($payload);
                } else {
                    $model = new $modelClass($payload);
                    $model->user_id = $contribution->user_id;
                    $model->estado = 1;
                    $baseSlug = null;
                    if (!empty($model->titulo)) {
                        $baseSlug = \Illuminate\Support\Str::slug($model->titulo);
                    } elseif (!empty($model->interprete)) {
                        $baseSlug = \Illuminate\Support\Str::slug($model->interprete);
                    } elseif (!empty($model->cancion)) {
                        $baseSlug = \Illuminate\Support\Str::slug($model->cancion);
                    }
                    if ($baseSlug) {
                        $slug = $baseSlug;
                        $i = 2;
                        while ($modelClass::where('slug', $slug)->exists()) {
                            $slug = $baseSlug . '-' . $i++;
                        }
                        $model->slug = $slug;
                    }
                    $model->save();
                    $contribution->contributable_id = $model->id;
                }
            }

            $contribution->status = 'approved';
            $contribution->save();

            // Premiar puntos: 50 por contenido nuevo, 20 por edición
            $user = User::find($contribution->user_id);
            $pointsAwarded = $isNew ? 50 : 20;
            $user->increment('points', $pointsAwarded);
            
            // Lógica de rangos (ejemplo simple)
            if ($user->points > 500) $user->update(['rank' => 'Folclorista de Plata']);
            if ($user->points > 1000) $user->update(['rank' => 'Folclorista de Oro']);

            UserNotification::notify(
                $contribution->user_id,
                'contribution.approved',
                '✅ Tu contribución fue aprobada',
                'Tu aporte fue revisado y publicado. ¡Gracias por colaborar!'
            );
        });

        return redirect()->route('backend.contributions.admin.index')->with('success', 'Contribución aprobada y publicada.');
    }

    public function reject(Request $request, $id)
    {
        $contribution = Contribution::findOrFail($id);
        $contribution->update([
            'status' => 'rejected',
            'moderator_comment' => $request->comment
        ]);

        UserNotification::notify(
            $contribution->user_id,
            'contribution.rejected',
            '❌ Tu contribución fue rechazada',
            $request->comment ? "Motivo: {$request->comment}" : 'Tu aporte no pudo ser publicado en esta oportunidad.'
        );

        return redirect()->route('backend.contributions.admin.index')->with('success', 'Contribución rechazada.');
    }
}

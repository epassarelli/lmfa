<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Contribution;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;

class ContributionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador']);
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

            if (!$isNew) {
                // Es una edición
                $model = $modelClass::findOrFail($contribution->contributable_id);
                $model->update($contribution->payload);
            } else {
                // Es contenido nuevo
                $model = new $modelClass($contribution->payload);
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

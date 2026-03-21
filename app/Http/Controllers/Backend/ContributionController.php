<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Contribution;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContributionController extends Controller
{
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
            
            if ($contribution->contributable_id) {
                // Es una edición
                $model = $modelClass::findOrFail($contribution->contributable_id);
                $model->update($contribution->payload);
            } else {
                // Es contenido nuevo
                $model = new $modelClass($contribution->payload);
                $model->user_id = $contribution->user_id;
                $model->estado = 1; // Publicado
                // Generar slug si no existe en el payload
                if (isset($model->titulo) && !isset($model->slug)) {
                    $model->slug = \Illuminate\Support\Str::slug($model->titulo);
                } elseif (isset($model->interprete) && !isset($model->slug)) {
                    $model->slug = \Illuminate\Support\Str::slug($model->interprete);
                } elseif (isset($model->cancion) && !isset($model->slug)) {
                    $model->slug = \Illuminate\Support\Str::slug($model->cancion);
                }
                $model->save();
                
                // Vincular la contribución al nuevo registro
                $contribution->contributable_id = $model->id;
            }

            $contribution->status = 'approved';
            $contribution->save();

            // Premiar puntos
            $user = User::find($contribution->user_id);
            $pointsAwarded = $contribution->contributable_id ? 20 : 50; // 20 por edición, 50 por nuevo
            $user->increment('points', $pointsAwarded);
            
            // Lógica de rangos (ejemplo simple)
            if ($user->points > 500) $user->update(['rank' => 'Folclorista de Plata']);
            if ($user->points > 1000) $user->update(['rank' => 'Folclorista de Oro']);
        });

        return redirect()->route('backend.contributions.index')->with('success', 'Contribución aprobada y publicada.');
    }

    public function reject(Request $request, $id)
    {
        $contribution = Contribution::findOrFail($id);
        $contribution->update([
            'status' => 'rejected',
            'moderator_comment' => $request->comment
        ]);

        return redirect()->route('backend.contributions.index')->with('success', 'Contribución rechazada.');
    }
}

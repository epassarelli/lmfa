<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Contribution;
use Illuminate\Support\Facades\Auth;

class ContributionController extends Controller
{
    public function index()
    {
        $contributions = Auth::user()->contributions()->latest()->get();
        return view('frontend.contributions.index', compact('contributions'));
    }

    public function create($type, $id = null)
    {
        $modelClass = "App\\Models\\" . ucfirst($type);
        if (!class_exists($modelClass)) {
            abort(404);
        }

        $original = $id ? $modelClass::find($id) : null;
        
        return view('frontend.contributions.create', [
            'type' => $type,
            'original' => $original,
            'modelClass' => $modelClass
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'payload' => 'required|array',
        ]);

        $modelClass = "App\\Models\\" . ucfirst($request->type);
        
        Contribution::create([
            'user_id' => Auth::id(),
            'contributable_type' => $modelClass,
            'contributable_id' => $request->id,
            'payload' => $request->payload,
            'status' => 'pending'
        ]);

        return redirect()->route('contributions.index')
            ->with('success', 'Tu colaboración ha sido recibida y está pendiente de moderación. ¡Gracias!');
    }
}

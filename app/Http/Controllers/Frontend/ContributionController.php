<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Contribution;
use Illuminate\Support\Facades\Auth;

class ContributionController extends Controller
{
    private const ALLOWED_TYPES = ['interprete', 'noticia', 'cancion', 'festival', 'event'];

    private const TYPE_TO_MODEL = [
        'interprete' => \App\Models\Interprete::class,
        'noticia'    => \App\Models\News::class,
        'cancion'    => \App\Models\Cancion::class,
        'festival'   => \App\Models\Festival::class,
        'event'      => \App\Models\Event::class,
    ];

    public function index()
    {
        $contributions = Auth::user()->contributions()->latest()->get();
        return view('frontend.contributions.index', compact('contributions'));
    }

    public function create($type, $id = null)
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            abort(404);
        }

        $modelClass = self::TYPE_TO_MODEL[$type];
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
            'type' => ['required', 'string', \Illuminate\Validation\Rule::in(self::ALLOWED_TYPES)],
            'payload' => 'required|array',
        ]);

        $modelClass = self::TYPE_TO_MODEL[$request->type];
        
        Contribution::create([
            'user_id' => Auth::id(),
            'contributable_type' => $modelClass,
            'contributable_id' => $request->id,
            'payload' => $request->payload,
            'status' => 'pending'
        ]);

        return redirect()->route('backend.contributions.index')
            ->with('success', 'Tu colaboración ha sido recibida y está pendiente de moderación. ¡Gracias!');
    }
}

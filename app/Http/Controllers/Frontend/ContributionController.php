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
            'modelClass' => $modelClass,
            'venues' => $type === 'event' ? \App\Models\Venue::where('status', 'active')->orderBy('name')->get(['id', 'name', 'city']) : collect(),
            'provincias' => $type === 'event' ? \App\Models\Provincia::orderBy('nombre')->get() : collect(),
            'interpretes' => $type === 'event' ? \App\Models\Interprete::active()->orderBy('interprete')->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string', \Illuminate\Validation\Rule::in(self::ALLOWED_TYPES)],
            'payload' => 'required|array',
        ]);

        $modelClass = self::TYPE_TO_MODEL[$request->type];
        $payload = $request->payload;
        if ($request->type === 'event') {
            if (isset($payload['interprete_ids'])) $payload['interprete_ids'] = array_values(array_filter($payload['interprete_ids'], 'filled'));
            foreach ($payload['sessions'] ?? [] as $index => $session) {
                $payload['sessions'][$index]['interprete_ids'] = array_values(array_filter($session['interprete_ids'] ?? [], 'filled'));
            }
            if (empty(array_filter($payload['new_venue'] ?? [], 'filled'))) unset($payload['new_venue']);
            if (!empty($payload['sessions']) && !$request->id) {
                unset($payload['start_at'], $payload['end_at'], $payload['interprete_id'], $payload['interprete_ids']);
                $payload = \Illuminate\Support\Facades\Validator::make($payload, \App\Services\EventProgramService::rules())->validate();
            } else {
                unset($payload['sessions']);
                $payload = \App\Support\EventInput::validate($payload, $request->id ? \App\Models\Event::findOrFail($request->id) : null);
            }
            // Una contribución siempre espera moderación, aunque su autor pueda publicar por otro circuito.
            $payload['editorial_status'] = 'draft';
            unset($payload['estado'], $payload['published_at'], $payload['created_by'], $payload['approved_by'], $payload['approved_at']);
        }
        
        Contribution::create([
            'user_id' => Auth::id(),
            'contributable_type' => $modelClass,
            'contributable_id' => $request->id,
            'payload' => $payload,
            'status' => 'pending'
        ]);

        return redirect()->route('backend.contributions.index')
            ->with('success', 'Tu colaboración ha sido recibida y está pendiente de moderación. ¡Gracias!');
    }
}

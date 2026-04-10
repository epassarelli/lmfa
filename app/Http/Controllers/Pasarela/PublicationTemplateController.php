<?php

namespace App\Http\Controllers\Pasarela;

use App\Http\Controllers\Controller;
use App\Models\PublicationTemplate;
use Illuminate\Http\Request;

class PublicationTemplateController extends Controller
{
    private const PROVIDERS = ['facebook', 'instagram', 'telegram', 'native_portal'];
    private const CONTENT_TYPES = ['App\Models\Event', 'App\Models\News'];
    private const VARIANTS = ['default', 'facebook_default', 'instagram_default', 'telegram_default', 'institutional'];

    public function index()
    {
        $templates = PublicationTemplate::orderBy('provider')
            ->orderBy('content_type')
            ->orderBy('variant_name')
            ->paginate(20);

        return view('pasarela.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('pasarela.templates.create', [
            'providers'    => self::PROVIDERS,
            'contentTypes' => self::CONTENT_TYPES,
            'variants'     => self::VARIANTS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'content_type'   => ['nullable', 'string', 'max:255'],
            'provider'       => ['required', 'string', 'in:' . implode(',', self::PROVIDERS)],
            'variant_name'   => ['required', 'string', 'max:100'],
            'template_text'  => ['required', 'string'],
            'is_active'      => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        PublicationTemplate::create($data);

        return redirect()->route('pasarela.templates.index')
            ->with('success', 'Template creado correctamente.');
    }

    public function edit(PublicationTemplate $template)
    {
        return view('pasarela.templates.edit', [
            'template'     => $template,
            'providers'    => self::PROVIDERS,
            'contentTypes' => self::CONTENT_TYPES,
            'variants'     => self::VARIANTS,
        ]);
    }

    public function update(Request $request, PublicationTemplate $template)
    {
        $data = $request->validate([
            'content_type'   => ['nullable', 'string', 'max:255'],
            'provider'       => ['required', 'string', 'in:' . implode(',', self::PROVIDERS)],
            'variant_name'   => ['required', 'string', 'max:100'],
            'template_text'  => ['required', 'string'],
            'is_active'      => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $template->update($data);

        return redirect()->route('pasarela.templates.index')
            ->with('success', 'Template actualizado.');
    }

    public function destroy(PublicationTemplate $template)
    {
        $template->delete();

        return redirect()->route('pasarela.templates.index')
            ->with('success', 'Template eliminado.');
    }

    public function preview(Request $request)
    {
        $text = $request->input('template_text', '');

        $sample = [
            'title'    => 'Festival de Folklore 2026',
            'subtitle' => 'Gran festival en el Parque Centenario',
            'excerpt'  => 'Una noche de música, danza y cultura popular.',
            'url'      => 'https://mifolkloreargentino.com/cartelera/festival-folklore-2026',
            'date'     => '15/08/2026',
            'city'     => 'Buenos Aires',
            'venue'    => 'Parque Centenario',
        ];

        // Simple token replacement for preview
        foreach ($sample as $key => $value) {
            $text = str_replace("{{$key}}", $value, $text);
        }

        return response()->json(['preview' => $text]);
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterEditionMail;
use App\Models\NewsletterEdition;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterEditionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** Ediciones del newsletter: borrador → vista previa y prueba → aprobación → envío. Solo administradores (ver rutas). */
class NewsletterEditionController extends Controller
{
    public function __construct(private NewsletterEditionService $editions)
    {
    }

    public function index()
    {
        $editions = NewsletterEdition::query()->orderByDesc('period_start')->orderByDesc('id')->paginate(15);
        $deliverable = NewsletterSubscriber::deliverable()->count();

        return view('backend.newsletter.editions.index', compact('editions', 'deliverable'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['period_start' => ['nullable', 'date']]);

        if (filled($data['period_start'] ?? null)) {
            $start = Carbon::parse($data['period_start'])->startOfWeek();
            $end = $start->copy()->endOfWeek();
        } else {
            [$start, $end] = NewsletterEditionService::lastWeek();
        }

        $existing = NewsletterEdition::where('edition_key', NewsletterEditionService::keyFor($start))->exists();
        $edition = $this->editions->buildDraft($start, $end, $request->user(), allowEmpty: true);

        return redirect()->route('backend.newsletter.editions.edit', $edition)
            ->with('success', $existing ? 'Ya existía la edición de ese período; la abrimos.' : 'Borrador creado. Revisá el contenido sugerido, ajustalo y aprobalo.');
    }

    public function edit(NewsletterEdition $edition)
    {
        $candidates = $edition->isDraft()
            ? $this->editions->candidates($edition->period_start, $edition->period_end)
            : [];

        return view('backend.newsletter.editions.edit', [
            'edition' => $edition,
            'candidates' => $candidates,
            'content' => $this->editions->content($edition),
            'deliverable' => NewsletterSubscriber::deliverable()->count(),
            'sentCount' => $edition->deliveries()->whereNotNull('sent_at')->count(),
        ]);
    }

    public function update(Request $request, NewsletterEdition $edition)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'intro' => ['nullable', 'string', 'max:1000'],
            'selection' => ['nullable', 'array'],
            'selection.*' => ['array'],
            'selection.*.*' => ['integer'],
        ]);

        $this->editions->update($edition, $data);

        return redirect()->route('backend.newsletter.editions.edit', $edition)->with('success', 'Borrador guardado.');
    }

    public function preview(NewsletterEdition $edition)
    {
        $placeholder = new NewsletterSubscriber(['email' => 'vista-previa@example.test', 'token' => 'prueba']);

        return response((new NewsletterEditionMail($edition, $placeholder, $this->editions->content($edition)))->render());
    }

    public function test(Request $request, NewsletterEdition $edition)
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $this->editions->sendTest($edition, $data['email']);

        return back()->with('success', 'Enviamos una prueba a '.$data['email'].' (asunto con «[PRUEBA]»). No cuenta como envío.');
    }

    public function approve(Request $request, NewsletterEdition $edition)
    {
        $data = $request->validate(['scheduled_at' => ['nullable', 'date']]);

        $this->editions->approve($edition, $request->user(), filled($data['scheduled_at'] ?? null) ? Carbon::parse($data['scheduled_at']) : null);

        return redirect()->route('backend.newsletter.editions.edit', $edition)->with('success', $edition->scheduled_at
            ? 'Edición aprobada y programada para el '.$edition->scheduled_at->format('d/m/Y H:i').'.'
            : 'Edición aprobada. Todavía no se envió: usá «Enviar ahora» o programala.');
    }

    public function unapprove(NewsletterEdition $edition)
    {
        $this->editions->unapprove($edition);

        return redirect()->route('backend.newsletter.editions.edit', $edition)->with('success', 'La edición volvió a borrador.');
    }

    public function send(NewsletterEdition $edition)
    {
        $count = $this->editions->send($edition);

        return redirect()->route('backend.newsletter.editions.index')->with('success', "Edición enviada a {$count} suscriptor(es) confirmado(s). Los envíos salen por la cola.");
    }

    public function destroy(NewsletterEdition $edition)
    {
        if (! $edition->isDraft()) {
            return back()->with('error', 'Solo se puede eliminar un borrador.');
        }

        $edition->delete();

        return redirect()->route('backend.newsletter.editions.index')->with('success', 'Borrador eliminado.');
    }
}

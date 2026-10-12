<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\ContactMessageRequest;
use App\Mail\ContactRecieveEmail;
use App\Mail\ContactSendEmail;
use App\Models\ContactMessage;
use App\Services\ContactFormGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactoController extends Controller
{
    public function __construct(private ContactFormGuard $guard)
    {
    }

    public function index()
    {
        $user = Auth::user();

        return view('frontend.contacto.contacto', [
            'subjects' => config('contact.subjects'),
            'formToken' => $this->guard->token(),
            'prefillName' => $user?->name,
            'prefillEmail' => $user?->email,
        ]);
    }

    public function store(ContactMessageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Los bots que completan el campo trampa reciben la misma respuesta, sin guardar nada.
        if ($this->guard->isHoneypot($request)) {
            return $this->thanks();
        }

        if ($error = $this->guard->tokenError($data['form_token'])) {
            return back()->withInput()->withErrors(['form_token' => $error]);
        }

        if ($this->guard->isDuplicate($data['email'], $data['message'])) {
            return $this->thanks();
        }

        if ($this->guard->emailLimited($data['email'])) {
            return back()->withInput()->withErrors(['email' => 'Ya recibimos varios mensajes desde este correo. Probá de nuevo más tarde.']);
        }

        $this->guard->hitEmail($data['email']);

        $spam = $this->guard->looksLikeSpam($data['message']);

        // Siempre se guarda primero: si el correo falla, el mensaje queda en el backoffice.
        $message = ContactMessage::create([
            'nombre' => $data['name'],
            'email' => $data['email'],
            'asunto' => $data['issue'],
            'mensaje' => $data['message'],
            'fecha_envio' => now(),
            'status' => $spam ? ContactMessage::SPAM : ContactMessage::NEW,
            'spam_reason' => $spam ? 'too_many_links' : null,
            'user_id' => Auth::id(),
            'ip_hash' => $this->guard->ipHash($request->ip()),
        ]);

        if (! $spam) {
            $this->notify($message);
        }

        return $this->thanks();
    }

    private function thanks(): RedirectResponse
    {
        return redirect()->route('contacto')->with('contact_success', 'Gracias por escribirnos. Recibimos tu mensaje y te vamos a responder a la brevedad.');
    }

    /** Los correos son best-effort: un fallo se registra pero no pierde el mensaje ni rompe el envío. */
    private function notify(ContactMessage $message): void
    {
        try {
            Mail::send(new ContactRecieveEmail($message));
        } catch (Throwable $e) {
            Log::error('Contacto: no se pudo avisar al equipo del mensaje nuevo.', ['contact_message_id' => $message->id, 'error' => $e->getMessage()]);
        }

        if (! config('contact.send_confirmation')) {
            return;
        }

        try {
            Mail::send(new ContactSendEmail($message->email, $message->nombre));
        } catch (Throwable $e) {
            Log::error('Contacto: no se pudo enviar la confirmación al remitente.', ['contact_message_id' => $message->id, 'error' => $e->getMessage()]);
        }
    }
}

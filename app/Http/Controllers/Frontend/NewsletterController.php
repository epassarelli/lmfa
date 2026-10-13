<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterSubscriptionService;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /** Respuesta única del formulario: no revela si el correo ya estaba, se dio de baja o es inválido. */
    public const NEUTRAL_MESSAGE = 'Listo. Si el correo es válido, te enviamos un mensaje para confirmar tu suscripción. Revisá también la carpeta de spam.';

    public function __construct(private NewsletterSubscriptionService $subscriptions)
    {
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email:rfc|max:255',
            'source' => 'nullable|string|max:50',
        ]);

        // Campo trampa completo: se responde igual, pero no se crea ni se envía nada.
        if (filled($request->input('company_site'))) {
            return back()->with('newsletter_success', self::NEUTRAL_MESSAGE);
        }

        $this->subscriptions->request($data['email'], $data['source'] ?? 'web', $request->ip(), auth()->id());

        return back()->with('newsletter_success', self::NEUTRAL_MESSAGE);
    }

    /** El enlace del mail solo muestra el botón: confirmar con un GET lo podrían disparar los filtros de correo. */
    public function showConfirmation(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        return view('frontend.newsletter.confirm', ['subscriber' => $subscriber, 'confirmed' => false, 'invalid' => ! $subscriber]);
    }

    public function confirm(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if ($subscriber && $subscriber->status !== NewsletterSubscriber::BOUNCED) {
            $this->subscriptions->confirm($subscriber);
        }

        return view('frontend.newsletter.confirm', [
            'subscriber' => $subscriber,
            'confirmed' => (bool) $subscriber && $subscriber->status === NewsletterSubscriber::ACTIVE,
            'invalid' => ! $subscriber || $subscriber->status === NewsletterSubscriber::BOUNCED,
        ]);
    }

    /** GET: página con botón (los antivirus y filtros abren los enlaces; no deben dar de baja solos). */
    public function showUnsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        return view('frontend.newsletter.unsubscribed', [
            'message' => $subscriber ? null : 'El enlace de baja no es válido.',
            'token' => $subscriber ? $token : null,
            'done' => false,
        ]);
    }

    /** POST: da de baja. También lo usan los clientes de correo con «baja de un clic» (sin token CSRF). */
    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if ($subscriber) {
            $this->subscriptions->unsubscribe($subscriber);
        }

        return view('frontend.newsletter.unsubscribed', [
            'message' => $subscriber
                ? 'Te diste de baja correctamente. Ya no vas a recibir nuestros correos.'
                : 'El enlace de baja no es válido o ya fue procesado.',
            'token' => null,
            'done' => (bool) $subscriber,
        ]);
    }
}

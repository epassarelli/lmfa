<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewsletterSubscriber;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255'
        ]);

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $request->email]);

        if ($subscriber->exists && $subscriber->status === 'active') {
            return back()->with('newsletter_info', 'Ya estabas suscrito a nuestro newsletter. ¡Gracias por continuar apoyando el folklore!');
        }

        $subscriber->status = 'active';
        $subscriber->unsubscribed_at = null;
        $subscriber->user_id = auth()->id();
        $subscriber->source = $request->input('source', 'web');
        $subscriber->save();

        return back()->with('newsletter_success', '¡Suscripción exitosa! Recibirás nuestras novedades.');
    }

    public function unsubscribe($token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if ($subscriber && $subscriber->status === 'active') {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now()
            ]);
            $message = 'Te has desuscrito correctamente. Ya no recibirás nuestros correos semanales.';
        } else {
            $message = 'El enlace de desuscripción no es válido o ya fue procesado con anterioridad.';
        }

        return view('frontend.newsletter.unsubscribed', compact('message'));
    }
}

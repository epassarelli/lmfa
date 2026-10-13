<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterSubscriptionService;
use App\Support\BackendListing;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    public function __construct(private NewsletterSubscriptionService $subscriptions)
    {
    }

    public function index(Request $request)
    {
        [$sort, $direction] = BackendListing::resolveSort(
            $request,
            ['email', 'status', 'created_at', 'unsubscribed_at', 'confirmed_at'],
            'created_at'
        );

        $query = NewsletterSubscriber::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();

            $query->where(function ($inner) use ($search) {
                $inner->where('email', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        // «unconfirmed» = pendientes de confirmar (incluye los heredados activos que nunca confirmaron).
        if ($request->input('status') === 'unconfirmed') {
            $query->unconfirmed();
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscribers = $query
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'deliverable' => NewsletterSubscriber::deliverable()->count(),
            'unconfirmed' => NewsletterSubscriber::unconfirmed()->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', NewsletterSubscriber::UNSUBSCRIBED)->count(),
            'bounced' => NewsletterSubscriber::where('status', NewsletterSubscriber::BOUNCED)->count(),
        ];

        return view('backend.newsletter.index', compact('subscribers', 'counts'));
    }

    /** Da de baja a un suscriptor activo. Reactivar no se hace desde acá: lo confirma la persona desde su correo. */
    public function toggleStatus(NewsletterSubscriber $subscriber)
    {
        if (! in_array($subscriber->status, [NewsletterSubscriber::ACTIVE, NewsletterSubscriber::PENDING], true)) {
            return back()->with('success', 'Para reactivarlo, la persona tiene que confirmar su suscripción desde su correo (usá «Pedir confirmación»).');
        }

        $this->subscriptions->unsubscribe($subscriber);

        return back()->with('success', 'Suscriptor dado de baja.');
    }

    /** Para direcciones que el administrador sabe inexistentes (p. ej. por un aviso de rechazo recibido). */
    public function markBounced(NewsletterSubscriber $subscriber)
    {
        $this->subscriptions->markBounced($subscriber);

        return back()->with('success', 'Marcada como rebotada: no se le vuelve a escribir.');
    }

    public function requestConfirmation(NewsletterSubscriber $subscriber)
    {
        if ($subscriber->isDeliverable()) {
            return back()->with('success', 'Esa persona ya confirmó su suscripción.');
        }

        $sent = $this->subscriptions->sendConfirmation($subscriber);

        return back()->with('success', $sent
            ? 'Enviamos el correo de confirmación a '.$subscriber->email.'.'
            : 'No se envió: se pidió hace menos de '.NewsletterSubscriptionService::RESEND_AFTER_MINUTES.' minutos, la dirección rebotó o falló el envío (ver el log).');
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return back()->with('success', 'Suscriptor eliminado.');
    }
}

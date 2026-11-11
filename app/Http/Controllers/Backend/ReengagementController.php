<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\ReengagementMail;
use App\Models\ReengagementMailSend;
use App\Services\ReengagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/** Pantalla del administrador para disparar a mano el mail de reactivación de usuarios. */
class ReengagementController extends Controller
{
    public function __construct(private ReengagementService $service)
    {
    }

    public function index()
    {
        $eligible = $this->service->eligibleQuery()->orderBy('id');
        $total = (clone $eligible)->count();

        $users = $eligible->select(['id', 'name', 'email', 'last_login_at', 'created_at'])->limit(50)->get()
            ->each(fn ($user) => $user->variant = $this->service->variantFor($user));

        return view('backend.reengagement.index', [
            'total' => $total,
            'neverCount' => (clone $this->service->eligibleQuery())->whereNull('last_login_at')->count(),
            'users' => $users,
            'lastBatchAt' => $this->service->lastBatchAt(),
            'dueSince' => $this->service->dueSince(),
            'isDue' => $this->service->isDue(),
            'batches' => $this->service->recentBatches(),
            'inactivityDays' => $this->service->inactivityDays(),
            'cooldownDays' => $this->service->cooldownDays(),
        ]);
    }

    /** Vista previa en el navegador, con los datos del propio administrador. */
    public function preview(Request $request)
    {
        $mail = new ReengagementMail($request->user(), $this->variant($request));

        return response($mail->render());
    }

    /** Envía una prueba solo al correo del administrador que la pide. */
    public function test(Request $request)
    {
        Mail::to($request->user()->email)->send(new ReengagementMail($request->user(), $this->variant($request)));

        return back()->with('success', 'Te enviamos una prueba a '.$request->user()->email.'.');
    }

    public function send(Request $request)
    {
        $request->validate(['confirm' => ['accepted']], ['confirm.accepted' => 'Confirmá el envío marcando la casilla.']);

        $count = $this->service->dispatchBatch($request->user());

        if ($count === 0) {
            return back()->with('error', 'No hay destinatarios para enviar (o ya se está procesando otra tanda).');
        }

        return back()->with('success', "Se pusieron en cola {$count} mails. Salen de a poco, respetando el tope por minuto.");
    }

    private function variant(Request $request): string
    {
        return $request->query('variant', $request->input('variant')) === ReengagementMailSend::VARIANT_NEVER
            ? ReengagementMailSend::VARIANT_NEVER
            : ReengagementMailSend::VARIANT_LAPSED;
    }
}

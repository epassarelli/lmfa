<?php

namespace App\Jobs;

use App\Mail\ReengagementMail;
use App\Models\ReengagementMailSend;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/** Envía el mail de reactivación a un usuario de una tanda, una sola vez. */
class SendReengagementMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public int $sendId)
    {
    }

    public function handle(): void
    {
        $send = ReengagementMailSend::find($this->sendId);

        // Solo lo que sigue en cola: un reintento o doble despacho no duplica el correo.
        if (! $send || $send->status !== ReengagementMailSend::QUEUED) {
            return;
        }

        $user = User::find($send->user_id);

        // Pudo darse de baja o desactivarse entre el despacho y el envío.
        if (! $user || $user->status !== 'active' || $user->reengagement_opt_out_at !== null) {
            $send->update(['status' => ReengagementMailSend::SKIPPED]);

            return;
        }

        // Ritmo: lo que excede el tope vuelve a la cola. Con cola síncrona (tests/desarrollo) no aplica.
        $limit = (int) config('reengagement.max_per_minute');

        if ($limit > 0 && config('queue.default') !== 'sync') {
            if (RateLimiter::tooManyAttempts('reengagement-send', $limit)) {
                $this->release(30);

                return;
            }

            RateLimiter::hit('reengagement-send', 60);
        }

        Mail::to($send->email)->send(new ReengagementMail($user, $send->variant));

        $send->update(['status' => ReengagementMailSend::SENT, 'sent_at' => now(), 'error' => null]);
    }

    public function failed(\Throwable $e): void
    {
        ReengagementMailSend::query()
            ->whereKey($this->sendId)
            ->where('status', ReengagementMailSend::QUEUED)
            ->update(['status' => ReengagementMailSend::FAILED, 'error' => mb_substr($e->getMessage(), 0, 500)]);
    }
}

<?php

namespace App\Services;

use App\Jobs\SendReengagementMailJob;
use App\Models\ReengagementMailSend;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Mail de «te extrañamos» a usuarios inactivos. Reglas en project/docs/specs/reactivacion-usuarios.md.
 */
class ReengagementService
{
    public function inactivityDays(): int
    {
        return max(1, (int) config('reengagement.inactivity_days', 30));
    }

    public function cooldownDays(): int
    {
        return max(1, (int) config('reengagement.cooldown_days', 30));
    }

    /**
     * Usuarios que hoy recibirían el mail:
     * - activos, con email y sin baja de estos mails;
     * - último ingreso hace más de N días, o nunca ingresaron y la cuenta no es de los últimos N días;
     * - sin otro mail de reactivación en cola o enviado en los últimos N días.
     */
    public function eligibleQuery(): Builder
    {
        $limit = now()->subDays($this->inactivityDays());

        return User::query()
            ->where('status', 'active')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereNull('reengagement_opt_out_at')
            ->where(function (Builder $q) use ($limit) {
                $q->where('last_login_at', '<', $limit)
                    ->orWhere(function (Builder $never) use ($limit) {
                        $never->whereNull('last_login_at')
                            ->where(fn (Builder $created) => $created->whereNull('created_at')->orWhere('created_at', '<', $limit));
                    });
            })
            ->whereNotExists(function ($sub) use ($limit) {
                $sub->selectRaw('1')
                    ->from('reengagement_mail_sends as s')
                    ->whereColumn('s.user_id', 'users.id')
                    ->whereIn('s.status', [ReengagementMailSend::QUEUED, ReengagementMailSend::SENT])
                    ->where('s.created_at', '>=', now()->subDays($this->cooldownDays()));
            });
    }

    public function eligibleCount(): int
    {
        return $this->eligibleQuery()->count();
    }

    public function variantFor(User $user): string
    {
        return $user->last_login_at === null ? ReengagementMailSend::VARIANT_NEVER : ReengagementMailSend::VARIANT_LAPSED;
    }

    public function lastBatchAt(): ?Carbon
    {
        $value = ReengagementMailSend::query()->max('created_at');

        return $value ? Carbon::parse($value) : null;
    }

    /** Pasaron N días desde la última tanda (o nunca se envió). */
    public function isDue(): bool
    {
        $last = $this->lastBatchAt();

        return $last === null || $last->lte(now()->subDays($this->cooldownDays()));
    }

    public function dueSince(): ?Carbon
    {
        return $this->lastBatchAt()?->copy()->addDays($this->cooldownDays());
    }

    /** Aviso del backoffice: corresponde una nueva tanda y hay a quién enviarle. */
    public function pendingCount(): int
    {
        return $this->isDue() ? $this->eligibleCount() : 0;
    }

    /**
     * Crea el registro de cada destinatario y encola su envío. Devuelve la cantidad encolada.
     * Un bloqueo corto evita que un doble clic arme dos tandas a la vez.
     */
    public function dispatchBatch(?User $triggeredBy = null): int
    {
        $lock = Cache::lock('reengagement-dispatch', 120);

        if (! $lock->get()) {
            return 0;
        }

        try {
            $batch = (string) Str::uuid();
            $count = 0;

            $this->eligibleQuery()->select(['id', 'email', 'last_login_at'])->orderBy('id')->chunkById(200, function ($users) use ($batch, $triggeredBy, &$count) {
                foreach ($users as $user) {
                    $send = ReengagementMailSend::create([
                        'batch_uuid' => $batch,
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'variant' => $this->variantFor($user),
                        'status' => ReengagementMailSend::QUEUED,
                        'triggered_by' => $triggeredBy?->id,
                    ]);

                    SendReengagementMailJob::dispatch($send->id);
                    $count++;
                }
            });

            app(AdminAttentionService::class)->forget();

            return $count;
        } finally {
            $lock->release();
        }
    }

    /** Enlace firmado de baja (no vence: debe funcionar siempre desde el mail). */
    public function unsubscribeUrl(User $user): string
    {
        return URL::signedRoute('reengagement.unsubscribe', ['user' => $user->id]);
    }

    public function optOut(User $user): void
    {
        if ($user->reengagement_opt_out_at === null) {
            $user->forceFill(['reengagement_opt_out_at' => now()])->save();
        }

        app(AdminAttentionService::class)->forget();
    }

    /** @return \Illuminate\Support\Collection<int, object> últimas tandas con su resumen por estado */
    public function recentBatches(int $limit = 10)
    {
        return ReengagementMailSend::query()
            ->selectRaw("batch_uuid, MIN(created_at) as started_at, COUNT(*) as total,
                SUM(status = 'sent') as sent, SUM(status = 'failed') as failed,
                SUM(status = 'queued') as queued, SUM(status = 'skipped') as skipped")
            ->groupBy('batch_uuid')
            ->orderByDesc('started_at')
            ->limit($limit)
            ->get();
    }
}

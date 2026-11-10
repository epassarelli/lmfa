<?php

namespace App\Jobs\Publication;

use App\Models\PublicationTarget;
use App\Services\Connectors\ConnectorFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishToProviderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // seconds between retries

    public function __construct(
        protected int $targetId
    ) {}

    public function handle(): void
    {
        $target = PublicationTarget::with(['socialAccount', 'request'])->find($this->targetId);

        if (!$target) {
            Log::warning("[PublishToProviderJob] Target #{$this->targetId} not found.");
            return;
        }

        if (in_array($target->status, ['success', 'published']) || app(\App\Services\Publication\SocialPublicationGuard::class)->managed($target)) {
            Log::info("[PublishToProviderJob] Target #{$this->targetId} already published, skipping.");
            return;
        }

        $target->update(['status' => 'processing']);

        try {
            $connector = ConnectorFactory::make($target->provider);

            // Check health before publishing
            if (!$connector->isHealthy($target)) {
                $target->update(['status' => 'failed']);
                Log::error("[PublishToProviderJob] Connector unhealthy for target #{$this->targetId} provider={$target->provider}");
                return;
            }

            $result = $connector->publish($target);

            if ($result['success']) {
                Log::info("[PublishToProviderJob] Published target #{$this->targetId} via {$target->provider}.");
            } else {
                Log::warning("[PublishToProviderJob] Failed target #{$this->targetId}: " . 'Consultar el intento saneado');
                // Ensure target is marked failed if connector returned early without recording an attempt
                $target->refresh();
                if ($target->status === 'processing') {
                    $target->update(['status' => 'failed']);
                }
            }

        } catch (\Exception $e) {
            $target->update(['status' => 'failed']);
            Log::error("[PublishToProviderJob] Exception for target #{$this->targetId}: " . get_class($e));
            throw $e; // Let Laravel retry if attempts remain
        }
    }

    public function failed(\Throwable $exception): void
    {
        $target = PublicationTarget::find($this->targetId);
        if ($target && !in_array($target->status, ['success', 'published']) && !app(\App\Services\Publication\SocialPublicationGuard::class)->managed($target)) {
            $target->update(['status' => 'failed']);
        }
        Log::error("[PublishToProviderJob] FINAL FAILURE target #{$this->targetId}: " . get_class($exception));
    }
}

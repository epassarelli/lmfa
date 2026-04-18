<?php

namespace App\Services\Connectors;

use App\Models\PublicationTarget;
use App\Models\PublicationAttempt;
use App\Models\PublicationRequest;

abstract class BaseConnector
{
    /**
     * Publish the content represented by the given target.
     * Must return an array with keys: success (bool), external_post_id, external_url, error_code, error_message.
     */
    abstract public function publish(PublicationTarget $target): array;

    /**
     * Check if the connector is healthy (account token valid, etc).
     */
    public function isHealthy(PublicationTarget $target): bool
    {
        $account = $target->socialAccount;
        if (!$account) return true; // native portal has no account

        if ($account->status !== 'active') return false;
        if ($account->token_expires_at && $account->token_expires_at->isPast()) return false;

        return true;
    }

    /**
     * Load the content model from the publication request.
     */
    protected function resolveContent(PublicationTarget $target): ?object
    {
        $request = $target->request;
        if (!$request) return null;

        $modelClass = $request->content_type;
        return $modelClass::with(['images'])->find($request->content_id);
    }

    /**
     * Record a PublicationAttempt for this target.
     */
    protected function recordAttempt(
        PublicationTarget $target,
        array $payload,
        array $result
    ): PublicationAttempt {
        $attemptNumber = $target->attempts()->count() + 1;

        $status = $result['success'] ? 'success' : 'failed';

        $target->update(['status' => $status]);

        return PublicationAttempt::create([
            'publication_target_id'  => $target->id,
            'attempt_number'         => $attemptNumber,
            'started_at'             => now(),
            'finished_at'            => now(),
            'request_payload_json'   => $payload,
            'response_payload_json'  => $result['response'] ?? null,
            'external_post_id'       => $result['external_post_id'] ?? null,
            'external_url'           => $result['external_url'] ?? null,
            'status'                 => $status,
            'error_code'             => $result['error_code'] ?? null,
            'error_message'          => $result['error_message'] ?? null,
            'is_retryable'           => $result['is_retryable'] ?? false,
        ]);
    }
}

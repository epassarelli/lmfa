<?php

namespace App\Http\Requests\Concerns;

trait EnforcesEditorialProposalState
{
    protected function enforceEditorialProposalState(): void
    {
        if (! $this->user() || $this->user()->hasRole('administrador')) {
            return;
        }

        $this->merge([
            'editorial_status' => 'draft',
            'published_at' => null,
            'verification_status' => 'pending',
            'last_verified_at' => null,
            'verified_by_user_id' => null,
            'verification_method' => null,
        ]);
    }
}

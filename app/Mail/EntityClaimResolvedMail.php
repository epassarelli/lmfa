<?php

namespace App\Mail;

use App\Models\EntityClaimRequest;
use App\Models\OrganizationMember;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso a quien hizo una solicitud de ficha (vínculo o alta) de que fue aprobada o rechazada.
 */
class EntityClaimResolvedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EntityClaimRequest $claim)
    {
    }

    public function envelope(): Envelope
    {
        $approved = $this->claim->status === EntityClaimRequest::APPROVED;
        $name = trim(preg_replace('/[\r\n]+/', ' ', (string) ($this->claim->displayName() ?? 'tu ficha')));

        return new Envelope(
            subject: $approved ? "Aprobamos tu solicitud sobre {$name}" : "Tu solicitud sobre {$name} no fue aprobada",
            to: [$this->claim->user->email],
        );
    }

    public function content(): Content
    {
        $approved = $this->claim->status === EntityClaimRequest::APPROVED;
        $role = null;

        if ($approved && $this->claim->organization_id) {
            $memberRole = OrganizationMember::query()
                ->where('organization_id', $this->claim->organization_id)
                ->where('user_id', $this->claim->user_id)
                ->value('role');
            $role = $memberRole ? (config('entity_management.member_roles')[$memberRole] ?? $memberRole) : null;
        }

        return new Content(
            view: 'emails.entity_claim_resolved',
            with: [
                'approved' => $approved,
                'userName' => $this->claim->user->name,
                'entityName' => $this->claim->displayName() ?? 'tu ficha',
                'isCreation' => $this->claim->isCreation(),
                'role' => $role,
                'notes' => $this->claim->review_notes,
                'url' => route('backend.my-entities.index'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

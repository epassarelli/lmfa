<?php

namespace App\Mail;

use App\Models\ReengagementMailSend;
use App\Models\User;
use App\Services\ReengagementService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/** «Te extrañamos»: invita a volver a la cuenta, cuenta las ventajas y permite recuperar la contraseña. */
class ReengagementMail extends Mailable
{
    use SerializesModels;

    public function __construct(public User $user, public string $variant = ReengagementMailSend::VARIANT_LAPSED)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->variant === ReengagementMailSend::VARIANT_NEVER
            ? 'Tu cuenta en Mi Folklore Argentino te está esperando'
            : 'En Mi Folklore Argentino te extrañamos');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.reengagement',
            with: [
                'name' => $this->user->name,
                'never' => $this->variant === ReengagementMailSend::VARIANT_NEVER,
                'loginUrl' => route('login'),
                'resetUrl' => route('password.request'),
                'unsubscribeUrl' => app(ReengagementService::class)->unsubscribeUrl($this->user),
            ],
        );
    }

    /** Baja de un clic desde Gmail/Outlook (RFC 8058). */
    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.app(ReengagementService::class)->unsubscribeUrl($this->user).'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }
}

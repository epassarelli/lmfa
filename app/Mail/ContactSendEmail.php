<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Confirmación de recepción al remitente (se puede apagar con CONTACT_SEND_CONFIRMATION). */
class ContactSendEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $contactEmail, public string $name)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Gracias por contactarnos - Mi Folklore Argentino',
            to: [$this->contactEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_confirmation',
            with: ['name' => $this->name],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

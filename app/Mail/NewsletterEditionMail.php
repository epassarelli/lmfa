<?php

namespace App\Mail;

use App\Models\NewsletterEdition;
use App\Models\NewsletterSubscriber;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/** Correo de una edición del newsletter. Se dibuja siempre con el contenido congelado (o la vista previa del borrador). */
class NewsletterEditionMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public NewsletterEdition $edition,
        public NewsletterSubscriber $subscriber,
        public array $sections,
        public bool $isTest = false,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: ($this->isTest ? '[PRUEBA] ' : '').$this->edition->subject);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.newsletter.edition', with: ['unsubscribeUrl' => $this->subscriber->unsubscribeUrl()]);
    }

    /** Baja de un clic desde Gmail/Outlook (List-Unsubscribe + List-Unsubscribe-Post, RFC 8058). */
    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->subscriber->unsubscribeUrl().'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }
}

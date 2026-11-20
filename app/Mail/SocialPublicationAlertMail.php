<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Aviso operativo de publicaciones en redes (fallo, resultado incierto, token inválido). Sin datos sensibles. */
class SocialPublicationAlertMail extends Mailable
{
    use Queueable;

    public function __construct(public string $recipient, public string $title, public array $lines, public string $panelUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[MFA redes] '.preg_replace('/[\r\n]+/', ' ', $this->title), to: [$this->recipient]);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.social_publication_alert');
    }
}

<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail de doble confirmación: sin este clic no se envía ningún newsletter. */
class ConfirmNewsletterSubscriptionMail extends Mailable
{
    use SerializesModels;

    public function __construct(public NewsletterSubscriber $subscriber)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirmá tu suscripción al newsletter de Mi Folklore Argentino');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.newsletter.confirm',
            with: [
                'confirmUrl' => $this->subscriber->confirmationUrl(),
                'days' => NewsletterSubscriber::CONFIRMATION_DAYS,
            ],
        );
    }
}

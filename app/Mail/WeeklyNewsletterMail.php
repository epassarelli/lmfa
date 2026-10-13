<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\NewsletterSubscriber;
use Illuminate\Mail\Mailables\Headers;

class WeeklyNewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscriber;
    public $newsData;

    public function __construct(NewsletterSubscriber $subscriber, array $newsData)
    {
        $this->subscriber = $subscriber;
        $this->newsData = $newsData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lo mejor de la semana en el Folklore Argentino',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.newsletter.weekly',
        );
    }

    /** Baja de un clic desde Gmail/Outlook (List-Unsubscribe + List-Unsubscribe-Post). */
    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->subscriber->unsubscribeUrl().'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function attachments(): array
    {
        return [];
    }
}

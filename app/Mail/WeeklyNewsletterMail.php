<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\NewsletterSubscriber;

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

    public function attachments(): array
    {
        return [];
    }
}

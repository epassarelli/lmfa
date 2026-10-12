<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso al equipo de un mensaje nuevo del formulario de contacto. El Reply-To es el remitente, así que
 * responder desde el correo escribe directamente a quien nos contactó.
 */
class ContactRecieveEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contact)
    {
    }

    public function envelope(): Envelope
    {
        // Sin saltos de línea: el asunto y el nombre vienen de un formulario público.
        $clean = fn (string $value) => trim(preg_replace('/[\r\n]+/', ' ', $value));

        return new Envelope(
            subject: 'Contacto web: '.$clean($this->contact->asunto),
            to: [config('contact.notify_to')],
            replyTo: [new Address($this->contact->email, $clean($this->contact->nombre))],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_message',
            with: [
                'contact' => $this->contact->toArray(),
                'adminUrl' => route('backend.contact-messages.show', $this->contact->id),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

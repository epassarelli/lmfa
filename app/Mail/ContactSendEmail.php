<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactSendEmail extends Mailable
{
  use Queueable, SerializesModels;
  public $contactEmail, $name, $lastName;
  /**
   * Create a new message instance.
   */
  public function __construct($email, $name, $lastName)
  {
    $this->contactEmail = $email;
    $this->name = $name;
    $this->lastName = $lastName;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'EURO-HARD',
      to: $this->contactEmail,
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'mail.contact.send',
      with: [
        'name' => $this->name,
        'lastName' => $this->lastName
      ]
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>
   */
  public function attachments(): array
  {
    return [];
  }
}

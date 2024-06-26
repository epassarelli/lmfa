<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CancionCreated extends Mailable
{
  use Queueable, SerializesModels;

  public $details;

  public function __construct($details)
  {
    $this->details = $details;
  }

  public function build()
  {
    return $this->subject('Nueva Canción Creada')
      ->view('emails.cancionCreated');
  }
}

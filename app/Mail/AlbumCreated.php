<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlbumCreated extends Mailable
{
  use Queueable, SerializesModels;

  public $details;

  public function __construct($details)
  {
    $this->details = $details;
  }

  public function build()
  {
    return $this->subject('Nuevo Álbum Creado')
      ->view('emails.albumCreated');
  }
}

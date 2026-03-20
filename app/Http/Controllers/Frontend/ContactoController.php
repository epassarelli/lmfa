<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Frontend\ContactMessageRequest;
use App\Mail\ContactRecieveEmail;
use App\Mail\ContactSendEmail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;

class ContactoController extends Controller
{
  public function index()
  {
    return view('frontend.contacto.contacto');
  }

  public function store(ContactMessageRequest $request)
  {
    // Create message in DB
    $message = ContactMessage::create([
        'nombre' => $request->name . ' ' . $request->lastName,
        'email' => $request->email,
        'asunto' => $request->issue,
        'mensaje' => $request->message,
        'fecha_envio' => now(),
    ]);

    // Send email to admin
    try {
        Mail::send(new ContactRecieveEmail($message->toArray()));
    } catch (\Exception $e) {
        // Log error if needed
    }

    // Send confirmation to user
    try {
        Mail::send(new ContactSendEmail($request->email, $request->name, $request->lastName));
    } catch (\Exception $e) {
        // Log error if needed
    }

    toast('Gracias por contactarnos, te responderemos a la brevedad.', 'success');
    return redirect()->route('contacto');
  }
}

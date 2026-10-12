<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Bandeja de mensajes del formulario de contacto. Solo administradores (ver routes/admin.php).
 */
class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status', ContactMessage::NEW)->toString();
        $search = trim($request->string('q')->toString());

        $messages = ContactMessage::query()
            ->when($status !== 'all' && array_key_exists($status, ContactMessage::statuses()), fn ($query) => $query->status($status))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(fn ($q) => $q->where('nombre', 'like', $like)->orWhere('email', 'like', $like)->orWhere('asunto', 'like', $like)->orWhere('mensaje', 'like', $like));
            })
            ->orderByDesc('fecha_envio')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $counts = ContactMessage::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('backend.contact-messages.index', compact('messages', 'status', 'search', 'counts'));
    }

    public function show(ContactMessage $contactMessage)
    {
        // Abrirlo lo marca como leído (solo si era nuevo).
        if ($contactMessage->status === ContactMessage::NEW) {
            $contactMessage->update(['status' => ContactMessage::READ, 'read_at' => now()]);
        }

        return view('backend.contact-messages.show', ['message' => $contactMessage]);
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(ContactMessage::statuses()))]]);

        $contactMessage->update([
            'status' => $data['status'],
            'read_at' => $contactMessage->read_at ?? ($data['status'] === ContactMessage::NEW ? null : now()),
            'answered_at' => $data['status'] === ContactMessage::ANSWERED ? now() : $contactMessage->answered_at,
        ]);

        return back()->with('success', 'Estado actualizado: '.$contactMessage->fresh()->statusLabel().'.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()->route('backend.contact-messages.index', ['status' => 'all'])->with('success', 'Mensaje eliminado.');
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\{AuditLog, News, Event, PublicationTarget, SocialPublicationEligibility};
use App\Services\Publication\SocialPublicationLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialPublicationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['content_kind' => 'nullable|in:news,events', 'status' => 'nullable|in:pending,reserved,processing,success,failed,uncertain,cancelled']);
        $targets = PublicationTarget::with('request.content')->where('executor', 'n8n')->where('destination_key', config('social_publication.destination'))
            ->when($filters['content_kind'] ?? null, fn ($q, $v) => $q->where('ledger_content_kind', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->orderByDesc('id')->paginate(15)->withQueryString();
        return view('backend.social-publications.index', compact('targets'));
    }

    public function eligibility(Request $request, SocialPublicationLedgerService $ledger)
    {
        $data = $request->validate(['content_kind' => 'required|in:news,events', 'content_id' => 'required|integer|min:1', 'enabled' => 'required|boolean', 'portal_only_override' => 'nullable|accepted']);
        $ledger->enable($data['content_kind'], $data['content_id'], (bool) $data['enabled'], $request->boolean('portal_only_override'), $request->user()->id);
        return back()->with('success', 'Habilitación actualizada. No se envió ninguna publicación.');
    }

    public function resolve(Request $request, int $target, SocialPublicationLedgerService $ledger)
    {
        $data = $request->validate(['action' => 'required|in:retry,published,not_published', 'reason' => 'required|string|min:10|max:500', 'confirmed' => 'required|accepted', 'external_post_id' => 'required_if:action,published|nullable|string|max:128']);
        DB::transaction(function () use ($target, $data, $ledger) {
            $t = PublicationTarget::where('executor', 'n8n')->where('destination_key', config('social_publication.destination'))->lockForUpdate()->findOrFail($target);
            $old = $t->status;
            if ($data['action'] === 'retry') {
                abort_unless($old === 'failed' && $t->attempts_count < 3 && (!$t->retry_after || $t->retry_after->isPast()), 409);
                $t->update(['status' => 'pending']);
            } else {
                abort_unless($old === 'uncertain', 409);
                if ($data['action'] === 'published') {
                    $page = substr($t->destination_key, strlen('facebook:page:'));
                    abort_unless(preg_match('/^'.preg_quote($page, '/').'_[0-9]+$/D', $data['external_post_id']), 422);
                    $t->update(['status' => 'success', 'external_post_id' => $data['external_post_id'], 'external_url' => 'https://www.facebook.com/'.$data['external_post_id'], 'published_at' => now()]);
                } else {
                    $t->update(['status' => 'failed', 'retry_after' => now()->addMinutes(30)]);
                }
                $t->attempts()->where('status', 'uncertain')->update(['status' => $t->status, 'finished_at' => now(),
                    'external_post_id' => $t->external_post_id, 'external_url' => $t->external_url]);
                $t->update(['lease_token_hash' => null]);
            }
            AuditLog::log('publication_target', $t->id, 'manual_resolution', ['status' => $old], ['status' => $t->status, 'reason' => $data['reason']]);
            $ledger->aggregate($t);
        });
        return back()->with('success', 'Acción registrada con auditoría.');
    }
}

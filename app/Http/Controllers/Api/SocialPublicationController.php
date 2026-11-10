<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{News, Event, PublicationTarget, SocialPublicationEligibility};
use App\Services\Publication\{SocialPublicationGuard, SocialPublicationLedgerService};
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class SocialPublicationController extends Controller
{
    public function __construct(private SocialPublicationLedgerService $ledger) {}

    private function active(): void { abort_unless(config('social_publication.enabled'), 503, 'Automatización pausada.'); }

    public function candidates(Request $request)
    {
        $this->active();
        $data = $request->validate(['content_kind' => 'nullable|in:news,events', 'per_page' => 'nullable|integer|min:1|max:50', 'provider' => 'nullable|in:facebook']);
        $query = SocialPublicationEligibility::where('enabled', true)->where('destination_key', config('social_publication.destination'))
            ->when($data['content_kind'] ?? null, fn ($q, $kind) => $q->where('content_kind', $kind))
            ->whereExists(function ($q) {
                $q->selectRaw('1')->from('publication_targets as t')->whereColumn('t.ledger_content_kind', 'social_publication_eligibilities.content_kind')
                    ->whereColumn('t.ledger_content_id', 'social_publication_eligibilities.content_id')->whereColumn('t.destination_key', 'social_publication_eligibilities.destination_key')
                    ->where('t.executor', 'n8n')->whereIn('t.status', ['pending', 'failed'])->where('t.attempts_count', '<', 3)
                    ->where(fn ($r) => $r->whereNull('t.retry_after')->orWhere('t.retry_after', '<=', now()));
            });
        $page = $query->orderBy('id')->paginate($data['per_page'] ?? 15);
        $models = [];
        foreach (['news' => News::class, 'events' => Event::class] as $kind => $class) {
            $models[$kind] = $class::whereIn('id', $page->getCollection()->where('content_kind', $kind)->pluck('content_id'))->get()->keyBy('id');
        }
        $legacy = PublicationTarget::with('request:id,content_type,content_id')->where('executor', 'laravel')->where('provider', 'facebook')
            ->whereHas('socialAccount', fn ($q) => $q->where('account_external_id', substr(config('social_publication.destination'), strlen('facebook:page:'))))
            ->whereHas('request', fn ($q) => $q->whereIn('content_id', $page->getCollection()->pluck('content_id')))->get()
            ->mapWithKeys(fn ($t) => [$t->request->content_type.':'.$t->request->content_id => true]);
        $page->setCollection($page->getCollection()->map(function ($e) use ($models, $legacy) {
            try {
                $model = $models[$e->content_kind]->get($e->content_id);
                if (!$model || isset($legacy[get_class($model).':'.$model->id])) { return null; }
                return ['destination_key' => $e->destination_key, 'provider' => 'facebook'] + $this->ledger->snapshot($e, $model);
            } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface|\Illuminate\Database\Eloquent\ModelNotFoundException $ex) { return null; }
        })->filter()->values());
        return response()->json($page);
    }

    public function claim(Request $request)
    {
        $this->active();
        $request->merge(['request_key' => $request->header('Idempotency-Key')]);
        $data = $request->validate(['content_kind' => 'required|in:news,events', 'content_id' => 'required|integer|min:1',
            'destination_key' => 'required|string', 'provider' => 'required|in:facebook', 'request_key' => 'required|uuid', 'n8n_execution_id' => 'nullable|string|max:96']);
        try {
            $result = $this->ledger->claim($data['content_kind'], $data['content_id'], $data['request_key'], $data['n8n_execution_id'] ?? null);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') { abort(409, 'Conflicto de reserva: consultar estado.'); }
            throw $e;
        }
        return response()->json($result, $result['replayed'] ? 200 : 201);
    }

    public function begin(Request $request, int $target)
    {
        $this->active();
        $data = $request->validate(['attempt_id' => 'required|integer|min:1', 'lease' => 'required|string|size:64', 'content_hash' => 'required|regex:/^[a-f0-9]{64}$/',
            'asset_reference' => 'required|string|max:512', 'asset_sha256' => 'required|regex:/^[a-f0-9]{64}$/']);
        return response()->json($this->ledger->begin($target, $data));
    }

    public function result(Request $request, int $target)
    {
        $data = $request->validate(['attempt_id' => 'required|integer|min:1', 'lease' => 'required|string|size:64',
            'outcome' => 'required|in:success,definitive_failure,uncertain', 'external_post_id' => 'required_if:outcome,success|nullable|string|max:128',
            'error_code' => 'required_if:outcome,definitive_failure|nullable|regex:/^[A-Z0-9_]{1,64}$/',
            'provider_http_status' => 'required_if:outcome,definitive_failure|nullable|integer|min:400|max:499|not_in:408',
            'failure_evidence' => 'required_if:outcome,definitive_failure|nullable|in:provider_rejected']);
        try { return response()->json($this->ledger->result($target, $data)); }
        catch (QueryException $e) { if ($e->getCode() === '23000') { abort(409, 'Publicación remota ya registrada.'); } throw $e; }
    }

    public function render(Request $request, int $target)
    {
        $this->active();
        $data = $request->validate(['attempt_id' => 'required|integer|min:1', 'lease' => 'required|string|size:64']);
        return response()->json($this->ledger->render($target, $data));
    }

    public function show(int $target)
    {
        return response()->json($this->ledger->status(PublicationTarget::where('executor', 'n8n')->where('destination_key', config('social_publication.destination'))->findOrFail($target)));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\PublicationTarget;
use App\Services\Publication\{AutomaticNewsPublication, SocialPublicationLedgerService};
use Illuminate\Http\Request;

class InstagramPublicationController extends SocialPublicationController
{
    public function __construct() { parent::__construct(new SocialPublicationLedgerService('instagram')); }

    public function candidates(Request $request)
    {
        $data = $request->validate(['content_kind' => 'nullable|in:news', 'provider' => 'nullable|in:instagram', 'per_page' => 'nullable|integer|min:1|max:50']);
        $automatic = app(AutomaticNewsPublication::class);
        $page = $automatic->candidates('instagram')->select(['news.id', 'news.title', 'news.slug', 'news.excerpt', 'news.editorial_status', 'news.publication_mode', 'news.published_at', 'news.estado'])
            ->paginate($data['per_page'] ?? 15);
        $page->setCollection($page->getCollection()->map(fn ($news) =>
            ['provider' => 'instagram', 'destination_key' => config('social_publication.instagram_destination')]
            + $this->ledger->snapshot($automatic->eligibility($news, 'instagram'), $news)));
        return response()->json($page);
    }

    public function claim(Request $request)
    {
        $data = $request->validate(['content_kind' => 'required|in:news', 'content_id' => 'required|integer|min:1',
            'provider' => 'required|in:instagram', 'destination_key' => 'required|string', 'n8n_execution_id' => 'nullable|string|max:96']);
        $request->merge(['request_key' => $request->header('Idempotency-Key')]);
        $key = $request->validate(['request_key' => 'required|uuid'])['request_key'];
        $result = $this->ledger->claim('news', $data['content_id'], $key, $data['n8n_execution_id'] ?? null);
        return response()->json($result, $result['replayed'] ? 200 : 201);
    }

    public function show(int $target)
    {
        return response()->json($this->ledger->status(PublicationTarget::where('executor', 'n8n')->where('provider', 'instagram')
            ->where('destination_key', config('social_publication.instagram_destination'))->findOrFail($target)));
    }

    public function container(Request $request, int $target)
    {
        $data = $request->validate(['attempt_id' => 'required|integer|min:1', 'lease' => 'required|string|size:64', 'container_id' => ['required', 'string', 'regex:/^[0-9]{5,32}$/D']]);
        return response()->json($this->ledger->instagramContainer($target, $data));
    }
}

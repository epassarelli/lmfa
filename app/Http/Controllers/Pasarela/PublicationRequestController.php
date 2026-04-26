<?php

namespace App\Http\Controllers\Pasarela;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\News;
use App\Models\PublicationRequest;
use App\Models\PublicationTarget;
use App\Models\SocialAccount;
use App\Jobs\Publication\PublishToProviderJob;
use App\Services\Publication\PublicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * PC-07-HU-01: Crear solicitud de publicación multicanal.
 *
 * Permite al publicador registrar su intención de publicar un evento o noticia
 * en uno o más canales (portal, redes propias, redes del portal).
 * El servicio PublicationService crea los targets y despacha los jobs.
 */
class PublicationRequestController extends Controller
{
    public function __construct(private PublicationService $publicationService) {}

    /**
     * Mostrar el formulario para solicitar publicación de un contenido.
     *
     * Query params esperados:
     *   content_type = 'event' | 'news'
     *   content_id   = int
     */
    public function create(Request $request)
    {
        $contentType = $request->query('content_type');
        $contentId   = $request->query('content_id');

        $content = null;
        if ($contentType && $contentId) {
            [$content] = $this->resolveContent($contentType, $contentId);
        }

        $user = Auth::user();

        $socialAccounts = SocialAccount::where('owner_type', get_class($user))
            ->where('owner_id', $user->id)
            ->where('status', 'active')
            ->orderBy('provider')
            ->get();

        return view('pasarela.publication_requests.create', [
            'content'        => $content,
            'contentType'    => $contentType,
            'contentId'      => $contentId,
            'socialAccounts' => $socialAccounts,
        ]);
    }

    /**
     * Registrar la solicitud de publicación multicanal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_type'        => ['required', 'string', Rule::in(['event', 'news'])],
            'content_id'          => ['required', 'integer', 'min:1'],
            'mode'                => ['required', 'string', Rule::in(['portal_only', 'social_only', 'full'])],
            'wants_portal_publish' => ['boolean'],
            'wants_portal_social'  => ['boolean'],
            'wants_own_social'     => ['boolean'],
            'scheduled_at'         => ['nullable', 'date', 'after:now'],
        ]);

        [$content, $modelClass] = $this->resolveContent(
            $validated['content_type'],
            $validated['content_id']
        );

        // Verificar que el contenido pertenece al usuario o su organización
        $this->authorizeContent($content);

        $publicationRequest = $this->publicationService->createRequest(
            contentType: $modelClass,
            contentId:   $validated['content_id'],
            options: [
                'mode'                 => $validated['mode'],
                'wants_portal_publish' => $validated['wants_portal_publish'] ?? false,
                'wants_portal_social'  => $validated['wants_portal_social'] ?? false,
                'wants_own_social'     => $validated['wants_own_social'] ?? false,
                'scheduled_at'         => $validated['scheduled_at'] ?? null,
            ]
        );

        return redirect()
            ->route('pasarela.publication-requests.show', $publicationRequest)
            ->with('success', 'Solicitud de publicación registrada correctamente.');
    }

    /**
     * Mostrar el detalle de una solicitud de publicación.
     */
    public function show(PublicationRequest $publicationRequest)
    {
        $this->authorizeRequest($publicationRequest);

        $publicationRequest->load(['targets', 'requester']);

        return view('pasarela.publication_requests.show', [
            'publicationRequest' => $publicationRequest,
        ]);
    }

    /**
     * Listar las solicitudes de publicación del usuario autenticado.
     */
    public function index()
    {
        $requests = PublicationRequest::where('requested_by', Auth::id())
            ->with('targets')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('pasarela.publication_requests.index', [
            'requests' => $requests,
        ]);
    }

    /**
     * Reintentar un target fallido manualmente.
     */
    public function retryTarget(PublicationRequest $publicationRequest, PublicationTarget $target)
    {
        $this->authorizeRequest($publicationRequest);

        if ($target->publication_request_id !== $publicationRequest->id) {
            abort(404);
        }

        if ($target->status !== 'failed') {
            return back()->with('error', 'Solo se pueden reintentar targets con estado fallido.');
        }

        $target->update(['status' => 'pending']);
        PublishToProviderJob::dispatch($target->id);

        return back()->with('success', 'Target re-encolado correctamente.');
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Resolver el contenido a partir del tipo y el id.
     * Retorna [$model, $className].
     */
    private function resolveContent(string $contentType, int|string $contentId): array
    {
        return match ($contentType) {
            'event' => [Event::findOrFail($contentId), Event::class],
            'news'  => [News::findOrFail($contentId), News::class],
            default => abort(422, 'Tipo de contenido no soportado.'),
        };
    }

    /**
     * Verificar que el usuario puede publicar ese contenido.
     * MVP: el contenido debe pertenecer a la organización del usuario
     * o haber sido creado por él.
     */
    private function authorizeContent($content): void
    {
        $user = Auth::user();

        // Organizaciones a las que pertenece el usuario
        $ownedOrgIds = \App\Models\OrganizationMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('organization_id')
            ->toArray();

        $belongsToOrg  = isset($content->organization_id) && in_array($content->organization_id, $ownedOrgIds);
        $createdByUser = isset($content->created_by) && $content->created_by === $user->id;

        if (!$belongsToOrg && !$createdByUser) {
            abort(403, 'No tenés permiso para publicar este contenido.');
        }
    }

    /**
     * Verificar que el usuario autenticado es el solicitante.
     */
    private function authorizeRequest(PublicationRequest $publicationRequest): void
    {
        if ($publicationRequest->requested_by !== Auth::id() && !Auth::user()->hasRole('administrador')) {
            abort(403);
        }
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DataDeletionRequest;
use App\Models\User;
use App\Support\CanonicalUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LegalController extends Controller
{
    private const PROVIDER_FACEBOOK = 'facebook';

    public function privacy()
    {
        return view('frontend.legal.privacy', $this->pageData(
            'Politica de privacidad | Mi Folklore Argentino',
            'Conoce como Mi Folklore Argentino trata los datos personales, el acceso con Facebook, cookies, analitica y solicitudes de eliminacion.',
            route('legal.privacy', [], false)
        ));
    }

    public function terms()
    {
        return view('frontend.legal.terms', $this->pageData(
            'Condiciones del servicio | Mi Folklore Argentino',
            'Consulta las condiciones de uso, autenticacion con terceros, propiedad intelectual y reglas generales del portal Mi Folklore Argentino.',
            route('legal.terms', [], false)
        ));
    }

    public function dataDeletion()
    {
        return view('frontend.legal.data-deletion', $this->pageData(
            'Eliminacion de datos | Mi Folklore Argentino',
            'Conoce como solicitar la eliminacion o desvinculacion de datos de Facebook en Mi Folklore Argentino y como consultar el estado de tu pedido.',
            route('legal.data-deletion', [], false)
        ));
    }

    public function deleteUserDataInstructions()
    {
        return view('frontend.legal.data-deletion', $this->pageData(
            'Eliminacion de datos | Mi Folklore Argentino',
            'Conoce como solicitar la eliminacion o desvinculacion de datos de Facebook en Mi Folklore Argentino y como consultar el estado de tu pedido.',
            route('legal.deleteuserdata.instructions', [], false)
        ));
    }

    public function handleDeleteUserData(Request $request): JsonResponse
    {
        $signedRequest = $request->string('signed_request')->toString();

        if ($signedRequest === '') {
            return response()->json([
                'message' => 'signed_request es obligatorio.',
            ], 422);
        }

        $secret = (string) config('services.facebook.client_secret');

        if ($secret === '') {
            Log::error('Facebook data deletion callback missing app secret configuration.');

            return response()->json([
                'message' => 'El servicio no esta disponible.',
            ], 503);
        }

        [$encodedSignature, $encodedPayload] = array_pad(explode('.', $signedRequest, 2), 2, null);

        if (! $encodedSignature || ! $encodedPayload) {
            return response()->json([
                'message' => 'signed_request invalido.',
            ], 422);
        }

        $signature = $this->base64UrlDecode($encodedSignature);
        $payloadJson = $this->base64UrlDecode($encodedPayload);

        if ($signature === null || $payloadJson === null) {
            return response()->json([
                'message' => 'signed_request invalido.',
            ], 422);
        }

        $payload = json_decode($payloadJson, true);

        if (! is_array($payload)) {
            return response()->json([
                'message' => 'Payload invalido.',
            ], 422);
        }

        $algorithm = Str::upper((string) ($payload['algorithm'] ?? ''));

        if ($algorithm !== 'HMAC-SHA256') {
            return response()->json([
                'message' => 'Algoritmo no soportado.',
            ], 422);
        }

        $expectedSignature = hash_hmac('sha256', $encodedPayload, $secret, true);

        if (! hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'message' => 'Firma invalida.',
            ], 422);
        }

        $facebookUserId = (string) ($payload['user_id'] ?? '');

        if ($facebookUserId === '') {
            return response()->json([
                'message' => 'user_id es obligatorio.',
            ], 422);
        }

        $externalUserHash = hash('sha256', self::PROVIDER_FACEBOOK.'|'.$facebookUserId);

        $deletionRequest = DataDeletionRequest::query()->firstOrCreate(
            [
                'provider' => self::PROVIDER_FACEBOOK,
                'external_user_hash' => $externalUserHash,
            ],
            [
                'confirmation_code' => Str::random(64),
                'status' => DataDeletionRequest::STATUS_PENDING,
                'requested_at' => now(),
            ]
        );

        if (! $deletionRequest->isTerminal()) {
            try {
                $this->processDeletionRequest($deletionRequest, $facebookUserId);
            } catch (\Throwable $exception) {
                $deletionRequest->forceFill([
                    'status' => DataDeletionRequest::STATUS_FAILED,
                    'completed_at' => now(),
                    'error_detail' => 'processing_error',
                ])->save();

                Log::error('Facebook data deletion processing failed.', [
                    'request_id' => $deletionRequest->id,
                ]);

                return response()->json([
                    'message' => 'No se pudo procesar la solicitud.',
                ], 500);
            }
        }

        return response()->json([
            'url' => CanonicalUrl::normalize(route('legal.deleteuserdata.status', [
                'confirmationCode' => $deletionRequest->confirmation_code,
            ], false), false),
            'confirmation_code' => $deletionRequest->confirmation_code,
        ]);
    }

    public function deletionStatus(string $confirmationCode)
    {
        $deletionRequest = DataDeletionRequest::query()
            ->where('confirmation_code', $confirmationCode)
            ->first();

        if (! $deletionRequest) {
            throw new NotFoundHttpException();
        }

        return view('frontend.legal.data-deletion-status', array_merge(
            $this->pageData(
                'Estado de eliminacion de datos | Mi Folklore Argentino',
                'Consulta el estado general de una solicitud de eliminacion de datos procesada por Mi Folklore Argentino.',
                route('legal.deleteuserdata.status', ['confirmationCode' => $deletionRequest->confirmation_code], false)
            ),
            [
                'deletionRequest' => $deletionRequest,
                'statusLabel' => $this->statusLabel($deletionRequest->status),
                'statusMessage' => $this->statusMessage($deletionRequest->status),
            ]
        ));
    }

    private function processDeletionRequest(DataDeletionRequest $deletionRequest, string $facebookUserId): void
    {
        DB::transaction(function () use ($deletionRequest, $facebookUserId): void {
            $deletionRequest->refresh();

            if ($deletionRequest->status === DataDeletionRequest::STATUS_COMPLETED) {
                return;
            }

            $deletionRequest->forceFill([
                'status' => DataDeletionRequest::STATUS_PROCESSING,
                'error_detail' => null,
            ])->save();

            $user = User::query()
                ->where('facebook_id', $facebookUserId)
                ->orWhereHas('socialAccounts', function ($query) use ($facebookUserId) {
                    $query->where('provider', self::PROVIDER_FACEBOOK)
                        ->where('account_external_id', $facebookUserId);
                })
                ->first();

            if ($user) {
                $deletionRequest->user()->associate($user);
                $deletionRequest->save();

                $user->socialAccounts()
                    ->where('provider', self::PROVIDER_FACEBOOK)
                    ->delete();

                $user->tokens()->delete();

                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $user->id)
                    ->delete();

                $user->forceFill([
                    'facebook_id' => null,
                    'remember_token' => null,
                    'last_login_at' => null,
                ]);

                if ($user->google_id === null) {
                    $user->forceFill([
                        'name' => 'Usuario eliminado '.$user->id,
                        'email' => sprintf(
                            'deleted-user-%d-%s@mifolkloreargentino.invalid',
                            $user->id,
                            Str::lower(Str::random(12))
                        ),
                        'email_verified_at' => null,
                        'password' => Hash::make(Str::random(40)),
                        'phone' => null,
                    ]);
                }

                $user->save();
            }

            $deletionRequest->forceFill([
                'status' => DataDeletionRequest::STATUS_COMPLETED,
                'completed_at' => now(),
                'requested_at' => $deletionRequest->requested_at ?: now(),
                'error_detail' => null,
            ])->save();
        });
    }

    private function pageData(string $metaTitle, string $metaDescription, string $canonical): array
    {
        return [
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'canonical' => CanonicalUrl::normalize($canonical, false),
            'contactEmail' => 'info@mifolkloreargentino.com',
            'lastUpdated' => '20 de agosto de 2026',
        ];
    }

    private function base64UrlDecode(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decoded !== false) {
            return $decoded;
        }

        $padding = 4 - (strlen($value) % 4);

        if ($padding < 4) {
            $value .= str_repeat('=', $padding);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            DataDeletionRequest::STATUS_PENDING => 'Pendiente',
            DataDeletionRequest::STATUS_PROCESSING => 'En proceso',
            DataDeletionRequest::STATUS_COMPLETED => 'Completada',
            DataDeletionRequest::STATUS_FAILED => 'Requiere revision',
            default => 'Pendiente',
        };
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            DataDeletionRequest::STATUS_PENDING => 'Tu solicitud fue recibida y esta pendiente de procesamiento.',
            DataDeletionRequest::STATUS_PROCESSING => 'Tu solicitud se esta procesando en este momento.',
            DataDeletionRequest::STATUS_COMPLETED => 'La solicitud fue procesada. Si habia una cuenta asociada, se desvincularon o anonimizaron los datos correspondientes.',
            DataDeletionRequest::STATUS_FAILED => 'La solicitud fue recibida y requiere una revision manual adicional.',
            default => 'Tu solicitud fue recibida.',
        };
    }
}

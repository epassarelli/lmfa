<?php

namespace App\Menu\Filters;

use App\Services\AdminAttentionService;
use Illuminate\Support\Facades\Auth;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

/**
 * Insignia con la cantidad de pendientes en los items del menú que declaran `'pending' => '<clave>'`
 * (claims, contacts, review, classifieds). Solo para administradores; sin insignia si no hay pendientes.
 */
class PendingBadgeFilter implements FilterInterface
{
    public function __construct(private AdminAttentionService $attention)
    {
    }

    public function transform($item)
    {
        if (! is_array($item) || empty($item['pending'])) {
            return $item;
        }

        $user = Auth::user();

        if (! $user || ! $user->hasRole('administrador')) {
            unset($item['label'], $item['label_color']);

            return $item;
        }

        $badge = $this->attention->badge($item['pending']);

        if ($badge['count'] > 0) {
            $item['label'] = $badge['count'];
            $item['label_color'] = $badge['color'];
        } else {
            unset($item['label'], $item['label_color']);
        }

        return $item;
    }
}

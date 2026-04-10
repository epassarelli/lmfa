<?php

namespace App\Services\Connectors;

use App\Models\PublicationTarget;

class ConnectorFactory
{
    /**
     * Resolve the right connector for a given provider.
     */
    public static function make(string $provider): BaseConnector
    {
        return match ($provider) {
            'facebook'      => new FacebookConnector(),
            'instagram'     => new InstagramConnector(),
            'telegram'      => new TelegramConnector(),
            'native_portal' => new NativePortalConnector(),
            default         => throw new \InvalidArgumentException("Connector not found for provider: {$provider}"),
        };
    }
}

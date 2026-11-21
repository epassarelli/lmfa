<?php

namespace App\Services\Publication;

use App\Models\{Album, Event, News};

/**
 * Registro único de tipos de contenido autopublicables: modelo y cola automática de cada uno.
 * Sumar un tipo (estándar social-autopublicacion-estandar, sección 7) empieza por acá.
 */
class SocialPublicationKinds
{
    public const MODELS = [
        'news' => News::class,
        'events' => Event::class,
        'albums' => Album::class,
    ];

    public const QUEUES = [
        'news' => AutomaticNewsPublication::class,
        'events' => AutomaticEventPublication::class,
        'albums' => AutomaticAlbumPublication::class,
    ];

    public static function exists(string $kind): bool
    {
        return isset(self::MODELS[$kind]);
    }

    /** @return class-string<\Illuminate\Database\Eloquent\Model> */
    public static function model(string $kind): string
    {
        abort_unless(self::exists($kind), 422, 'Tipo no permitido.');
        return self::MODELS[$kind];
    }

    public static function queue(string $kind): object
    {
        abort_unless(self::exists($kind), 422, 'Tipo no permitido.');
        return app(self::QUEUES[$kind]);
    }

    public static function kindOf(?string $class): ?string
    {
        return $class ? (array_search($class, self::MODELS, true) ?: null) : null;
    }

    /** Si la cola automática del tipo está habilitada en el servidor. */
    public static function automatic(string $kind): bool
    {
        return (bool) config('social_publication.'.$kind.'_automatic');
    }
}

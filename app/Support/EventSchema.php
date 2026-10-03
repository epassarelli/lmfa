<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Festival;

/**
 * JSON-LD schema.org/Event de la ficha pública. Solo declara datos cargados:
 * nunca infiere precio, disponibilidad ni organizador.
 */
final class EventSchema
{
    public static function forEvent(Event $event, string $url, ?string $image, string $description, ?Festival $festival = null): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->title,
            'url' => $url,
            'startDate' => $event->start_at?->toIso8601String(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => static::attendanceMode($event->modality),
            'location' => static::location($event),
            'description' => $description,
        ];

        if ($event->end_at && $event->start_at && $event->end_at->greaterThan($event->start_at)) {
            $schema['endDate'] = $event->end_at->toIso8601String();
        }

        if ($image) {
            $schema['image'] = [$image];
        }

        $performers = $event->interpretes->map(fn ($artist) => [
            '@type' => $artist->artist_type === 'soloist' ? 'Person' : 'MusicGroup',
            'name' => $artist->interprete,
            'url' => route('artista.show', $artist->slug),
        ])->values()->all();

        if ($performers !== []) {
            $schema['performer'] = $performers;
        }

        if ($offers = static::offers($event)) {
            $schema['offers'] = $offers;
        }

        if ($event->organization_id && $event->organization) {
            $schema['organizer'] = array_filter([
                '@type' => 'Organization',
                'name' => $event->organization->name,
                'url' => static::validUrl($event->organization->website),
            ]);
        }

        if ($festival) {
            $schema['superEvent'] = ['@type' => 'Festival', 'name' => $festival->title, 'url' => $festival->getUrl()];
        }

        return $schema;
    }

    public static function validUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true) ? $url : null;
    }

    private static function attendanceMode(?string $modality): string
    {
        return match (strtolower((string) $modality)) {
            'online', 'virtual' => 'https://schema.org/OnlineEventAttendanceMode',
            'hibrido', 'híbrido', 'mixto', 'mixed' => 'https://schema.org/MixedEventAttendanceMode',
            default => 'https://schema.org/OfflineEventAttendanceMode',
        };
    }

    private static function location(Event $event): array
    {
        $venue = $event->venue_id ? $event->venue : null;

        $address = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => filled($event->address) ? trim($event->address) : (filled($venue?->address) ? trim($venue->address) : null),
            'addressLocality' => filled($event->city) ? trim($event->city) : (filled($venue?->city) ? trim($venue->city) : null),
            'addressRegion' => $event->provincia?->nombre,
            'addressCountry' => 'AR',
        ]);

        return [
            '@type' => 'Place',
            'name' => $venue?->name ?: ($address['addressLocality'] ?? $address['addressRegion'] ?? 'Argentina'),
            'address' => $address,
        ];
    }

    private static function offers(Event $event): ?array
    {
        $ticketUrl = static::validUrl($event->ticket_url);
        $priceText = filled($event->price_text) ? trim($event->price_text) : null;

        if (! $ticketUrl && ! $priceText && ! $event->is_free) {
            return null;
        }

        return array_filter([
            '@type' => 'Offer',
            'url' => $ticketUrl,
            'price' => $event->is_free ? '0' : null,
            'priceCurrency' => $event->is_free ? 'ARS' : null,
            'description' => $priceText,
        ], fn ($value) => $value !== null);
    }
}

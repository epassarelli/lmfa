<?php

namespace App\Services;

use App\Models\PeniaProfile;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PeniaProfileService
{
    public function publish(PeniaProfile $profile): PeniaProfile
    {
        return $this->save($profile, ['editorial_status' => 'published']);
    }

    public function unpublish(PeniaProfile $profile): PeniaProfile
    {
        return $this->save($profile, [
            'editorial_status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function archive(PeniaProfile $profile): PeniaProfile
    {
        return $this->save($profile, ['editorial_status' => 'archived']);
    }

    public function save(PeniaProfile $profile, array $data): PeniaProfile
    {
        $payload = $this->normalize($profile, $data);
        $profile->fill(Arr::except($payload, ['event_ids']));

        if ($profile->editorial_status === 'published') {
            $this->ensurePublishable($profile);
            $profile->published_at ??= now();
        }

        $profile->save();

        if (array_key_exists('event_ids', $payload)) {
            $profile->events()->sync($payload['event_ids'] ?? []);
        }

        return $profile;
    }

    private function normalize(PeniaProfile $profile, array $data): array
    {
        $title = $data['title'] ?? $profile->title;

        if (filled($title) && blank($data['slug'] ?? $profile->slug)) {
            $data['slug'] = Str::slug($title);
        }

        if (array_key_exists('body', $data)) {
            $data['body'] = RichTextHeadingSanitizer::normalize($data['body']);
        }

        if (array_key_exists('source_urls', $data)) {
            $data['source_urls'] = array_values(array_filter($data['source_urls'] ?? []));
        }

        return $data;
    }

    private function ensurePublishable(PeniaProfile $profile): void
    {
        $missing = [];

        foreach (['title', 'body', 'province_id', 'venue_type', 'verification_method', 'verified_by_user_id'] as $field) {
            if (blank($profile->{$field})) {
                $missing[] = $field;
            }
        }

        if (empty($profile->source_urls)) {
            $missing[] = 'source_urls';
        }

        if (! $profile->hasCurrentVerification()) {
            $missing[] = 'current_verification';
        }

        if ($missing) {
            throw ValidationException::withMessages([
                'editorial_status' => ['No se puede publicar una Peña sin verificación editorial vigente: '.implode(', ', $missing).'.'],
            ]);
        }
    }
}

<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePeniaProfileRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['body']);
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('penia_profiles', 'slug')->ignore($this->route('penia_profile')?->id)],
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'sometimes|string',
            'province_id' => 'sometimes|exists:provincias,id',
            'locality_id' => 'nullable|exists:localities,id',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'venue_type' => ['sometimes', Rule::in(['penia', 'centro_cultural', 'gastronomico_cultural', 'otro'])],
            'opening_hours' => 'nullable|array',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'reservation_url' => 'nullable|url|max:255',
            'capacity' => 'nullable|integer|min:1',
            'accessibility_notes' => 'nullable|string',
            'regular_events_summary' => 'nullable|string',
            'admission_notes' => 'nullable|string',
            'source_urls' => 'sometimes|array|min:1',
            'source_urls.*' => 'url|max:2048',
            'verification_status' => ['sometimes', Rule::in(['pending', 'verified', 'outdated'])],
            'last_verified_at' => 'nullable|date|before_or_equal:now',
            'verified_by_user_id' => 'nullable|exists:users,id',
            'verification_method' => ['nullable', Rule::in(['official_source', 'direct_confirmation', 'editorial_visit'])],
            'editorial_status' => ['sometimes', Rule::in(['draft', 'approved', 'published', 'archived'])],
            'published_at' => 'nullable|date',
            'image_alt' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'event_ids' => 'sometimes|nullable|array',
            'event_ids.*' => 'integer|exists:events,id',
        ];
    }
}

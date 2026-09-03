<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PeniaProfileRequest extends FormRequest
{
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['body']);
        if (blank($this->input('slug')) && filled($this->input('title'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
        }
        if (is_string($this->input('source_urls'))) {
            $this->merge([
                'source_urls' => array_values(array_filter(array_map(
                    'trim',
                    preg_split('/\r\n|\r|\n/', $this->input('source_urls'))
                ))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255', 'slug' => ['nullable', 'string', 'max:255', Rule::unique('penia_profiles', 'slug')->ignore($this->route('penia_profile')?->id)],
            'excerpt' => 'nullable|string|max:1000', 'body' => 'required|string', 'province_id' => 'required|exists:provincias,id', 'locality_id' => 'nullable|exists:localities,id',
            'city' => 'nullable|string|max:255', 'address' => 'nullable|string|max:255', 'venue_type' => ['required', Rule::in(['penia', 'centro_cultural', 'gastronomico_cultural', 'otro'])],
            'phone' => 'nullable|string|max:255', 'email' => 'nullable|email|max:255', 'website' => 'nullable|url|max:255', 'reservation_url' => 'nullable|url|max:255', 'capacity' => 'nullable|integer|min:1',
            'accessibility_notes' => 'nullable|string', 'regular_events_summary' => 'nullable|string', 'admission_notes' => 'nullable|string', 'source_urls' => 'required|array|min:1', 'source_urls.*' => 'url|max:2048',
            'verification_status' => ['required', Rule::in(['pending', 'verified', 'outdated'])], 'last_verified_at' => 'nullable|date|before_or_equal:now', 'verified_by_user_id' => 'nullable|exists:users,id',
            'verification_method' => ['nullable', Rule::in(['official_source', 'direct_confirmation', 'editorial_visit'])], 'editorial_status' => ['required', Rule::in(['draft', 'approved', 'published', 'archived'])],
            'published_at' => 'nullable|date', 'seo_title' => 'nullable|string|max:255', 'meta_description' => 'nullable|string|max:320', 'event_ids' => 'nullable|array', 'event_ids.*' => 'integer|exists:events,id',
        ];
    }
}

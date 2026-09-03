<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\EnforcesEditorialProposalState;
use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RadioSignalRequest extends FormRequest
{
    use EnforcesEditorialProposalState;
    use NormalizesRichTextFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->enforceEditorialProposalState();
        $this->normalizeRichTextFields(['body']);

        if (blank($this->input('slug')) && filled($this->input('title'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
        }

        if (is_string($this->input('source_urls'))) {
            $this->merge(['source_urls' => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $this->input('source_urls')))))]);
        }

        if (is_array($this->input('channels'))) {
            $this->merge(['channels' => array_values(array_filter($this->input('channels'), fn ($channel) => collect($channel)->except(['id', 'is_primary', 'is_active'])->filter(fn ($value) => filled($value))->isNotEmpty()))]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('radio_signals', 'slug')->ignore($this->route('radio_signal')?->id)],
            'excerpt' => 'nullable|string|max:1000', 'body' => 'required|string',
            'editorial_focus' => ['required', Rule::in(['folklore', 'mixed'])],
            'transmission_modes' => 'required|array|min:1', 'transmission_modes.*' => Rule::in(['air', 'web', 'streaming']),
            'province_id' => 'nullable|exists:provincias,id', 'locality_id' => 'nullable|exists:localities,id', 'city' => 'nullable|string|max:255', 'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180', 'coverage_scope' => ['required', Rule::in(['local', 'provincial', 'regional', 'national', 'global'])], 'coverage_notes' => 'nullable|string',
            'phone' => 'nullable|string|max:255', 'email' => 'nullable|email|max:255', 'website' => 'nullable|url|max:2048', 'source_urls' => 'required|array|min:1', 'source_urls.*' => 'url|max:2048',
            'verification_status' => ['required', Rule::in(['pending', 'verified', 'outdated'])], 'last_verified_at' => 'nullable|date|before_or_equal:now', 'verified_by_user_id' => 'nullable|exists:users,id', 'verification_method' => ['nullable', Rule::in(['official_source', 'direct_confirmation', 'editorial_visit', 'manual'])],
            'editorial_status' => ['required', Rule::in(['draft', 'approved', 'published', 'archived'])], 'published_at' => 'nullable|date', 'featured_image_path' => 'nullable|string|max:255', 'image_alt' => 'nullable|string|max:255', 'seo_title' => 'nullable|string|max:255', 'meta_description' => 'nullable|string|max:320',
            'channels' => 'nullable|array', 'channels.*.id' => 'nullable|integer', 'channels.*.label' => 'required_with:channels|string|max:255', 'channels.*.channel_type' => ['required_with:channels', Rule::in(['frequency', 'stream', 'website', 'platform'])], 'channels.*.platform' => ['nullable', Rule::in(['sitio_web', 'stream_directo', 'youtube', 'facebook', 'twitch', 'tunein', 'radio_garden', 'spotify', 'otra_oficial'])], 'channels.*.frequency_band' => ['nullable', Rule::in(['AM', 'FM'])], 'channels.*.frequency' => 'nullable|string|max:50', 'channels.*.url' => 'nullable|url|max:2048', 'channels.*.is_primary' => 'nullable|boolean', 'channels.*.is_active' => 'nullable|boolean',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('channels', []) as $index => $channel) {
                if (($channel['channel_type'] ?? null) === 'frequency') {
                    if (blank($channel['frequency_band'] ?? null)) {
                        $validator->errors()->add("channels.{$index}.frequency_band", 'Indicá AM o FM.');
                    }
                    if (blank($channel['frequency'] ?? null)) {
                        $validator->errors()->add("channels.{$index}.frequency", 'Indicá la frecuencia.');
                    }
                } elseif (blank($channel['url'] ?? null)) {
                    $validator->errors()->add("channels.{$index}.url", 'Este tipo de canal requiere una URL oficial.');
                }
            }

            if ($this->filled('locality_id') && $this->filled('province_id')) {
                $belongsToProvince = \App\Models\Locality::query()
                    ->whereKey($this->integer('locality_id'))
                    ->where('province_id', $this->integer('province_id'))
                    ->exists();
                if (! $belongsToProvince) {
                    $validator->errors()->add('locality_id', 'La localidad no pertenece a la provincia seleccionada.');
                }
            }
        });
    }
}

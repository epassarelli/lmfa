<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\EnforcesEditorialProposalState;
use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RadioProgramRequest extends FormRequest
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

        if ($this->isMethod('post') && blank($this->input('slug')) && filled($this->input('title'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
        }

        if (is_string($this->input('source_urls'))) {
            $this->merge(['source_urls' => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $this->input('source_urls')))))]);
        }

        if (is_array($this->input('slots'))) {
            $this->merge(['slots' => array_values(array_filter($this->input('slots'), fn ($slot) => collect($slot)->except(['id', 'is_active'])->filter(fn ($value) => filled($value))->isNotEmpty()))]);
        }
    }

    public function rules(): array
    {
        $creating = $this->isMethod('post');
        $required = $creating ? 'required' : 'sometimes';
        $slugPresence = $creating ? 'nullable' : 'sometimes';

        return [
            'radio_signal_id' => 'nullable|exists:radio_signals,id', 'title' => [$required, 'string', 'max:255'],
            'slug' => [$slugPresence, 'string', 'max:255', Rule::unique('radio_programs', 'slug')->ignore($this->route('radio_program')?->id)],
            'excerpt' => 'nullable|string|max:1000', 'body' => [$required, 'string'], 'is_folklore' => [$required, 'boolean'],
            'platform' => ['nullable', Rule::in(['sitio_web', 'stream_directo', 'youtube', 'facebook', 'twitch', 'tunein', 'radio_garden', 'spotify', 'otra_oficial'])], 'listening_url' => 'nullable|url|max:2048',
            'source_urls' => [$required, 'array', 'min:1'], 'source_urls.*' => 'url|max:2048', 'verification_status' => [$required, Rule::in(['pending', 'verified', 'outdated'])], 'last_verified_at' => 'nullable|date|before_or_equal:now', 'verified_by_user_id' => 'nullable|exists:users,id', 'verification_method' => ['nullable', Rule::in(['official_source', 'direct_confirmation', 'editorial_visit', 'manual'])],
            'editorial_status' => [$required, Rule::in(['draft', 'approved', 'published', 'archived'])], 'published_at' => 'nullable|date', 'seo_title' => 'nullable|string|max:255', 'meta_description' => 'nullable|string|max:320',
            'slots' => 'nullable|array', 'slots.*.id' => 'nullable|integer', 'slots.*.weekday' => 'required_with:slots|integer|between:0,6', 'slots.*.starts_at' => 'required_with:slots|date_format:H:i', 'slots.*.ends_at' => 'nullable|date_format:H:i|after:slots.*.starts_at', 'slots.*.timezone' => 'nullable|timezone', 'slots.*.is_active' => 'nullable|boolean',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $seen = [];
            foreach ($this->input('slots', []) as $index => $slot) {
                $key = ($slot['weekday'] ?? '').'|'.($slot['starts_at'] ?? '');
                if (isset($seen[$key])) {
                    $validator->errors()->add("slots.{$index}.starts_at", 'La franja semanal está repetida.');
                }
                $seen[$key] = true;
            }
        });
    }
}

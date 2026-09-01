<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreSongRequest extends FormRequest
{
  use NormalizesRichTextFields;

  protected function prepareForValidation(): void
  {
    $this->normalizeRichTextFields(['letra']);

    if (blank($this->input('slug')) && filled($this->input('cancion'))) {
      $this->merge(['slug' => Str::slug((string) $this->input('cancion'))]);
    }
  }

  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'cancion' => 'required|string|max:255',
      'slug' => ['required', 'string', 'max:255', Rule::unique('canciones', 'slug')->where(fn ($query) => $query->where('interprete_id', $this->input('interprete_id')))],
      'letra' => 'nullable|string',
      'excerpt' => 'nullable|string|max:1000',
      'composer' => 'nullable|string|max:255',
      'lyricist' => 'nullable|string|max:255',
      'rights_status' => 'nullable|in:unknown,authorized,licensed,public_domain,not_available',
      'lyrics_source_url' => 'nullable|url|max:2048',
      'is_instrumental' => 'nullable|boolean',
      'seo_title' => 'nullable|string|max:255',
      'meta_description' => 'nullable|string|max:320',
      'youtube' => 'nullable|string',
      'spotify' => 'nullable|string',
      'interprete_id' => 'required|exists:interpretes,id',
      'publicar' => 'nullable|date',
      'user_id' => 'nullable|exists:users,id',
      'estado' => 'nullable|boolean',
      'album_ids' => 'nullable|array',
      'album_ids.*' => 'integer|exists:albunes,id',
    ];
  }
}

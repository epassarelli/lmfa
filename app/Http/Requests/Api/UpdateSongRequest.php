<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSongRequest extends FormRequest
{
  use NormalizesRichTextFields;

  protected function prepareForValidation(): void
  {
    $this->normalizeRichTextFields(['letra']);
  }

  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'cancion' => 'sometimes|string|max:255',
      'slug' => ['sometimes', 'string', 'max:255', Rule::unique('canciones', 'slug')->where(fn ($query) => $query->where('interprete_id', $this->input('interprete_id', $this->route('song')?->interprete_id)))->ignore($this->route('song')?->id)],
      'letra' => 'sometimes|nullable|string',
      'excerpt' => 'sometimes|nullable|string|max:1000',
      'composer' => 'sometimes|nullable|string|max:255',
      'lyricist' => 'sometimes|nullable|string|max:255',
      'rights_status' => 'sometimes|nullable|in:unknown,authorized,licensed,public_domain,not_available',
      'lyrics_source_url' => 'sometimes|nullable|url|max:2048',
      'is_instrumental' => 'sometimes|boolean',
      'seo_title' => 'sometimes|nullable|string|max:255',
      'meta_description' => 'sometimes|nullable|string|max:320',
      'youtube' => 'nullable|string',
      'spotify' => 'nullable|string',
      'interprete_id' => 'sometimes|exists:interpretes,id',
      'publicar' => 'nullable|date',
      'user_id' => 'sometimes|exists:users,id',
      'estado' => 'nullable|boolean',
      'album_ids' => 'sometimes|nullable|array',
      'album_ids.*' => 'integer|exists:albunes,id',
    ];
  }
}

<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRichTextFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancionRequest extends FormRequest
{
  use NormalizesRichTextFields;

  protected function prepareForValidation(): void
  {
    $this->normalizeRichTextFields(['letra']);
  }

  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'cancion' => 'required|string|max:255',
      'slug' => ['nullable', 'string', 'max:255', Rule::unique('canciones', 'slug')->where(fn ($query) => $query->where('interprete_id', $this->input('interprete_id')))->ignore($this->route('cancion')?->id)],
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
      // 'album_id' => 'required|exists:albunes,id',
      'publicar' => 'nullable|date',
    ];
  }
}

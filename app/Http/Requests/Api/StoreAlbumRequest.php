<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreAlbumRequest extends FormRequest
{
  protected function prepareForValidation(): void
  {
    if (blank($this->input('slug')) && filled($this->input('album'))) {
      $this->merge(['slug' => Str::slug((string) $this->input('album'))]);
    }
  }

  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'album' => 'required|string|max:255',
      'album_type' => 'nullable|in:studio,live,compilation,ep,single,other',
      'slug' => ['required', 'string', 'max:255', Rule::unique('albunes', 'slug')->where(fn ($query) => $query->where('interprete_id', $this->input('interprete_id')))],
      'anio' => 'required|digits:4',
      'excerpt' => 'nullable|string|max:1000',
      'label' => 'nullable|string|max:255',
      'release_date' => 'nullable|date',
      'seo_title' => 'nullable|string|max:255',
      'meta_description' => 'nullable|string|max:320',
      'image_alt' => 'nullable|string|max:255',
      'spotify' => 'nullable|string',
      'featured_image_path' => 'nullable|string|max:2048',
      'featured_image_url' => 'nullable|url|max:2048',
      'interprete_id' => 'required|exists:interpretes,id',
      'foto' => 'nullable|string',
      'user_id' => 'nullable|exists:users,id',
      'estado' => 'nullable|boolean',
    ];
  }
}

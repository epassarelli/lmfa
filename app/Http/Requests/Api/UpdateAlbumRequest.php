<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlbumRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'album' => 'sometimes|string|max:255',
      'album_type' => 'sometimes|nullable|in:studio,live,compilation,ep,single,other',
      'slug' => ['sometimes', 'string', 'max:255', Rule::unique('albunes', 'slug')->where(fn ($query) => $query->where('interprete_id', $this->input('interprete_id', $this->route('album')?->interprete_id)))->ignore($this->route('album')?->id)],
      'anio' => 'sometimes|digits:4',
      'excerpt' => 'sometimes|nullable|string|max:1000',
      'label' => 'sometimes|nullable|string|max:255',
      'release_date' => 'sometimes|nullable|date',
      'seo_title' => 'sometimes|nullable|string|max:255',
      'meta_description' => 'sometimes|nullable|string|max:320',
      'image_alt' => 'sometimes|nullable|string|max:255',
      'spotify' => 'nullable|string',
      'featured_image_path' => 'sometimes|nullable|string|max:2048',
      'featured_image_url' => 'sometimes|nullable|url|max:2048',
      'interprete_id' => 'sometimes|exists:interpretes,id',
      'foto' => 'nullable|string',
      'user_id' => 'sometimes|exists:users,id',
      'estado' => 'nullable|boolean',
    ];
  }
}

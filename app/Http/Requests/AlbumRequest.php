<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlbumRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'album' => 'required|string|max:255',
      'album_type' => 'nullable|in:studio,live,compilation,ep,single,other',
      'slug' => ['nullable', 'string', 'max:255', Rule::unique('albunes', 'slug')->where(fn ($query) => $query->where('interprete_id', $this->input('interprete_id')))->ignore($this->route('album')?->id)],
      'anio' => 'required|digits:4',
      'excerpt' => 'nullable|string|max:1000',
      'label' => 'nullable|string|max:255',
      'release_date' => 'nullable|date',
      'seo_title' => 'nullable|string|max:255',
      'meta_description' => 'nullable|string|max:320',
      'image_alt' => 'nullable|string|max:255',
      'spotify' => 'nullable|string',
      'interprete_id' => 'required|exists:interpretes,id',
      'foto' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
    ];
  }
}

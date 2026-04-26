<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'title'        => 'required|string|max:255',
      'body'         => 'required|string',
      'start_at'     => 'required|date',
      'published_at' => 'nullable|date',
      'city'         => 'nullable|string|max:255',
      'address'      => 'nullable|string|max:255',
      'province_id'  => 'nullable|exists:provincias,id',
      'interprete_id'=> 'nullable|exists:interpretes,id',
      'ticket_url'   => 'nullable|url|max:255',
      'price_text'   => 'nullable|string|max:100',
      'is_free'      => 'nullable|boolean',
      'slug'         => 'nullable|string|max:255|unique:events,slug,' . ($this->route('event')?->id ?? $this->route('show')?->id ?? ''),
      'estado'       => 'nullable|integer|in:0,1',
      'foto'         => 'nullable|image|max:5120',
    ];
  }
}

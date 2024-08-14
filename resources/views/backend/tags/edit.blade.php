{{-- resources/views/admin/tags/edit.blade.php --}}
@extends('backend.tags.form')

@section('form_action')
  <form action="{{ route('backend.tags.update', $tag->id) }}" method="POST">
    @method('PUT')
  @endsection

  @section('form_values')
    value="{{ old('name', $tag->name) }}"
  @endsection

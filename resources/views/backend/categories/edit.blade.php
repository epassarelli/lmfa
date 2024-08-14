{{-- resources/views/admin/categories/edit.blade.php --}}
@extends('backend.categories.form')

@section('form_action')
  <form action="{{ route('backend.categories.update', $category->id) }}" method="POST">
    @method('PUT')
  @endsection

  @section('form_values')
    value="{{ old('name', $category->name) }}"
  @endsection

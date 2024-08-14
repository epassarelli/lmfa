{{-- resources/views/admin/categories/create.blade.php --}}
@extends('backend.categories.form')

@section('form_action')
  <form action="{{ route('backend.categories.store') }}" method="POST">
  @endsection

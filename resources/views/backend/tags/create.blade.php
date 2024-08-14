{{-- resources/views/admin/tags/create.blade.php --}}
@extends('backend.tags.form')

@section('form_action')
  <form action="{{ route('backend.tags.store') }}" method="POST">
  @endsection

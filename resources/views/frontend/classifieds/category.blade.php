@extends('layouts.app')

@section('title', 'Categories')

@section('content')
  <div class="container mt-4">
    <h1>Categories</h1>
    <div class="list-group">
      @foreach ($categories as $category)
        <a href="{{ route('classifieds.category', $category) }}" class="list-group-item list-group-item-action">
          {{ $category->name }}
        </a>
      @endforeach
    </div>
  </div>
@endsection

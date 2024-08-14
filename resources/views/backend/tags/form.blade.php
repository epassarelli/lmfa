@extends('adminlte::page')

@section('title', isset($category) ? 'Edit Category' : 'Create Category')

@section('content_header')
  <h1>{{ isset($category) ? 'Edit Category' : 'Create Category' }}</h1>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <form action="{{ isset($category) ? route('categories.update', $category) : route('categories.store') }}"
        method="POST">
        @csrf
        @if (isset($category))
          @method('PUT')
        @endif

        <div class="form-group">
          <label for="name">Name</label>
          <input type="text" name="name" id="name" class="form-control"
            value="{{ old('name', $category->name ?? '') }}" required>
          @error('name')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="slug">Slug</label>
          <input type="text" name="slug" id="slug" class="form-control"
            value="{{ old('slug', $category->slug ?? '') }}" required>
          @error('slug')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
      </form>
    </div>
  </div>
@endsection

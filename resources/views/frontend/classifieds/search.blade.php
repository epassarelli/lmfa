@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
  <div class="container mt-4">
    <h1>Search Results</h1>
    <form action="{{ route('classifieds.search') }}" method="GET" class="mb-4">
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="category_id">Category</label>
            <select name="category_id" id="category_id" class="form-control">
              <option value="">Select Category</option>
              @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="tag_id">Tag</label>
            <select name="tag_id" id="tag_id" class="form-control">
              <option value="">Select Tag</option>
              @foreach ($tags as $tag)
                <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                  {{ $tag->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
      </div>
    </form>
    <div class="row">
      @foreach ($classifieds as $classified)
        <div class="col-md-4 mb-4">
          <div class="card">
            <img src="{{ $classified->images->first()->image_path ?? 'placeholder.jpg' }}" class="card-img-top"
              alt="{{ $classified->title }}">
            <div class="card-body">
              <h5 class="card-title">{{ $classified->title }}</h5>
              <p class="card-text">{{ Str::limit($classified->description, 100) }}</p>
              <a href="{{ route('classifieds.show', $classified) }}" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    {{ $classifieds->links() }}
  </div>
@endsection

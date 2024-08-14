@extends('adminlte::page')

@section('title', isset($classified) ? 'Edit Classified' : 'Create Classified')

@section('content_header')
  <h1>{{ isset($classified) ? 'Edit Classified' : 'Create Classified' }}</h1>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <form action="{{ isset($classified) ? route('classifieds.update', $classified) : route('classifieds.store') }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @if (isset($classified))
          @method('PUT')
        @endif

        <div class="form-group">
          <label for="category_id">Category</label>
          <select name="category_id" id="category_id" class="form-control" required>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}"
                {{ isset($classified) && $classified->category_id == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
              </option>
            @endforeach
          </select>
          @error('category_id')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="title">Title</label>
          <input type="text" name="title" id="title" class="form-control"
            value="{{ old('title', $classified->title ?? '') }}" required>
          @error('title')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="description">Description</label>
          <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description', $classified->description ?? '') }}</textarea>
          @error('description')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="price">Price</label>
          <input type="text" name="price" id="price" class="form-control"
            value="{{ old('price', $classified->price ?? '') }}">
          @error('price')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="location">Location</label>
          <input type="text" name="location" id="location" class="form-control"
            value="{{ old('location', $classified->location ?? '') }}" required>
          @error('location')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="contact_info">Contact Info</label>
          <input type="text" name="contact_info" id="contact_info" class="form-control"
            value="{{ old('contact_info', $classified->contact_info ?? '') }}" required>
          @error('contact_info')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="expiration_date">Expiration Date</label>
          <input type="date" name="expiration_date" id="expiration_date" class="form-control"
            value="{{ old('expiration_date', $classified->expiration_date ?? '') }}">
          @error('expiration_date')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="tags">Tags</label>
          <select name="tags[]" id="tags" class="form-control" multiple>
            @foreach ($tags as $tag)
              <option value="{{ $tag->id }}"
                {{ isset($classified) && $classified->tags->contains($tag->id) ? 'selected' : '' }}>
                {{ $tag->name }}
              </option>
            @endforeach
          </select>
          @error('tags')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="images">Images</label>
          <input type="file" name="images[]" id="images" class="form-control" multiple>
          @error('images.*')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('classifieds.index') }}" class="btn btn-secondary">Cancel</a>
      </form>
    </div>
  </div>
@endsection

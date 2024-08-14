@extends('layouts.app')

@section('title', $classified->title)

@section('content')
  <div class="container mt-4">
    <h1>{{ $classified->title }}</h1>
    <div class="row">
      <div class="col-md-8">
        <div class="card mb-4">
          <img src="{{ $classified->images->first()->image_path ?? 'placeholder.jpg' }}" class="card-img-top"
            alt="{{ $classified->title }}">
          <div class="card-body">
            <h5 class="card-title">{{ $classified->title }}</h5>
            <p class="card-text">{{ $classified->description }}</p>
            <p class="card-text"><strong>Price:</strong> {{ $classified->price }}</p>
            <p class="card-text"><strong>Location:</strong> {{ $classified->location }}</p>
            <p class="card-text"><strong>Contact:</strong> {{ $classified->contact_info }}</p>
            <p class="card-text"><strong>Expiration Date:</strong> {{ $classified->expiration_date->format('d M, Y') }}
            </p>
            <p class="card-text"><strong>Category:</strong> {{ $classified->category->name }}</p>
            <p class="card-text"><strong>Tags:</strong>
              @foreach ($classified->tags as $tag)
                <span class="badge bg-primary">{{ $tag->name }}</span>
              @endforeach
            </p>
          </div>
        </div>
      </div>
    </div>
    <a href="{{ route('classifieds.index') }}" class="btn btn-secondary">Back to List</a>
  </div>
@endsection

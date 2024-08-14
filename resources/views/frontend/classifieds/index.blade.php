@extends('layouts.app')

@section('title', 'Classifieds')

@section('content')
    <div class="container mt-4">
        <h1>Classifieds</h1>
        <div class="row">
            @foreach($classifieds as $classified)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="{{ $classified->images->first()->image_path ?? 'placeholder.jpg' }}" class="card-img-top" alt="{{ $classified->title }}">
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

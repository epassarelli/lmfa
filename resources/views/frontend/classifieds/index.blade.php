@extends('layouts.app')

@section('title', 'Classifieds')

@section('content')
    <div class="container mt-4">
        <h1>Classifieds</h1>
        <div class="row">
            @foreach($classifieds as $classified)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        @if ($classified->images->isNotEmpty())
                            <x-optimized-image :image="$classified->images->first()" variant="card" class="card-img-top" />
                        @else
                            <img src="{{ asset('placeholder.jpg') }}" class="card-img-top" alt="{{ $classified->title }}">
                        @endif
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

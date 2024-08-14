@extends('adminlte::page')

@section('title', 'Classifieds')

@section('content_header')
  <h1>Classifieds</h1>
  <a href="{{ route('classifieds.create') }}" class="btn btn-primary">Create Classified</a>
@endsection

@section('content')
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Category</th>
        <th>Location</th>
        <th>Price</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($classifieds as $classified)
        <tr>
          <td>{{ $classified->id }}</td>
          <td>{{ $classified->title }}</td>
          <td>{{ $classified->category->name }}</td>
          <td>{{ $classified->location }}</td>
          <td>{{ $classified->price }}</td>
          <td>
            <a href="{{ route('classifieds.edit', $classified) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('classifieds.destroy', $classified) }}" method="POST" style="display:inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endsection

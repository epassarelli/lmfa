@extends('adminlte::page')

@section('title', 'Tags')

@section('content_header')
  <h1>Tags</h1>
  <a href="{{ route('tags.create') }}" class="btn btn-primary">Create Tag</a>
@endsection

@section('content')
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($tags as $tag)
        <tr>
          <td>{{ $tag->id }}</td>
          <td>{{ $tag->name }}</td>
          <td>
            <a href="{{ route('tags.edit', $tag) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('tags.destroy', $tag) }}" method="POST" style="display:inline;">
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

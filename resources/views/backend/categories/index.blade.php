@extends('adminlte::page')

@section('title', 'Categories')

@section('content_header')
  <h1>Categories</h1>
  <a href="{{ route('categories.create') }}" class="btn btn-primary">Create Category</a>
@endsection

@section('content')
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Slug</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($categories as $category)
        <tr>
          <td>{{ $category->id }}</td>
          <td>{{ $category->name }}</td>
          <td>{{ $category->slug }}</td>
          <td>
            <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('categories.destroy', $category) }}" method="POST" style="display:inline;">
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

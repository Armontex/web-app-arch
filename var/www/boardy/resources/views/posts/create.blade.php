@extends('layouts.app')

@section('title', 'Создать пост | Boardy')

@section('content')
  <div class="mb-3">
    <a class="text-decoration-none" href="{{ route('posts.index') }}">← Все посты</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h1 class="h2 mb-4">Создать пост</h1>

      <form action="{{ route('posts.store') }}" method="post">
        @csrf

        <div class="mb-3">
          <label class="form-label" for="post-title">Заголовок</label>
          <input
            class="form-control @error('title') is-invalid @enderror"
            id="post-title"
            name="title"
            type="text"
            value="{{ old('title') }}"
            maxlength="200"
            required
          >
          @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-4">
          <label class="form-label" for="post-body">Текст</label>
          <textarea
            class="form-control @error('body') is-invalid @enderror"
            id="post-body"
            name="body"
            rows="8"
            required
          >{{ old('body') }}</textarea>
          @error('body')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <button class="btn btn-primary" type="submit">Опубликовать</button>
      </form>
    </div>
  </div>
@endsection

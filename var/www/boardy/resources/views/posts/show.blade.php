@extends('layouts.app')

@section('title', $post->title . ' | Boardy')

@section('content')
  <div class="mb-3">
    <a class="text-decoration-none" href="{{ route('posts.index') }}">← Все посты</a>
  </div>

  <article class="card shadow-sm mb-4">
    <div class="card-body">
      <h1 class="h2 mb-3">{{ $post->title }}</h1>

      <div class="text-body-secondary small mb-4">
        Автор: {{ $post->author->name }}
        <span class="mx-2">·</span>
        <time datetime="{{ $post->created_at->toISOString() }}">
          {{ $post->created_at->format('d.m.Y H:i') }}
        </time>
      </div>

      <div class="fs-5 lh-lg">
        {!! nl2br(e($post->body)) !!}
      </div>

      @can('update', $post)
        <div class="d-flex gap-2 mt-4">
          <a class="btn btn-outline-primary" href="{{ route('posts.edit', $post) }}">Редактировать</a>
          <form action="{{ route('posts.destroy', $post) }}" method="post">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" type="submit">Удалить</button>
          </form>
        </div>
      @endcan
    </div>
  </article>
@endsection

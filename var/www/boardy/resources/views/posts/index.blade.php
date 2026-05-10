@extends('layouts.app')

@section('title', 'Все посты | Boardy')

@section('content')
  <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
    <h1 class="h2 mb-0">Все посты</h1>
    <a class="btn btn-primary" href="{{ route('posts.create') }}">Создать пост</a>
  </div>

  <div class="vstack gap-3">
    @forelse ($posts as $post)
      <article class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between gap-3 mb-2">
            <h2 class="h5 mb-0">
              <a class="text-decoration-none" href="{{ route('posts.show', $post) }}">
                {{ $post->title }}
              </a>
            </h2>
            <time class="text-body-secondary small" datetime="{{ $post->created_at->toISOString() }}">
              {{ $post->created_at->format('d.m.Y H:i') }}
            </time>
          </div>

          <div class="text-body-secondary small mb-3">
            Автор: {{ $post->author->name }}
          </div>

          <p class="mb-0">{{ Str::limit($post->body, 240) }}</p>
        </div>
      </article>
    @empty
      <div class="card">
        <div class="card-body text-body-secondary">Постов пока нет.</div>
      </div>
    @endforelse
  </div>

  <div class="mt-4">
    {{ $posts->links() }}
  </div>
@endsection

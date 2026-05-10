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

  <section class="card shadow-sm">
    <div class="card-body">
      <h2 class="h4 mb-3">Комментарии</h2>

      @forelse ($post->comments as $comment)
        <div class="border-bottom pb-3 mb-3">
          <div class="d-flex justify-content-between gap-3 mb-2">
            <strong>{{ $comment->author->name }}</strong>
            <time class="text-body-secondary small" datetime="{{ $comment->created_at->toISOString() }}">
              {{ $comment->created_at->format('d.m.Y H:i') }}
            </time>
          </div>
          <p class="mb-0">{{ $comment->body }}</p>
        </div>
      @empty
        <p class="text-body-secondary mb-0">Комментариев пока нет.</p>
      @endforelse

      @auth
        <form class="mt-4" action="{{ route('comments.store') }}" method="post">
          @csrf
          <input type="hidden" name="post_id" value="{{ $post->id }}">

          <div class="mb-3">
            <label class="form-label" for="comment-body">Новый комментарий</label>
            <textarea
              class="form-control @error('body') is-invalid @enderror"
              id="comment-body"
              name="body"
              rows="4"
              required
            >{{ old('body') }}</textarea>
            @error('body')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <button class="btn btn-primary" type="submit">Отправить</button>
        </form>
      @else
        <div class="alert alert-secondary mt-4 mb-0">
          Чтобы оставить комментарий, войдите в аккаунт.
        </div>
      @endauth
    </div>
  </section>
@endsection

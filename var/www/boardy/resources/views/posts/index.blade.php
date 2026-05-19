@extends('layouts.app')

@section('title', 'Все посты | Boardy')

@section('content')
  <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
    <h1 class="h2 mb-0">Все посты</h1>
    <a class="btn btn-primary" href="{{ route('posts.create') }}">Создать пост</a>
  </div>

  <div id="posts-feed" class="vstack gap-3">
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

@section('scripts')
  <script>
    const wsUrl = @json(app()->environment('production') ? 'wss://api.'.parse_url(config('app.url'), PHP_URL_HOST).'/ws' : 'ws://localhost:8001/ws');

    function connect() {
      const ws = new WebSocket(wsUrl);

      ws.onmessage = (event) => {
        const message = JSON.parse(event.data);

        if (message.type === 'new_post') {
          prependPost(message.post);
        }
      };

      ws.onclose = () => {
        setTimeout(connect, 3000);
      };
    }

    function prependPost(post) {
      const feed = document.getElementById('posts-feed');

      if (!feed) {
        return;
      }

      const article = document.createElement('article');
      article.className = 'card shadow-sm';
      article.innerHTML = `
        <div class="card-body">
          <div class="d-flex justify-content-between gap-3 mb-2">
            <h2 class="h5 mb-0">
              <a class="text-decoration-none" href="/posts/${encodeURIComponent(post.id)}">
                ${escapeHtml(post.title)}
              </a>
            </h2>
            <time class="text-body-secondary small" datetime="${escapeHtml(post.created_at)}">
              ${formatDate(post.created_at)}
            </time>
          </div>

          <div class="text-body-secondary small mb-3">
            Автор: ${escapeHtml(post.author)}
          </div>

          <p class="mb-0">${escapeHtml(limitText(post.body, 240))}</p>
        </div>
      `;

      feed.prepend(article);
    }

    function escapeHtml(value) {
      const div = document.createElement('div');
      div.textContent = value ?? '';
      return div.innerHTML;
    }

    function formatDate(value) {
      const date = new Date(value);

      if (Number.isNaN(date.getTime())) {
        return '';
      }

      return date.toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    }

    function limitText(value, length) {
      const text = value ?? '';

      if (text.length <= length) {
        return text;
      }

      return `${text.slice(0, length - 1)}…`;
    }

    connect();
  </script>
@endsection

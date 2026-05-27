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

  <section
    class="card shadow-sm"
    data-comments-root
    data-post-id="{{ $post->id }}"
    data-author-id="{{ auth()->id() }}"
    data-author-name="{{ auth()->user()?->name }}"
    data-access-token="{{ app()->environment('local') ? auth()->user()?->createToken('lab14-comments')->accessToken : '' }}"
  >
    <div class="card-body">
      <h2 class="h4 mb-3">Комментарии</h2>

      <div class="vstack gap-3" data-comments-list>
        <p class="text-body-secondary mb-0" data-comments-empty>Комментариев пока нет.</p>
      </div>

      @auth
        <form class="mt-4" data-comment-form>
          <div class="mb-3">
            <label class="form-label" for="comment-body">Новый комментарий</label>
            <textarea
              class="form-control"
              id="comment-body"
              name="body"
              rows="4"
              required
            ></textarea>
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

@section('scripts')
  <script type="module">
    const apiOrigin = ['localhost', '127.0.0.1'].includes(window.location.hostname)
      ? 'http://127.0.0.1:8001'
      : `https://api.${window.location.hostname}`;
    const wsUrl = ['localhost', '127.0.0.1'].includes(window.location.hostname)
      ? 'ws://127.0.0.1:8001/ws'
      : `wss://api.${window.location.hostname}/ws`;

    const root = document.querySelector('[data-comments-root]');
    const postId = Number(root.dataset.postId);
    const currentUserId = Number(root.dataset.authorId);
    const authorName = root.dataset.authorName || 'Boardy User';
    const accessToken = root.dataset.accessToken;
    const list = root.querySelector('[data-comments-list]');
    const empty = root.querySelector('[data-comments-empty]');
    const form = root.querySelector('[data-comment-form]');

    const comments = new Map();

    if (accessToken) {
      sessionStorage.setItem('access_token', accessToken);
    }

    function renderComment(comment) {
      let item = list.querySelector(`[data-comment-id="${comment.id}"]`);

      if (!item) {
        item = document.createElement('div');
        item.className = 'border-bottom pb-3';
        item.dataset.commentId = comment.id;
        list.append(item);
      }

      const canManage = Number(comment.author_id) === currentUserId;

      item.innerHTML = `
        <div class="d-flex justify-content-between gap-3 mb-2">
          <strong>${escapeHtml(comment.author_name)}</strong>
          <time class="text-body-secondary small" datetime="${escapeHtml(comment.created_at)}">
            ${formatDate(comment.created_at)}
          </time>
        </div>
        <p class="mb-2" data-comment-body>${escapeHtml(comment.body)}</p>
        ${canManage ? `
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" type="button" data-comment-edit>Изменить</button>
            <button class="btn btn-sm btn-outline-danger" type="button" data-comment-delete>Удалить</button>
          </div>
        ` : ''}
      `;
    }

    function refreshEmptyState() {
      empty.hidden = comments.size > 0;
    }

    function upsertComment(comment) {
      if (Number(comment.post_id) !== postId) {
        return;
      }

      comments.set(Number(comment.id), comment);
      renderComment(comment);
      refreshEmptyState();
    }

    function removeComment(comment) {
      if (Number(comment.post_id) !== postId) {
        return;
      }

      comments.delete(Number(comment.id));
      list.querySelector(`[data-comment-id="${comment.id}"]`)?.remove();
      refreshEmptyState();
    }

    async function loadComments() {
      const response = await fetch(`${apiOrigin}/api/posts/${postId}/comments`);
      const data = await response.json();

      data.items.forEach(upsertComment);
      refreshEmptyState();
    }

    async function sendComment(body) {
      const response = await window.boardyAuth.authedFetch(`${apiOrigin}/api/posts/${postId}/comments`, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          body,
          author_name: authorName,
        }),
      });

      if (!response.ok) {
        throw new Error(`Create failed: ${response.status}`);
      }
    }

    async function updateComment(id, body) {
      const response = await window.boardyAuth.authedFetch(`${apiOrigin}/api/comments/${id}`, {
        method: 'PUT',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ body }),
      });

      if (!response.ok) {
        throw new Error(`Update failed: ${response.status}`);
      }
    }

    async function deleteComment(id) {
      const response = await window.boardyAuth.authedFetch(`${apiOrigin}/api/comments/${id}`, {
        method: 'DELETE',
        headers: {
          Accept: 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error(`Delete failed: ${response.status}`);
      }
    }

    function connectCommentsWs() {
      const ws = new WebSocket(wsUrl);

      ws.onmessage = (event) => {
        const message = JSON.parse(event.data);

        if (message.type === 'comment.created' || message.type === 'comment.updated') {
          console.info('Comment realtime event', message);
          upsertComment(message.comment);
        }

        if (message.type === 'comment.deleted') {
          console.info('Comment realtime event', message);
          removeComment(message.comment);
        }
      };

      ws.onclose = () => {
        setTimeout(connectCommentsWs, 3000);
      };
    }

    form?.addEventListener('submit', async (event) => {
      event.preventDefault();

      const textarea = form.elements.body;
      const body = textarea.value.trim();

      if (!body) {
        return;
      }

      try {
        await sendComment(body);
        textarea.value = '';
      } catch (error) {
        console.error('Comment create failed', error);
      }
    });

    list.addEventListener('click', async (event) => {
      const item = event.target.closest('[data-comment-id]');

      if (!item) {
        return;
      }

      const id = Number(item.dataset.commentId);
      const comment = comments.get(id);

      if (event.target.matches('[data-comment-edit]')) {
        const body = prompt('Комментарий', comment?.body || '');

        if (body?.trim()) {
          try {
            await updateComment(id, body.trim());
          } catch (error) {
            console.error('Comment update failed', error);
          }
        }
      }

      if (event.target.matches('[data-comment-delete]')) {
        try {
          await deleteComment(id);
        } catch (error) {
          console.error('Comment delete failed', error);
        }
      }
    });

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

    await loadComments();
    connectCommentsWs();
  </script>
@endsection

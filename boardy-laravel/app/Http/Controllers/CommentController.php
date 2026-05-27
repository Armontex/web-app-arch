<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'post_id' => ['required', 'exists:posts,id'],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $comment = $request->user()->comments()->create($data);

        try {
            Http::timeout(2)->post(config('services.fastapi.internal_url').'/internal/broadcast', [
                'type' => 'new_comment',
                'comment' => [
                    'id' => $comment->id,
                    'post_id' => $comment->post_id,
                    'body' => $comment->body,
                    'author' => $request->user()->name,
                    'created_at' => $comment->created_at->toISOString(),
                ],
            ]);
        } catch (Throwable $exception) {
            Log::warning('WS comment broadcast failed: '.$exception->getMessage());
        }

        return back()->with('success', 'Комментарий добавлен.');
    }
}

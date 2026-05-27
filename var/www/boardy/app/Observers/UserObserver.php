<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Redis;

class UserObserver
{
    public function updated(User $user): void
    {
        if (! $user->wasChanged('name')) {
            return;
        }

        Redis::publish('user.renamed', json_encode([
            'user_id' => $user->id,
            'old_name' => $user->getOriginal('name'),
            'name' => $user->name,
        ]));
    }
}

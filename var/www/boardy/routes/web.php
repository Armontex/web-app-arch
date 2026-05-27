<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('posts.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('posts.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('posts', PostController::class);

Route::post('comments', [CommentController::class, 'store'])
    ->middleware('auth')
    ->name('comments.store');

Route::view('/oauth/callback', 'auth.oauth.callback')->name('oauth.callback');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

if (app()->environment('local')) {
    Route::get('/dev-login/{user}', function (User $user) {
        auth()->login($user);

        return redirect()->route('posts.index');
    })->name('dev.login');
}

require __DIR__.'/auth.php';

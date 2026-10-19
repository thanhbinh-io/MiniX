<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RetweetController;
use App\Http\Controllers\TweetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TweetController::class, 'index'])->name('tweets.index');
Route::get('/{username}/status/{id}', [TweetController::class, 'show'])->name('tweets.show');

Route::middleware('auth')->group(function () {
    Route::post('/tweets', [TweetController::class, 'store'])->name('tweets.store');
    Route::delete('/tweets/{tweet}', [TweetController::class, 'destroy'])->name('tweets.destroy');

    Route::post('/tweets/{tweet}/like', [LikeController::class, 'toggle'])->name('tweets.like');
    Route::post('/tweets/{tweet}/retweet', [RetweetController::class, 'toggle'])->name('tweets.retweet');
    Route::post('/tweets/{tweet}/bookmark', [BookmarkController::class, 'toggle'])->name('tweets.bookmark');
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

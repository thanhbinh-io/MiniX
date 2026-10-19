<?php

use App\Http\Controllers\BlockMuteController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RetweetController;
use App\Http\Controllers\TweetController;
use App\Http\Controllers\UserProfileController;
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

    // Follow, Block, Mute
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');
    Route::post('/users/{user}/block', [BlockMuteController::class, 'toggleBlock'])->name('users.block');
    Route::post('/users/{user}/mute', [BlockMuteController::class, 'toggleMute'])->name('users.mute');

    // Profile Settings & Custom Updates
    Route::post('/profile/update', [UserProfileController::class, 'update'])->name('profile.custom_update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Follower & Following lists
Route::get('/{username}/followers', [FollowController::class, 'followers'])->name('users.followers');
Route::get('/{username}/following', [FollowController::class, 'following'])->name('users.following');

require __DIR__.'/auth.php';

// Profile page route - placed at the bottom to avoid capturing reserved system endpoints
Route::get('/{username}', [UserProfileController::class, 'show'])
    ->where('username', '^(?!login|register|logout|profile|tweets|bookmarks|api|dashboard|settings|explore|notifications|messages|search|password).*$')
    ->name('users.profile');

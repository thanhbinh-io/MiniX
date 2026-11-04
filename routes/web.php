<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\BlockMuteController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\CreatorStudioController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RetweetController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TweetController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TweetController::class, 'index'])->name('tweets.index');
Route::get('/explore', [SearchController::class, 'explore'])->name('explore');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/{username}/status/{id}', [TweetController::class, 'show'])->name('tweets.show');

Route::middleware(['auth', 'check.suspended'])->group(function () {
    Route::post('/tweets', [TweetController::class, 'store'])->name('tweets.store');
    Route::patch('/tweets/{tweet}', [TweetController::class, 'update'])->name('tweets.update');
    Route::delete('/tweets/{tweet}', [TweetController::class, 'destroy'])->name('tweets.destroy');

    Route::post('/tweets/{tweet}/like', [LikeController::class, 'toggle'])->name('tweets.like');
    Route::post('/tweets/{tweet}/retweet', [RetweetController::class, 'toggle'])->name('tweets.retweet');
    Route::post('/tweets/{tweet}/bookmark', [BookmarkController::class, 'toggle'])->name('tweets.bookmark');
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');

    // AI Assistant (MiniX AI / Bot)
    Route::get('/bot', [BotController::class, 'index'])->name('bot.index');
    Route::post('/bot/chat', [BotController::class, 'chat'])->name('bot.chat');
    Route::post('/bot/clear', [BotController::class, 'clear'])->name('bot.clear');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');

    // Direct Messages (1-1 Chat)
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/recipients', [MessageController::class, 'recipients'])->name('messages.recipients');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

    // Follow, Block, Mute
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');
    Route::post('/users/{user}/block', [BlockMuteController::class, 'toggleBlock'])->name('users.block');
    Route::post('/users/{user}/mute', [BlockMuteController::class, 'toggleMute'])->name('users.mute');

    // Profile Settings & Custom Updates
    Route::post('/profile/update', [UserProfileController::class, 'update'])->name('profile.custom_update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reports (user-facing)
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

    // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/account', [SettingsController::class, 'account'])->name('account');
        Route::get('/privacy', [SettingsController::class, 'privacy'])->name('privacy');
        Route::get('/privacy/blocked', [SettingsController::class, 'blockedUsers'])->name('privacy.blocked');
        Route::get('/privacy/muted', [SettingsController::class, 'mutedUsers'])->name('privacy.muted');
        Route::patch('/privacy', [SettingsController::class, 'updatePrivacy'])->name('privacy.update');
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::patch('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('/export-data', [SettingsController::class, 'exportData'])->name('export');
        Route::post('/deactivate', [SettingsController::class, 'deactivate'])->name('deactivate');
    });

    // Creator Studio
    Route::get('/creator-studio', [CreatorStudioController::class, 'index'])->name('creator_studio');
});

// Follower & Following lists
Route::get('/{username}/followers', [FollowController::class, 'followers'])->name('users.followers');
Route::get('/{username}/following', [FollowController::class, 'following'])->name('users.following');

// ─── Admin Routes ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');

    Route::post('/reports/{report}/resolve', [AdminDashboardController::class, 'resolveReport'])->name('reports.resolve');
    Route::delete('/reports/{report}/tweet', [AdminDashboardController::class, 'deleteTweetFromReport'])->name('reports.delete_tweet');
    Route::post('/users/{user}/toggle-status', [AdminDashboardController::class, 'toggleUserStatus'])->name('users.toggle_status');
    Route::post('/users/{user}/toggle-role', [AdminDashboardController::class, 'toggleUserRole'])->name('users.toggle_role');
    Route::delete('/tweets/{tweet}', [AdminDashboardController::class, 'deleteTweet'])->name('tweets.delete');
});

require __DIR__.'/auth.php';

// Profile page route - placed at the bottom to avoid capturing reserved system endpoints
Route::get('/{username}', [UserProfileController::class, 'show'])
    ->where('username', '^(?!login|register|logout|profile|tweets|bookmarks|api|dashboard|settings|explore|notifications|messages|search|password|admin|reports|creator-studio|bot).*$')
    ->name('users.profile');

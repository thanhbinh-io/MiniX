<?php

namespace App\Http\Middleware;

use App\Models\Hashtag;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user'                       => $user,
                'unread_notifications_count' => fn () => $user?->unreadNotifications()->count() ?? 0,
                'unread_messages_count'      => fn () => $user ? Message::where('receiver_id', $user->id)->where('is_read', false)->count() : 0,
                'is_admin'                   => fn () => $user?->role === 'admin' ?? false,
                'is_suspended'               => fn () => $user?->status === 'suspended' ?? false,
            ],
            'trends' => function () {
                try {
                    return Hashtag::withCount(['tweets' => fn ($q) => $q->where('tweets.created_at', '>=', now()->subDays(7))])
                        ->having('tweets_count', '>', 0)
                        ->orderByDesc('tweets_count')
                        ->limit(5)
                        ->get()
                        ->map(fn ($h) => [
                            'tag'         => '#' . $h->tag,
                            'raw_tag'     => $h->tag,
                            'category'    => 'Trending in Social',
                            'tweet_count' => number_format($h->tweets_count) . ' ' . Str::plural('post', $h->tweets_count),
                        ]);
                } catch (\Throwable $e) {
                    return [];
                }
            },
            'who_to_follow' => function () use ($user) {
                try {
                    if (! $user) {
                        return User::where('status', 'active')
                            ->latest()
                            ->limit(3)
                            ->get()
                            ->map(fn ($u) => [
                                'id'         => $u->id,
                                'name'       => $u->name,
                                'username'   => $u->username,
                                'avatar_url' => $u->avatar_url,
                                'handle'     => $u->handle,
                            ]);
                    }

                    $followingIds = $user->following()->pluck('following_id')->push($user->id);

                    return User::whereNotIn('id', $followingIds)
                        ->where('status', 'active')
                        ->latest()
                        ->limit(3)
                        ->get()
                        ->map(fn ($u) => [
                            'id'         => $u->id,
                            'name'       => $u->name,
                            'username'   => $u->username,
                            'avatar_url' => $u->avatar_url,
                            'handle'     => $u->handle,
                        ]);
                } catch (\Throwable $e) {
                    return [];
                }
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ]);
    }
}

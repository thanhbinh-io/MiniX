<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    /**
     * Search tweets and users.
     */
    public function index(Request $request)
    {
        $query = trim($request->query('q', ''));
        $type = $request->query('type', 'top'); // 'top' | 'latest' | 'people'
        $currentUserId = $request->user()?->id;

        if ($query === '') {
            return redirect()->route('explore');
        }

        if ($type === 'people') {
            $users = User::where('name', 'like', "%{$query}%")
                ->orWhere('username', 'like', "%{$query}%")
                ->withCount(['followers', 'following'])
                ->paginate(15)
                ->withQueryString();

            if ($currentUserId) {
                $users->getCollection()->transform(function ($u) use ($currentUserId) {
                    $u->is_following = $u->followers()->where('follower_id', $currentUserId)->exists();
                    return $u;
                });
            }

            return Inertia::render('Search', [
                'query' => $query,
                'type' => $type,
                'users' => $users,
                'tweets' => null,
            ]);
        }

        // Tweet search: 'latest' or 'top'
        $tweetsQuery = Tweet::where('tweet', 'like', "%{$query}%")
            ->whereNull('parent_id')
            ->withInteractionDetails($currentUserId);

        if ($type === 'latest') {
            $tweetsQuery->orderByDesc('id');
        } else {
            // 'top'
            $tweetsQuery->orderByDesc('likes_count')->orderByDesc('id');
        }

        $tweets = $tweetsQuery->paginate(15)->withQueryString();

        return Inertia::render('Search', [
            'query' => $query,
            'type' => $type,
            'tweets' => $tweets,
            'users' => null,
        ]);
    }

    /**
     * Explore trending topics and curated tweets.
     */
    public function explore(Request $request): Response
    {
        $currentUserId = $request->user()?->id;

        $tweets = Tweet::whereNull('parent_id')
            ->withInteractionDetails($currentUserId)
            ->orderByDesc('id')
            ->take(15)
            ->get();

        $trends = [
            [
                'category' => 'Technology · Trending',
                'topic' => '#TwitterClone',
                'tweets_count' => '42.8K',
            ],
            [
                'category' => 'Web Development · Trending',
                'topic' => '#Laravel',
                'tweets_count' => '28.1K',
            ],
            [
                'category' => 'Frontend · Trending',
                'topic' => '#VueJS',
                'tweets_count' => '19.4K',
            ],
            [
                'category' => 'Full-Stack · Trending',
                'topic' => '#InertiaJS',
                'tweets_count' => '11.5K',
            ],
            [
                'category' => 'Design · Trending',
                'topic' => '#TailwindCSS',
                'tweets_count' => '35.2K',
            ],
        ];

        return Inertia::render('Explore', [
            'tweets' => $tweets,
            'trends' => $trends,
        ]);
    }
}

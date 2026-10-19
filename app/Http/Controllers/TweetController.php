<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTweetRequest;
use App\Models\Tweet;
use App\Models\TweetView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TweetController extends Controller
{
    /**
     * Display a listing of the top-level tweets.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $userId = $user?->id;

        $excludedUserIds = collect();
        if ($user) {
            $blockedIds = $user->blockedUsers()->pluck('blocked_id');
            $blockedByIds = $user->blockedByUsers()->pluck('user_id');
            $mutedIds = $user->mutedUsers()->pluck('muted_id');
            $excludedUserIds = $blockedIds->merge($blockedByIds)->merge($mutedIds)->unique();
        }

        $tweets = Tweet::whereNull('parent_id')
            ->when($excludedUserIds->isNotEmpty(), fn ($q) => $q->whereNotIn('user_id', $excludedUserIds))
            ->withInteractionDetails($userId)
            ->orderBy('id', 'desc')
            ->get();

        return Inertia::render('Welcome', [
            'tweets' => $tweets,
        ]);
    }

    /**
     * Display a single tweet with its replies thread.
     */
    public function show(Request $request, $username, $id): Response
    {
        $userId = $request->user()?->id;
        $ip = $request->ip();
        $sessionId = $request->session()->getId();

        $tweet = Tweet::where('id', $id)
            ->withInteractionDetails($userId)
            ->firstOrFail();

        // View count deduplication: check if recorded in last 2 hours
        $recentView = TweetView::where('tweet_id', $tweet->id)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('ip_address', $ip)->where('session_id', $sessionId))
            ->where('created_at', '>=', now()->subHours(2))
            ->exists();

        if (! $recentView) {
            TweetView::create([
                'tweet_id' => $tweet->id,
                'user_id' => $userId,
                'ip_address' => $ip,
                'session_id' => $sessionId,
            ]);
            $tweet->loadCount('views');
        }

        $replies = $tweet->replies()
            ->withInteractionDetails($userId)
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Tweet/Show', [
            'tweet' => $tweet,
            'replies' => $replies,
        ]);
    }

    /**
     * Store a newly created tweet or reply in storage.
     */
    public function store(StoreTweetRequest $request): RedirectResponse
    {
        $filePath = null;
        $isVideo = false;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $isVideo = ($extension === 'mp4');
            $folder = $isVideo ? '/videos/' : '/pics/';
            $fileName = time() . '_' . uniqid() . '.' . $extension;

            $targetDir = public_path($folder);
            if (! file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $file->move($targetDir, $fileName);
            $filePath = $folder . $fileName;
        }

        $parentId = $request->input('parent_id');
        $quoteOfId = $request->input('quote_of_id');

        $request->user()->tweets()->create([
            'parent_id' => $parentId,
            'quote_of_id' => $quoteOfId,
            'tweet' => $request->input('tweet') ?? '',
            'file' => $filePath,
            'is_video' => $isVideo,
        ]);

        if ($parentId) {
            return redirect()->back();
        }

        return redirect()->route('tweets.index');
    }

    /**
     * Remove the specified tweet from storage.
     */
    public function destroy(Tweet $tweet): RedirectResponse
    {
        $this->authorize('delete', $tweet);

        if (! empty($tweet->file) && file_exists(public_path($tweet->file))) {
            @unlink(public_path($tweet->file));
        }

        $tweet->delete();

        return redirect()->back();
    }
}

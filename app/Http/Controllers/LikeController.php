<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Tweet;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Toggle like status for the specified tweet.
     */
    public function toggle(Request $request, Tweet $tweet)
    {
        $userId = $request->user()->id;
        $existing = Like::where('user_id', $userId)
            ->where('tweet_id', $tweet->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id' => $userId,
                'tweet_id' => $tweet->id,
            ]);
            $liked = true;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'liked' => $liked,
                'likes_count' => $tweet->likes()->count(),
            ]);
        }

        return back();
    }
}

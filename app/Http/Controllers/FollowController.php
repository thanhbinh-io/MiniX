<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FollowController extends Controller
{
    /**
     * Toggle follow/unfollow for the target user.
     */
    public function toggle(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'You cannot follow yourself.'], 422);
            }
            return back()->withErrors(['follow' => 'You cannot follow yourself.']);
        }

        $isFollowing = $currentUser->isFollowing($user);

        if ($isFollowing) {
            $currentUser->following()->detach($user->id);
            $following = false;
        } else {
            $currentUser->following()->attach($user->id);
            $following = true;

            // Notify target user
            $user->notify(new \App\Notifications\AppActivityNotification($currentUser, 'follow'));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'is_following' => $following,
                'followers_count' => $user->followers()->count(),
            ]);
        }

        return back();
    }

    /**
     * List user followers.
     */
    public function followers(Request $request, $username): Response
    {
        $user = User::where('username', $username)->firstOrFail();
        $currentUserId = $request->user()?->id;

        $users = $user->followers()
            ->withCount(['followers', 'following'])
            ->get()
            ->map(function ($u) use ($currentUserId) {
                $u->is_following = $currentUserId ? $u->followers()->where('follower_id', $currentUserId)->exists() : false;
                return $u;
            });

        return Inertia::render('Profile/FollowList', [
            'profileUser' => $user,
            'type' => 'followers',
            'users' => $users,
        ]);
    }

    /**
     * List users followed by this user.
     */
    public function following(Request $request, $username): Response
    {
        $user = User::where('username', $username)->firstOrFail();
        $currentUserId = $request->user()?->id;

        $users = $user->following()
            ->withCount(['followers', 'following'])
            ->get()
            ->map(function ($u) use ($currentUserId) {
                $u->is_following = $currentUserId ? $u->followers()->where('follower_id', $currentUserId)->exists() : false;
                return $u;
            });

        return Inertia::render('Profile/FollowList', [
            'profileUser' => $user,
            'type' => 'following',
            'users' => $users,
        ]);
    }
}

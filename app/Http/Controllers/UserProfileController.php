<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use App\Models\User;
use App\Support\ReservedUsernames;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserProfileController extends Controller
{
    /**
     * Display the specified user's profile with filtered tabs.
     */
    public function show(Request $request, string $username): Response
    {
        if (ReservedUsernames::isReserved($username)) {
            abort(404);
        }

        $user = User::where('username', $username)
            ->withCount(['followers', 'following', 'tweets'])
            ->firstOrFail();

        $currentUser = $request->user();
        $currentUserId = $currentUser?->id;

        $isFollowing = $currentUser ? $currentUser->isFollowing($user) : false;
        $isBlocked = $currentUser ? $currentUser->hasBlocked($user) : false;
        $isMuted = $currentUser ? $currentUser->hasMuted($user) : false;
        $isBlockedBy = $currentUser ? $user->hasBlocked($currentUser) : false;

        $tab = $request->query('tab', 'tweets');

        $tweets = collect();

        if (! $isBlocked && ! $isBlockedBy) {
            if ($tab === 'replies') {
                $tweets = Tweet::where('user_id', $user->id)
                    ->whereNotNull('parent_id')
                    ->withInteractionDetails($currentUserId)
                    ->orderByDesc('id')
                    ->get();
            } elseif ($tab === 'media') {
                $tweets = Tweet::where('user_id', $user->id)
                    ->whereNotNull('file')
                    ->withInteractionDetails($currentUserId)
                    ->orderByDesc('id')
                    ->get();
            } elseif ($tab === 'likes') {
                $tweets = $user->likedTweets()
                    ->withInteractionDetails($currentUserId)
                    ->orderByDesc('likes.id')
                    ->get();
            } else {
                // Default 'tweets' tab
                $tweets = Tweet::where('user_id', $user->id)
                    ->whereNull('parent_id')
                    ->withInteractionDetails($currentUserId)
                    ->orderByDesc('id')
                    ->get();
            }
        }

        return Inertia::render('Profile/Show', [
            'profileUser' => $user,
            'currentTab' => $tab,
            'tweets' => $tweets,
            'isFollowing' => $isFollowing,
            'isBlocked' => $isBlocked,
            'isMuted' => $isMuted,
            'isBlockedBy' => $isBlockedBy,
        ]);
    }

    /**
     * Update the authenticated user's profile details and upload avatar/banner.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:280'],
            'location' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'string', 'max:100'],
            'birthdate' => ['nullable', 'date'],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'banner' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:10240'],
        ]);

        $user->name = $request->input('name');
        $user->bio = $request->input('bio');
        $user->location = $request->input('location');
        $user->website = $request->input('website');
        $user->birthdate = $request->input('birthdate');

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarFile = $request->file('avatar');
            $avatarName = 'avatar_' . $user->id . '_' . time() . '.' . $avatarFile->getClientOriginalExtension();
            $targetDir = public_path('/pics/avatars/');
            if (! file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $avatarFile->move($targetDir, $avatarName);
            $user->avatar_path = '/pics/avatars/' . $avatarName;
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            $bannerFile = $request->file('banner');
            $bannerName = 'banner_' . $user->id . '_' . time() . '.' . $bannerFile->getClientOriginalExtension();
            $targetDir = public_path('/pics/banners/');
            if (! file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $bannerFile->move($targetDir, $bannerName);
            $user->banner_path = '/pics/banners/' . $bannerName;
        }

        $user->save();

        return redirect()->back();
    }
}

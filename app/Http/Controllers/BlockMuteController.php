<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class BlockMuteController extends Controller
{
    /**
     * Toggle block for the specified user.
     */
    public function toggleBlock(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'You cannot block yourself.'], 422);
        }

        $hasBlocked = $currentUser->hasBlocked($user);

        if ($hasBlocked) {
            $currentUser->blockedUsers()->detach($user->id);
            $blocked = false;
        } else {
            $currentUser->blockedUsers()->attach($user->id);
            // Also sever any follow connections
            $currentUser->following()->detach($user->id);
            $user->following()->detach($currentUser->id);
            $blocked = true;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'is_blocked' => $blocked,
            ]);
        }

        return back();
    }

    /**
     * Toggle mute for the specified user.
     */
    public function toggleMute(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'You cannot mute yourself.'], 422);
        }

        $hasMuted = $currentUser->hasMuted($user);

        if ($hasMuted) {
            $currentUser->mutedUsers()->detach($user->id);
            $muted = false;
        } else {
            $currentUser->mutedUsers()->attach($user->id);
            $muted = true;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'is_muted' => $muted,
            ]);
        }

        return back();
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTweetRequest;
use App\Models\Tweet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TweetController extends Controller
{
    /**
     * Display a listing of the tweets.
     */
    public function index(): Response
    {
        $tweets = Tweet::with('user')
            ->orderBy('id', 'desc')
            ->get();

        return Inertia::render('Welcome', [
            'tweets' => $tweets,
        ]);
    }

    /**
     * Store a newly created tweet in storage.
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

        $request->user()->tweets()->create([
            'tweet' => $request->input('tweet') ?? '',
            'file' => $filePath,
            'is_video' => $isVideo,
        ]);

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

        return redirect()->route('tweets.index');
    }
}

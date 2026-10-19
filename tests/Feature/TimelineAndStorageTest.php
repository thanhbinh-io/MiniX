<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TimelineAndStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_you_feed_displays_unblocked_tweets(): void
    {
        $viewer = User::factory()->create();
        $normalAuthor = User::factory()->create();
        $blockedAuthor = User::factory()->create();
        $mutedAuthor = User::factory()->create();

        // Block and mute
        $viewer->blockedUsers()->attach($blockedAuthor->id);
        $viewer->mutedUsers()->attach($mutedAuthor->id);

        $goodTweet = Tweet::factory()->create(['user_id' => $normalAuthor->id, 'tweet' => 'Good content']);
        $blockedTweet = Tweet::factory()->create(['user_id' => $blockedAuthor->id, 'tweet' => 'Blocked content']);
        $mutedTweet = Tweet::factory()->create(['user_id' => $mutedAuthor->id, 'tweet' => 'Muted content']);

        $response = $this->actingAs($viewer)->get('/?tab=for-you');
        $response->assertOk();

        // Check Inertia props
        $tweets = $response->viewData('page')['props']['tweets']['data'];
        $tweetIds = collect($tweets)->pluck('id');

        $this->assertTrue($tweetIds->contains($goodTweet->id));
        $this->assertFalse($tweetIds->contains($blockedTweet->id));
        $this->assertFalse($tweetIds->contains($mutedTweet->id));
    }

    public function test_following_feed_only_displays_followed_users_tweets(): void
    {
        $viewer = User::factory()->create();
        $followedUser = User::factory()->create();
        $strangerUser = User::factory()->create();

        // Viewer follows followedUser
        $viewer->following()->attach($followedUser->id);

        $followedTweet = Tweet::factory()->create(['user_id' => $followedUser->id, 'tweet' => 'Followed tweet']);
        $strangerTweet = Tweet::factory()->create(['user_id' => $strangerUser->id, 'tweet' => 'Stranger tweet']);
        $ownTweet = Tweet::factory()->create(['user_id' => $viewer->id, 'tweet' => 'My own tweet']);

        $response = $this->actingAs($viewer)->get('/?tab=following');
        $response->assertOk();

        $tweets = $response->viewData('page')['props']['tweets']['data'];
        $tweetIds = collect($tweets)->pluck('id');

        $this->assertTrue($tweetIds->contains($followedTweet->id));
        $this->assertTrue($tweetIds->contains($ownTweet->id));
        $this->assertFalse($tweetIds->contains($strangerTweet->id));
    }

    public function test_guest_accessing_following_feed_gets_empty_list(): void
    {
        Tweet::factory()->count(3)->create();

        $guestResponse = $this->get('/?tab=following');
        $guestResponse->assertOk();
        $guestTweets = $guestResponse->viewData('page')['props']['tweets']['data'];
        $this->assertEmpty($guestTweets);
    }

    public function test_feed_is_paginated(): void
    {
        $user = User::factory()->create();

        // Create 20 tweets
        Tweet::factory()->count(20)->create();

        $page1Response = $this->get('/?page=1');
        $page1Response->assertOk();
        $page1Tweets = $page1Response->viewData('page')['props']['tweets']['data'];
        $this->assertCount(15, $page1Tweets);

        $page2Response = $this->get('/?page=2');
        $page2Response->assertOk();
        $page2Tweets = $page2Response->viewData('page')['props']['tweets']['data'];
        $this->assertCount(5, $page2Tweets);
    }

    public function test_can_upload_image_to_public_storage(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $image = UploadedFile::fake()->image('picture.jpg', 800, 600);

        $response = $this->actingAs($user)->post('/tweets', [
            'tweet' => 'Tweet with image',
            'file' => $image,
        ]);

        $response->assertRedirect('/');

        $tweet = Tweet::where('user_id', $user->id)->first();
        $this->assertNotNull($tweet);
        $this->assertStringStartsWith('/storage/tweets/images/', $tweet->file);
        $this->assertFalse($tweet->is_video);

        $relativePath = str_replace('/storage/', '', $tweet->file);
        Storage::disk('public')->assertExists($relativePath);
    }

    public function test_can_upload_video_to_public_storage(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $video = UploadedFile::fake()->create('video.mp4', 2048, 'video/mp4');

        $response = $this->actingAs($user)->post('/tweets', [
            'tweet' => 'Tweet with video',
            'file' => $video,
        ]);

        $response->assertRedirect('/');

        $tweet = Tweet::where('user_id', $user->id)->first();
        $this->assertNotNull($tweet);
        $this->assertStringStartsWith('/storage/tweets/videos/', $tweet->file);
        $this->assertTrue($tweet->is_video);

        $relativePath = str_replace('/storage/', '', $tweet->file);
        Storage::disk('public')->assertExists($relativePath);
    }

    public function test_rejects_oversized_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // 6MB image (exceeds 5MB limit)
        $oversizedImage = UploadedFile::fake()->create('large.jpg', 6144, 'image/jpeg');

        $response = $this->actingAs($user)->post('/tweets', [
            'tweet' => 'Tweet with large image',
            'file' => $oversizedImage,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('tweets', 0);
    }

    public function test_deleting_tweet_cleans_up_file_from_storage(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $image = UploadedFile::fake()->image('test_delete.png', 400, 400);

        $this->actingAs($user)->post('/tweets', [
            'tweet' => 'Tweet to be deleted',
            'file' => $image,
        ]);

        $tweet = Tweet::where('user_id', $user->id)->first();
        $relativePath = str_replace('/storage/', '', $tweet->file);
        Storage::disk('public')->assertExists($relativePath);

        // Delete the tweet
        $deleteResponse = $this->actingAs($user)->delete("/tweets/{$tweet->id}");
        $deleteResponse->assertRedirect();

        $this->assertDatabaseMissing('tweets', ['id' => $tweet->id]);
        Storage::disk('public')->assertMissing($relativePath);
    }
}

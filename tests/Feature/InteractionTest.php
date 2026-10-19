<?php

namespace Tests\Feature;

use App\Models\Bookmark;
use App\Models\Like;
use App\Models\Retweet;
use App\Models\Tweet;
use App\Models\TweetView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_interact_with_tweets(): void
    {
        $tweet = Tweet::factory()->create();

        $this->post("/tweets/{$tweet->id}/like")->assertRedirect('/login');
        $this->post("/tweets/{$tweet->id}/retweet")->assertRedirect('/login');
        $this->post("/tweets/{$tweet->id}/bookmark")->assertRedirect('/login');
        $this->get('/bookmarks')->assertRedirect('/login');
    }

    public function test_user_can_toggle_like(): void
    {
        $user = User::factory()->create();
        $tweet = Tweet::factory()->create();

        // 1. Like
        $response = $this->actingAs($user)->postJson("/tweets/{$tweet->id}/like");
        $response->assertOk()
            ->assertJson([
                'liked' => true,
                'likes_count' => 1,
            ]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);

        // 2. Unlike (toggle)
        $response2 = $this->actingAs($user)->postJson("/tweets/{$tweet->id}/like");
        $response2->assertOk()
            ->assertJson([
                'liked' => false,
                'likes_count' => 0,
            ]);

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);
    }

    public function test_user_can_toggle_retweet(): void
    {
        $user = User::factory()->create();
        $tweet = Tweet::factory()->create();

        // 1. Retweet
        $response = $this->actingAs($user)->postJson("/tweets/{$tweet->id}/retweet");
        $response->assertOk()
            ->assertJson([
                'retweeted' => true,
                'retweets_count' => 1,
            ]);

        $this->assertDatabaseHas('retweets', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);

        // 2. Undo retweet
        $response2 = $this->actingAs($user)->postJson("/tweets/{$tweet->id}/retweet");
        $response2->assertOk()
            ->assertJson([
                'retweeted' => false,
                'retweets_count' => 0,
            ]);

        $this->assertDatabaseMissing('retweets', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);
    }

    public function test_user_can_toggle_bookmark_and_view_bookmarks_page(): void
    {
        $user = User::factory()->create();
        $tweet = Tweet::factory()->create([
            'tweet' => 'Valuable bookmark tweet',
        ]);

        // Bookmark
        $response = $this->actingAs($user)->postJson("/tweets/{$tweet->id}/bookmark");
        $response->assertOk()
            ->assertJson([
                'bookmarked' => true,
                'bookmarks_count' => 1,
            ]);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);

        // View bookmarks page
        $pageResponse = $this->actingAs($user)->get('/bookmarks');
        $pageResponse->assertOk();

        // Unbookmark
        $response2 = $this->actingAs($user)->postJson("/tweets/{$tweet->id}/bookmark");
        $response2->assertOk()
            ->assertJson([
                'bookmarked' => false,
                'bookmarks_count' => 0,
            ]);

        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $user->id,
            'tweet_id' => $tweet->id,
        ]);
    }

    public function test_user_can_reply_to_tweet(): void
    {
        $author = User::factory()->create(['username' => 'author']);
        $replier = User::factory()->create(['username' => 'replier']);
        $parentTweet = Tweet::factory()->create([
            'user_id' => $author->id,
            'tweet' => 'Parent thread tweet',
        ]);

        $response = $this->actingAs($replier)->post('/tweets', [
            'tweet' => 'This is a threaded reply!',
            'parent_id' => $parentTweet->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tweets', [
            'user_id' => $replier->id,
            'parent_id' => $parentTweet->id,
            'tweet' => 'This is a threaded reply!',
        ]);

        // Verify thread on status page
        $statusResponse = $this->actingAs($replier)->get("/{$author->username}/status/{$parentTweet->id}");
        $statusResponse->assertOk();
    }

    public function test_user_can_quote_tweet(): void
    {
        $author = User::factory()->create(['username' => 'author']);
        $quoter = User::factory()->create(['username' => 'quoter']);
        $originalTweet = Tweet::factory()->create([
            'user_id' => $author->id,
            'tweet' => 'Original tweet to be quoted',
        ]);

        $response = $this->actingAs($quoter)->post('/tweets', [
            'tweet' => 'My commentary on this tweet',
            'quote_of_id' => $originalTweet->id,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('tweets', [
            'user_id' => $quoter->id,
            'quote_of_id' => $originalTweet->id,
            'tweet' => 'My commentary on this tweet',
        ]);
    }

    public function test_viewing_tweet_status_records_deduplicated_view(): void
    {
        $author = User::factory()->create(['username' => 'author']);
        $viewer = User::factory()->create(['username' => 'viewer']);
        $tweet = Tweet::factory()->create(['user_id' => $author->id]);

        // First view
        $this->actingAs($viewer)->get("/{$author->username}/status/{$tweet->id}")->assertOk();
        $this->assertDatabaseCount('tweet_views', 1);

        // Second view in same session/user within 2 hours does not duplicate
        $this->actingAs($viewer)->get("/{$author->username}/status/{$tweet->id}")->assertOk();
        $this->assertDatabaseCount('tweet_views', 1);
    }

    public function test_deleting_tweet_cascades_associated_interactions(): void
    {
        $author = User::factory()->create();
        $interactor = User::factory()->create();

        $tweet = Tweet::factory()->create(['user_id' => $author->id]);

        Like::create(['user_id' => $interactor->id, 'tweet_id' => $tweet->id]);
        Retweet::create(['user_id' => $interactor->id, 'tweet_id' => $tweet->id]);
        Bookmark::create(['user_id' => $interactor->id, 'tweet_id' => $tweet->id]);
        Tweet::factory()->create(['user_id' => $interactor->id, 'parent_id' => $tweet->id]);

        $this->assertDatabaseCount('likes', 1);
        $this->assertDatabaseCount('retweets', 1);
        $this->assertDatabaseCount('bookmarks', 1);
        $this->assertDatabaseCount('tweets', 2);

        $response = $this->actingAs($author)->delete("/tweets/{$tweet->id}");
        $response->assertRedirect();

        $this->assertDatabaseMissing('tweets', ['id' => $tweet->id]);
        $this->assertDatabaseCount('likes', 0);
        $this->assertDatabaseCount('retweets', 0);
        $this->assertDatabaseCount('bookmarks', 0);
    }
}

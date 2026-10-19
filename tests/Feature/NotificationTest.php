<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_like_creates_notification_for_tweet_owner(): void
    {
        $author = User::factory()->create();
        $liker = User::factory()->create();
        $tweet = Tweet::factory()->create(['user_id' => $author->id]);

        $this->assertEquals(0, $author->notifications()->count());

        $this->actingAs($liker)->postJson("/tweets/{$tweet->id}/like")->assertOk();

        $author->refresh();
        $this->assertEquals(1, $author->notifications()->count());

        $notif = $author->notifications()->first();
        $this->assertEquals('like', $notif->data['type']);
        $this->assertEquals($liker->id, $notif->data['sender_id']);
        $this->assertEquals($tweet->id, $notif->data['tweet_id']);
    }

    public function test_self_like_does_not_create_notification(): void
    {
        $author = User::factory()->create();
        $tweet = Tweet::factory()->create(['user_id' => $author->id]);

        $this->actingAs($author)->postJson("/tweets/{$tweet->id}/like")->assertOk();

        $author->refresh();
        $this->assertEquals(0, $author->notifications()->count());
    }

    public function test_retweet_creates_notification_for_tweet_owner(): void
    {
        $author = User::factory()->create();
        $retweeter = User::factory()->create();
        $tweet = Tweet::factory()->create(['user_id' => $author->id]);

        $this->actingAs($retweeter)->postJson("/tweets/{$tweet->id}/retweet")->assertOk();

        $author->refresh();
        $this->assertEquals(1, $author->notifications()->count());
        $this->assertEquals('retweet', $author->notifications()->first()->data['type']);
    }

    public function test_reply_creates_notification_for_parent_tweet_owner(): void
    {
        $author = User::factory()->create();
        $replier = User::factory()->create();
        $tweet = Tweet::factory()->create(['user_id' => $author->id]);

        $this->actingAs($replier)->post('/tweets', [
            'tweet' => 'Great insight!',
            'parent_id' => $tweet->id,
        ]);

        $author->refresh();
        $this->assertEquals(1, $author->notifications()->count());
        $this->assertEquals('reply', $author->notifications()->first()->data['type']);
    }

    public function test_follow_creates_notification_for_target_user(): void
    {
        $follower = User::factory()->create();
        $followee = User::factory()->create();

        $this->actingAs($follower)->postJson("/users/{$followee->id}/follow")->assertOk();

        $followee->refresh();
        $this->assertEquals(1, $followee->notifications()->count());
        $this->assertEquals('follow', $followee->notifications()->first()->data['type']);
    }

    public function test_visiting_notifications_marks_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Trigger 2 notifications
        $this->actingAs($otherUser)->postJson("/users/{$user->id}/follow");
        $tweet = Tweet::factory()->create(['user_id' => $user->id]);
        $this->actingAs($otherUser)->postJson("/tweets/{$tweet->id}/like");

        $user->refresh();
        $this->assertEquals(2, $user->unreadNotifications()->count());

        $response = $this->actingAs($user)->get('/notifications');
        $response->assertOk();

        $user->refresh();
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }
}

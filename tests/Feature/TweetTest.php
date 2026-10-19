<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TweetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_create_tweets(): void
    {
        $response = $this->post('/tweets', [
            'tweet' => 'Hello from a guest',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('tweets', 0);
    }

    public function test_authenticated_user_can_create_tweet(): void
    {
        $user = User::factory()->create([
            'username' => 'alice',
        ]);

        $response = $this->actingAs($user)->post('/tweets', [
            'tweet' => 'This is my first real tweet!',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('tweets', [
            'user_id' => $user->id,
            'tweet' => 'This is my first real tweet!',
        ]);
    }

    public function test_tweet_requires_text_or_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tweets', [
            'tweet' => '',
        ]);

        $response->assertSessionHasErrors('tweet');
        $this->assertDatabaseCount('tweets', 0);
    }

    public function test_tweet_content_cannot_exceed_280_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tweets', [
            'tweet' => str_repeat('a', 281),
        ]);

        $response->assertSessionHasErrors('tweet');
        $this->assertDatabaseCount('tweets', 0);
    }

    public function test_authenticated_user_can_delete_their_own_tweet(): void
    {
        $user = User::factory()->create();
        $tweet = Tweet::factory()->create([
            'user_id' => $user->id,
            'tweet' => 'My temporary tweet',
        ]);

        $response = $this->actingAs($user)->delete("/tweets/{$tweet->id}");

        $response->assertRedirect('/');
        $this->assertDatabaseMissing('tweets', [
            'id' => $tweet->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_tweet(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $tweet = Tweet::factory()->create([
            'user_id' => $owner->id,
            'tweet' => 'Owner tweet',
        ]);

        $response = $this->actingAs($otherUser)->delete("/tweets/{$tweet->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('tweets', [
            'id' => $tweet->id,
        ]);
    }

    public function test_guests_cannot_delete_tweets(): void
    {
        $owner = User::factory()->create();
        $tweet = Tweet::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->delete("/tweets/{$tweet->id}");

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('tweets', [
            'id' => $tweet->id,
        ]);
    }
}

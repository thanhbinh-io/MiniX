<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectMessageShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_recipients_endpoint(): void
    {
        $response = $this->getJson('/messages/recipients');
        $response->assertStatus(401);
    }

    public function test_user_can_fetch_recipients_list(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create(['name' => 'Friend User', 'username' => 'frienduser']);

        // User follows friend
        $user->following()->attach($friend->id);

        $response = $this->actingAs($user)->getJson('/messages/recipients');
        $response->assertOk()
            ->assertJsonStructure([
                'recipients' => [
                    '*' => ['id', 'name', 'username', 'avatar_url']
                ]
            ]);

        $this->assertEquals($friend->id, $response->json('recipients.0.id'));
    }

    public function test_recipients_excludes_suspended_and_blocked_users(): void
    {
        $user = User::factory()->create();
        $suspendedFriend = User::factory()->create(['status' => 'suspended']);
        $blockedFriend = User::factory()->create();

        $user->following()->attach($suspendedFriend->id);
        $user->following()->attach($blockedFriend->id);
        $user->blockedUsers()->attach($blockedFriend->id);

        $response = $this->actingAs($user)->getJson('/messages/recipients');
        $response->assertOk();

        $recipientIds = collect($response->json('recipients'))->pluck('id');
        $this->assertFalse($recipientIds->contains($suspendedFriend->id));
        $this->assertFalse($recipientIds->contains($blockedFriend->id));
        $this->assertFalse($recipientIds->contains($user->id));
    }

    public function test_user_can_search_recipients_by_keyword(): void
    {
        $user = User::factory()->create();
        $alice = User::factory()->create(['name' => 'Alice Johnson', 'username' => 'alicej']);
        $bob = User::factory()->create(['name' => 'Bob Smith', 'username' => 'bobsmith']);

        $response = $this->actingAs($user)->getJson('/messages/recipients?q=Alice');
        $response->assertOk();

        $recipientUsernames = collect($response->json('recipients'))->pluck('username');
        $this->assertTrue($recipientUsernames->contains('alicej'));
        $this->assertFalse($recipientUsernames->contains('bobsmith'));
    }

    public function test_user_can_send_direct_message_sharing_tweet(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $tweet = Tweet::factory()->create();

        $shareText = "Xem bài viết này nhé!\n\nhttp://localhost/{$tweet->user->username}/status/{$tweet->id}";

        $response = $this->actingAs($sender)->postJson('/messages', [
            'receiver_id' => $receiver->id,
            'message'     => $shareText,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('messages', [
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'message'     => $shareText,
        ]);
    }
}


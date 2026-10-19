<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProfileAndFollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_register_with_reserved_username(): void
    {
        $response = $this->post('/register', [
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);

        // Another reserved word
        $response2 = $this->post('/register', [
            'name' => 'Explore User',
            'username' => 'explore',
            'email' => 'explore@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response2->assertSessionHasErrors('username');

        // Valid non-reserved username
        $responseValid = $this->post('/register', [
            'name' => 'Valid User',
            'username' => 'valid_user12',
            'email' => 'valid@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $responseValid->assertRedirect('/');
        $this->assertDatabaseHas('users', ['username' => 'valid_user12']);
    }

    public function test_can_view_user_profile_and_tabs(): void
    {
        $author = User::factory()->create(['username' => 'super_coder']);
        $viewer = User::factory()->create();

        // 1. Regular tweet
        Tweet::factory()->create([
            'user_id' => $author->id,
            'tweet' => 'Hello world tweet',
            'parent_id' => null,
        ]);

        // 2. Reply tweet
        $parent = Tweet::factory()->create();
        Tweet::factory()->create([
            'user_id' => $author->id,
            'tweet' => 'Reply to someone',
            'parent_id' => $parent->id,
        ]);

        // 3. Media tweet
        Tweet::factory()->create([
            'user_id' => $author->id,
            'tweet' => 'Media tweet',
            'file' => '/pics/sample.jpg',
            'is_video' => false,
        ]);

        // Test main posts tab
        $response = $this->actingAs($viewer)->get("/{$author->username}");
        $response->assertOk();

        // Test replies tab
        $repliesResponse = $this->actingAs($viewer)->get("/{$author->username}?tab=replies");
        $repliesResponse->assertOk();

        // Test media tab
        $mediaResponse = $this->actingAs($viewer)->get("/{$author->username}?tab=media");
        $mediaResponse->assertOk();

        // Test likes tab
        $likesResponse = $this->actingAs($viewer)->get("/{$author->username}?tab=likes");
        $likesResponse->assertOk();

        // Non-existent user returns 404
        $this->get('/non_existent_user_xyz')->assertNotFound();

        // Reserved route returns 404 on profile lookup
        $this->get('/messages')->assertNotFound();
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/users/{$user->id}/follow");
        $response->assertStatus(422)
            ->assertJson(['message' => 'You cannot follow yourself.']);

        $this->assertDatabaseCount('follows', 0);
    }

    public function test_user_can_toggle_follow(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Follow userB
        $response = $this->actingAs($userA)->postJson("/users/{$userB->id}/follow");
        $response->assertOk()
            ->assertJson([
                'is_following' => true,
                'followers_count' => 1,
            ]);

        $this->assertDatabaseHas('follows', [
            'follower_id' => $userA->id,
            'following_id' => $userB->id,
        ]);

        // Unfollow userB
        $response2 = $this->actingAs($userA)->postJson("/users/{$userB->id}/follow");
        $response2->assertOk()
            ->assertJson([
                'is_following' => false,
                'followers_count' => 0,
            ]);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $userA->id,
            'following_id' => $userB->id,
        ]);
    }

    public function test_can_view_followers_and_following_pages(): void
    {
        $userA = User::factory()->create(['username' => 'usera']);
        $userB = User::factory()->create(['username' => 'userb']);

        // UserA follows UserB
        $userA->following()->attach($userB->id);

        $followersResponse = $this->actingAs($userA)->get("/{$userB->username}/followers");
        $followersResponse->assertOk();

        $followingResponse = $this->actingAs($userA)->get("/{$userA->username}/following");
        $followingResponse->assertOk();
    }

    public function test_user_can_block_and_unblock_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Establish bidirectional follows
        $userA->following()->attach($userB->id);
        $userB->following()->attach($userA->id);

        $this->assertDatabaseCount('follows', 2);

        // UserA blocks UserB
        $response = $this->actingAs($userA)->postJson("/users/{$userB->id}/block");
        $response->assertOk()->assertJson(['is_blocked' => true]);

        $this->assertDatabaseHas('blocks', [
            'user_id' => $userA->id,
            'blocked_id' => $userB->id,
        ]);

        // Verify blocking severed all follow links
        $this->assertDatabaseCount('follows', 0);

        // UserB's tweets are filtered from UserA's feed
        Tweet::factory()->create(['user_id' => $userB->id, 'tweet' => 'Tweet from blocked user']);
        Tweet::factory()->create(['user_id' => $userA->id, 'tweet' => 'Tweet from user A']);

        $feedResponse = $this->actingAs($userA)->get('/');
        $feedResponse->assertOk();

        // Unblock
        $responseUnblock = $this->actingAs($userA)->postJson("/users/{$userB->id}/block");
        $responseUnblock->assertOk()->assertJson(['is_blocked' => false]);
        $this->assertDatabaseMissing('blocks', [
            'user_id' => $userA->id,
            'blocked_id' => $userB->id,
        ]);
    }

    public function test_user_can_mute_and_unmute_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Mute UserB
        $response = $this->actingAs($userA)->postJson("/users/{$userB->id}/mute");
        $response->assertOk()->assertJson(['is_muted' => true]);

        $this->assertDatabaseHas('mutes', [
            'user_id' => $userA->id,
            'muted_id' => $userB->id,
        ]);

        // Unmute UserB
        $responseUnmute = $this->actingAs($userA)->postJson("/users/{$userB->id}/mute");
        $responseUnmute->assertOk()->assertJson(['is_muted' => false]);

        $this->assertDatabaseMissing('mutes', [
            'user_id' => $userA->id,
            'muted_id' => $userB->id,
        ]);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'bio' => 'Old Bio',
        ]);

        $avatar = UploadedFile::fake()->image('custom_avatar.jpg', 200, 200);
        $banner = UploadedFile::fake()->image('custom_banner.jpg', 1200, 400);

        $response = $this->actingAs($user)->post('/profile/update', [
            'name' => 'New Full Name',
            'bio' => 'Software Engineer & OSS Builder',
            'location' => 'San Francisco, CA',
            'website' => 'https://example.com',
            'birthdate' => '1995-05-15',
            'avatar' => $avatar,
            'banner' => $banner,
        ]);

        $response->assertRedirect();

        $user->refresh();
        $this->assertEquals('New Full Name', $user->name);
        $this->assertEquals('Software Engineer & OSS Builder', $user->bio);
        $this->assertEquals('San Francisco, CA', $user->location);
        $this->assertEquals('https://example.com', $user->website);
        $this->assertEquals('1995-05-15', $user->birthdate->format('Y-m-d'));
        $this->assertNotNull($user->avatar_path);
        $this->assertNotNull($user->banner_path);

        // Clean up created fake files
        if ($user->avatar_path && File::exists(public_path($user->avatar_path))) {
            File::delete(public_path($user->avatar_path));
        }
        if ($user->banner_path && File::exists(public_path($user->banner_path))) {
            File::delete(public_path($user->banner_path));
        }
    }
}

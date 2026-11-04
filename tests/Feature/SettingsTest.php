<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_settings(): void
    {
        $this->get('/settings')->assertRedirect('/login');
        $this->get('/settings/account')->assertRedirect('/login');
        $this->get('/settings/privacy')->assertRedirect('/login');
        $this->get('/settings/notifications')->assertRedirect('/login');
        $this->get('/creator-studio')->assertRedirect('/login');
    }

    public function test_user_can_view_account_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings/account');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/Account')
            ->has('accountInfo.username')
        );
    }

    public function test_user_can_view_privacy_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings/privacy');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/Privacy')
            ->has('userSettings')
        );
    }

    public function test_user_can_update_privacy_chat_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/settings/privacy', [
            'allow_dms_from' => 'following',
            'protect_posts'  => true,
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();

        $this->assertEquals('following', $user->getSetting('allow_dms_from'));
        $this->assertTrue($user->getSetting('protect_posts'));
    }

    public function test_user_can_view_and_update_notification_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/settings/notifications', [
            'quality_filter' => false,
            'notify_likes'   => false,
            'notify_email'   => true,
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();

        $this->assertFalse($user->getSetting('quality_filter'));
        $this->assertFalse($user->getSetting('notify_likes'));
        $this->assertTrue($user->getSetting('notify_email'));
    }

    public function test_user_can_export_archive(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings/export-data');
        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
    }

    public function test_creator_studio_page_loads_with_stats(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/creator-studio');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('CreatorStudio/Index')
            ->has('creatorStats.followers_count')
        );
    }
}


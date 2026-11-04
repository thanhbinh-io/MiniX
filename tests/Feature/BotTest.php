<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BotTest extends TestCase
{
    public function test_guests_cannot_access_bot_chat()
    {
        $response = $this->get('/bot');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_bot_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/bot');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Bot/Index')
            ->has('messages')
        );
    }

    public function test_message_validation_rules_are_enforced()
    {
        $user = User::factory()->create();

        // Empty message
        $response = $this->actingAs($user)->post('/bot/chat', [
            'message' => '',
        ]);
        $response->assertSessionHasErrors('message');

        // Oversized message (> 1000 chars)
        $response = $this->actingAs($user)->post('/bot/chat', [
            'message' => str_repeat('a', 1001),
        ]);
        $response->assertSessionHasErrors('message');
    }

    public function test_authenticated_user_can_chat_with_bot()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Chào bạn! Tôi là Grok AI đây.']
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/bot/chat', [
            'message' => 'Xin chào Grok',
        ]);

        $response->assertRedirect();

        $messages = session('bot_messages');
        $this->assertIsArray($messages);
        $this->assertCount(2, $messages);
        $this->assertEquals('user', $messages[0]['role']);
        $this->assertEquals('Xin chào Grok', $messages[0]['content']);
        $this->assertEquals('assistant', $messages[1]['role']);
        $this->assertEquals('Chào bạn! Tôi là Grok AI đây.', $messages[1]['content']);
    }

    public function test_handles_api_failure_gracefully()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => 'API Unavailable'
            ], 500),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/bot/chat', [
            'message' => 'Chào',
        ]);

        $response->assertRedirect();

        $messages = session('bot_messages');
        $this->assertCount(2, $messages);
        $this->assertEquals('assistant', $messages[1]['role']);
        $this->assertStringContainsString('bận', $messages[1]['content']);
    }

    public function test_user_can_clear_conversation()
    {
        $user = User::factory()->create();

        session(['bot_messages' => [
            ['id' => '1', 'role' => 'user', 'content' => 'Test'],
            ['id' => '2', 'role' => 'assistant', 'content' => 'Response'],
        ]]);

        $response = $this->actingAs($user)->post('/bot/clear');

        $response->assertRedirect();
        $this->assertNull(session('bot_messages'));
    }
}


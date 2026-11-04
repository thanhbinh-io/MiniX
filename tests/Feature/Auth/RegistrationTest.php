<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Secret@123',
            'password_confirmation' => 'Secret@123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_username_must_be_valid_format(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'invalid user!',
            'email' => 'test@example.com',
            'password' => 'Secret@123',
            'password_confirmation' => 'Secret@123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create(['username' => 'existinguser']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'existinguser',
            'email' => 'test2@example.com',
            'password' => 'Secret@123',
            'password_confirmation' => 'Secret@123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_password_must_be_at_least_8_characters(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'shortpass',
            'email' => 'short@example.com',
            'password' => 'Sec@12',
            'password_confirmation' => 'Sec@12',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_password_cannot_exceed_16_characters(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'longpass',
            'email' => 'long@example.com',
            'password' => 'SecretPass@1234567', // 18 chars
            'password_confirmation' => 'SecretPass@1234567',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_password_must_contain_mixed_case(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'nomixcase',
            'email' => 'nomix@example.com',
            'password' => 'secret@123', // all lowercase
            'password_confirmation' => 'secret@123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_password_must_contain_symbol(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'nosymbol',
            'email' => 'nosymbol@example.com',
            'password' => 'SecretPass123', // no symbol
            'password_confirmation' => 'SecretPass123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}

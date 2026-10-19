<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchAndExploreTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_search_query_redirects_to_explore(): void
    {
        $response = $this->get('/search');
        $response->assertRedirect('/explore');

        $responseWhitespace = $this->get('/search?q=   ');
        $responseWhitespace->assertRedirect('/explore');
    }

    public function test_search_tweets_by_keyword(): void
    {
        $tweet1 = Tweet::factory()->create(['tweet' => 'Laravel is an awesome web framework']);
        $tweet2 = Tweet::factory()->create(['tweet' => 'Vanilla CSS styling tricks']);

        $response = $this->get('/search?q=Laravel&type=latest');
        $response->assertOk();

        $tweets = $response->viewData('page')['props']['tweets']['data'];
        $ids = collect($tweets)->pluck('id');

        $this->assertTrue($ids->contains($tweet1->id));
        $this->assertFalse($ids->contains($tweet2->id));
    }

    public function test_search_people_by_name_or_username(): void
    {
        $user1 = User::factory()->create(['name' => 'Alexandre Dumas', 'username' => 'alex_writer']);
        $user2 = User::factory()->create(['name' => 'Charles Dickens', 'username' => 'charles_d']);

        $response = $this->get('/search?q=alex&type=people');
        $response->assertOk();

        $users = $response->viewData('page')['props']['users']['data'];
        $ids = collect($users)->pluck('id');

        $this->assertTrue($ids->contains($user1->id));
        $this->assertFalse($ids->contains($user2->id));
    }

    public function test_explore_page_renders_successfully(): void
    {
        Tweet::factory()->count(5)->create();

        $response = $this->get('/explore');
        $response->assertOk();

        $trends = $response->viewData('page')['props']['trends'];
        $this->assertNotEmpty($trends);

        $tweets = $response->viewData('page')['props']['tweets'];
        $this->assertCount(5, $tweets);
    }
}

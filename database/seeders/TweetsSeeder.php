<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TweetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Demo main user
        $demo = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'username' => 'demouser',
                'password' => Hash::make('password'),
                'bio' => 'Full-stack explorer and open-source enthusiast.',
                'avatar_path' => 'https://randomuser.me/api/portraits/men/32.jpg',
            ]
        );

        $user1 = User::firstOrCreate(
            ['email' => 'johnni@example.com'],
            [
                'name' => 'Johnni Ward',
                'username' => 'johnniward',
                'password' => Hash::make('password'),
                'bio' => 'Climbing, adventure, and outdoor fitness.',
                'avatar_path' => 'https://randomuser.me/api/portraits/men/40.jpg',
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'andre@example.com'],
            [
                'name' => 'Andre Carpenter',
                'username' => 'andrecarpenter',
                'password' => Hash::make('password'),
                'bio' => 'JavaScript dev building modern frontend apps.',
                'avatar_path' => 'https://randomuser.me/api/portraits/men/7.jpg',
            ]
        );

        $user3 = User::firstOrCreate(
            ['email' => 'josephine@example.com'],
            [
                'name' => 'Josephine Perry',
                'username' => 'josephineperry',
                'password' => Hash::make('password'),
                'bio' => 'Digital marketer & SEO specialist.',
                'avatar_path' => 'https://randomuser.me/api/portraits/women/59.jpg',
            ]
        );

        $user4 = User::firstOrCreate(
            ['email' => 'avis@example.com'],
            [
                'name' => 'Avis Glover',
                'username' => 'avisglover',
                'password' => Hash::make('password'),
                'bio' => 'Guitarist & music producer.',
                'avatar_path' => 'https://randomuser.me/api/portraits/men/77.jpg',
            ]
        );

        $user1->tweets()->create([
            'tweet' => "We went rock climbing this weekend! Climbing is way more fun than exercising on gym equipment. It works both mind and body. ENJOY!",
            'file' => '/videos/Sportsman.mp4',
            'is_video' => true,
        ]);

        $user2->tweets()->create([
            'tweet' => "Do you wanna learn Javascript & Vue 3? Building full-stack clones with Laravel + Inertia is an absolute breeze once you get the hang of it.",
            'file' => '/pics/Code.png',
            'is_video' => false,
        ]);

        $user3->tweets()->create([
            'tweet' => "Just published our new SEO & marketing breakdown. Take a look and let me know your thoughts!",
            'file' => '/videos/Seo.mp4',
            'is_video' => true,
        ]);

        $user4->tweets()->create([
            'tweet' => "Playing guitar every single day has taught me more discipline than any routine ever could. Never underestimate the power of consistent practice.",
            'file' => '/pics/PlayGuitar.png',
            'is_video' => false,
        ]);

        $demo->tweets()->create([
            'tweet' => "Excited to welcome everyone to our clean Twitter/X clone built on Laravel 9, Vue 3, and Inertia.js! Everything is now connected to a real database with authentication.",
            'file' => null,
            'is_video' => false,
        ]);
    }
}

<?php

namespace App\Support;

class ReservedUsernames
{
    public const LIST = [
        'login',
        'register',
        'logout',
        'profile',
        'tweets',
        'bookmarks',
        'api',
        'dashboard',
        'settings',
        'explore',
        'notifications',
        'messages',
        'search',
        'password',
        'home',
        'admin',
        'auth',
        'status',
        'null',
        'undefined',
        'about',
        'help',
        'terms',
        'privacy',
    ];

    public static function isReserved(string $username): bool
    {
        return in_array(strtolower($username), self::LIST, true);
    }
}

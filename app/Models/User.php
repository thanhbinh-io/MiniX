<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'avatar_path',
        'banner_path',
        'location',
        'website',
        'birthdate',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate' => 'date',
    ];

    /**
     * Avatar URL accessor with fallback.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path) {
            return str_starts_with($this->avatar_path, 'http')
                ? $this->avatar_path
                : asset($this->avatar_path);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=1C9CEF&color=fff&size=120';
    }

    /**
     * Get tweets authored by this user.
     */
    public function tweets()
    {
        return $this->hasMany(Tweet::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function retweets()
    {
        return $this->hasMany(Retweet::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function likedTweets()
    {
        return $this->belongsToMany(Tweet::class, 'likes')->withTimestamps();
    }

    public function retweetedTweets()
    {
        return $this->belongsToMany(Tweet::class, 'retweets')->withTimestamps();
    }

    public function bookmarkedTweets()
    {
        return $this->belongsToMany(Tweet::class, 'bookmarks')->withTimestamps();
    }
}

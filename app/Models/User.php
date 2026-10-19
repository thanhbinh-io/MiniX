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
        'banner_url',
        'handle',
        'joined_date_human',
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
     * Banner URL accessor with fallback.
     */
    public function getBannerUrlAttribute(): string
    {
        if ($this->banner_path) {
            return str_starts_with($this->banner_path, 'http')
                ? $this->banner_path
                : asset($this->banner_path);
        }

        return 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1200&h=400&fit=crop';
    }

    /**
     * Handle accessor.
     */
    public function getHandleAttribute(): string
    {
        return '@' . ($this->username ?? 'user');
    }

    /**
     * Formatted joined date.
     */
    public function getJoinedDateHumanAttribute(): string
    {
        return $this->created_at ? 'Joined ' . $this->created_at->format('F Y') : '';
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

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocks', 'user_id', 'blocked_id')->withTimestamps();
    }

    public function blockedByUsers()
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocked_id', 'user_id')->withTimestamps();
    }

    public function mutedUsers()
    {
        return $this->belongsToMany(User::class, 'mutes', 'user_id', 'muted_id')->withTimestamps();
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    public function hasBlocked(User $user): bool
    {
        return $this->blockedUsers()->where('blocked_id', $user->id)->exists();
    }

    public function hasMuted(User $user): bool
    {
        return $this->mutedUsers()->where('muted_id', $user->id)->exists();
    }
}

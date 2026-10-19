<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'quote_of_id',
        'tweet',
        'file',
        'is_video',
    ];

    protected $casts = [
        'is_video' => 'boolean',
        'is_liked' => 'boolean',
        'is_retweeted' => 'boolean',
        'is_bookmarked' => 'boolean',
    ];

    protected $appends = [
        'created_at_human',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Tweet::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Tweet::class, 'parent_id');
    }

    public function quoteOf()
    {
        return $this->belongsTo(Tweet::class, 'quote_of_id');
    }

    public function quotes()
    {
        return $this->hasMany(Tweet::class, 'quote_of_id');
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

    public function views()
    {
        return $this->hasMany(TweetView::class);
    }

    public function scopeWithInteractionDetails($query, $userId = null)
    {
        return $query->with(['user', 'quoteOf.user'])
            ->withCount(['replies', 'retweets', 'likes', 'views'])
            ->when($userId, function ($q) use ($userId) {
                $q->withExists([
                    'likes as is_liked' => fn ($subQuery) => $subQuery->where('user_id', $userId),
                    'retweets as is_retweeted' => fn ($subQuery) => $subQuery->where('user_id', $userId),
                    'bookmarks as is_bookmarked' => fn ($subQuery) => $subQuery->where('user_id', $userId),
                ]);
            });
    }

    public function getCreatedAtHumanAttribute(): string
    {
        if (! $this->created_at) {
            return '';
        }

        return $this->created_at->diffForHumans();
    }
}

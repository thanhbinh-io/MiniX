<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tweet',
        'file',
        'is_video',
    ];

    protected $casts = [
        'is_video' => 'boolean',
    ];

    protected $appends = [
        'created_at_human',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCreatedAtHumanAttribute(): string
    {
        if (! $this->created_at) {
            return '';
        }

        return $this->created_at->diffForHumans();
    }
}

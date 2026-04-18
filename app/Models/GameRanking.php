<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRanking extends Model
{
    protected $fillable = [
        'user_id',
        'player_name',
        'score',
        'play_time',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'play_time' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeTopRankings($query, int $limit = 10)
    {
        return $query->orderBy('score', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($limit);
    }
}

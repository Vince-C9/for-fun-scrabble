<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameWords extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'player_id',
        'word',
        'score',
        'bonuses',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function tiles(): HasMany
    {
        return $this->hasMany(Tile::class);
    }
}

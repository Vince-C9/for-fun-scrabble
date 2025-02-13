<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tile extends Model
{
    use HasFactory;

    public $fillable = [
        'letter', 'game_id', 'coordinates', 'is_used', 'player_hand_id', 'score'
    ];

    //Only pull from tiles that are already used.
    public function scopeIsUsed($query){
        return $query->where('is_used', true)->orHas('currentTiles');
    }

    //Only pull from tiles that are still in the bag
    public function scopeIsNotUsed($query){
        return $query->where('is_used', false)->doesntHave('currentTiles');
    }


    public function game(): BelongsTo {
        return $this->belongsTo(Game::class);
    }

    public function currentTiles(){
        return $this->hasMany(CurrentTiles::class);
    }

}

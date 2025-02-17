<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;
    public $fillable = [
        'game_id','score', 'name'
    ];

    public function game(){
        return $this->belongsTo(Game::class);
    }

    public function currentTiles(): HasMany{
        return $this->hasMany(CurrentTiles::class);
    }

    public function gameWords(): HasMany{
        return $this->hasMany(GameWords::class);
    }
    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $with = ['players', 'tiles'];

    protected $fillable = [
        'id',
        'state'
    ];

    public function players(){
        return $this->hasMany(Player::class);
    }

    public function tiles(){
        return $this->hasMany(Tile::class);
    }

    public function gameWords(): HasMany
    {
        return $this->hasMany(GameWords::class);
    }
}

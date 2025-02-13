<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrentTiles extends Model
{
    use HasFactory;
    protected $with = ['tile'];

    protected $fillable = [
        'tile_id'
    ];

    public function player(): BelongsTo {
        return $this->belongsTo(Player::class);        
    }

    public function tile(): BelongsTo {
        return $this->belongsTo(Tile::class);
    }
}

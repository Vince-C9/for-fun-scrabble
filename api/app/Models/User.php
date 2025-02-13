<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function players(): HasMany{
        return $this->hasMany(Player::class);
    }
}

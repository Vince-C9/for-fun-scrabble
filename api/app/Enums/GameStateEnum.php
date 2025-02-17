<?php
namespace App\Enums;

enum GameStateEnum: string
{
    case PLAYING = 'playing';
    case WON = 'won';
    case LOST = 'lost';
    case DRAW = 'draw';
    case FULL = 'full';
    case LOCKED = 'locked';
    case SEEKING_PLAYERS = 'seeking_players';

    public static function isValid(string $value): bool
    {
        return match ($value) {
            self::PLAYING->value,
            self::WON->value,
            self::LOST->value,
            self::DRAW->value,
            self::FULL->value,
            self::LOCKED->value,
            self::SEEKING_PLAYERS->value => true,
            default => false,
        };
    }
}
<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Player;
use App\Models\Tile;
use App\Models\Word;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ScrabbleService
{
    protected array $letterValues = [ 
        'A' => 1, 'B' => 3, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 4, 'G' => 2,
        'H' => 4, 'I' => 1, 'J' => 8, 'K' => 5, 'L' => 1, 'M' => 3, 'N' => 1,
        'O' => 1, 'P' => 3, 'Q' => 10, 'R' => 1, 'S' => 1, 'T' => 1, 'U' => 1,
        'V' => 4, 'W' => 4, 'X' => 8, 'Y' => 4, 'Z' => 10
    ];

    protected array $tileCounts = [
        'A' => 9, 'B' => 2, 'C' => 2, 'D' => 4, 'E' => 12, 'F' => 2, 'G' => 3,
        'H' => 2, 'I' => 9, 'J' => 1, 'K' => 1, 'L' => 4, 'M' => 2, 'N' => 6,
        'O' => 8, 'P' => 2, 'Q' => 1, 'R' => 6, 'S' => 4, 'T' => 6, 'U' => 4,
        'V' => 2, 'W' => 2, 'X' => 1, 'Y' => 2, 'Z' => 1
    ];

    /** @param array<Player> $players */
    public function createGame(Collection $users): Game {
        dd($users);
        $game = Game::create(['state'=>'active']);
        foreach ($users as $user) { 
            $game->players()->create(['user_id' => $user->id, 'score'=>0]);
        }
        $this->initialiseTiles($game);
        return $game;
    }


    private function initialiseTiles(Game $game) {
        foreach($this->tileCounts as $letter => $count) {
            for($i = 0; $i < $count; $i++) {
                $game->tiles()->create(['letter'=>$letter]);
            }
        } 
    }


    public function placeWord(Game $game, Player $player, array $wordData): array {

        if (!$game->players()->where('id', $player->id)->exists()) {
            return ['error' => 'Player is not part of this game'];
        }

        if(!$this->isValidWord($wordData['word'])) {
            return ['error' => 'Invalid Word'];
        }

        $score = $this->calculateScore($wordData['word']);

        $player->increment('score', $score);
        return ['success'=>true, 'score'=>$score];
    }

    private function calculateScore(string $word): int {
        $score = 0;
        foreach (str_split(strtoupper($word)) as $letter) {
            $score += $this->letterValues[$letter] ?? 0;
        }
        return $score;
    }

    private function isValidWord(string $word): bool {
        return Cache::remember("valid_word_{$word}", now()->addDay(), function() use ($word) {
            return !!Word::where('word', $word)->exists();
        });
    }
}
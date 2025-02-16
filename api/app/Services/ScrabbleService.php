<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Player;
use App\Models\Tile;
use App\Models\Word;
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
    public function createGame(array $player): Game {
        $game = Game::create(['state'=>'active']);
        $game->players()->create($player);
        
        $this->initialiseTiles($game);
        return $game->load('players', 'tiles');
    }


    private function initialiseTiles(Game $game) {
        foreach($this->tileCounts as $letter => $count) {
            for($i = 0; $i < $count; $i++) {
                $game->tiles()->create(['letter' => $letter, 'score' => $this->letterValues[$letter]]);
            }
        } 
    }


    public function placeWord(Game $game, Player $player, array $wordData): array {

        //Make sure player is part of this game
        if (!$game->players()->where('id', $player->id)->exists()) {
            return ['error' => 'Player is not part of this game'];
        }

        //Make sure word is valid scrabble word
        if(!$this->isValidWord($wordData['word'])) {
            return ['error' => 'Invalid Word'];
        }
    
        $tileBonuses=[];
        //Log each letter as used against this game, by this player.
        foreach($wordData['tiles'] as $tile) {
            //Validate tile
            $validTile = $game->tiles()->where('id', $tile['id'])->first();
            if($validTile) {
                //Relying on FE atm to get functionality done but need to make this BE authoritative in the long run.

                $tileBonuses = array_merge($tileBonuses, $tile['letterBonuses']);

                Tile::where('id', $tile['id'])->update([
                    'is_used' => true,
                    'x' => $tile['x'],
                    'y' => $tile['y'],
                    'player_hand_id' => null
                ]);
                
                //Remove each tile from players hand
                $player->currentTiles()->where('tile_id', $tile['id'])->delete();
            } else {
                return ['error' => 'Unused tile of letter '.$tile['letter'].' does not exist against this game.'];
            }
        }
        
        //Save the word to players game words
        $game->gameWords()->create([
            'player_id' => $player->id,
            'word' => Word::where('word', $wordData['word'])->first()->id,
            'score' => $wordData['wordScore'],
            'bonuses' => json_encode(['word' => $wordData['wordBonuses'], 'tile'=> $tileBonuses])
        ]);
        
        //Make this back end authoritative, rework calculate score once all other functions in place
        $score = $wordData['wordScore'];

        $player->increment('score', $score);
        return ['success'=>true, 'score'=>$score];
    }

    /**
     * Check if a word is valid by looking it up in the database, and remember the result for a day.
     *
     * @param string $word
     * @return bool
     */
    public function isValidWord(string $word): bool {
         return Cache::remember("valid_word_{$word}", now()->addDay(), function() use ($word) {
            return Word::where('word', $word)->exists();
        });
    }

    //Get unused tiles still valid in the game and add them to the players current hand
    public function generatePlayerHandFromGameTiles(Game $game, Player $player) {
        $currentHandCount = $player->currentTiles()->count();
        $totalLoops = 7 - $currentHandCount;

        $unusedTiles = $game->tiles()->isNotUsed()->inRandomOrder()->get();
        
        $loops = 0;
        if($totalLoops > 0){
            foreach ($unusedTiles as $tile) {
                $loops ++;
                $player->currentTiles()->create(['tile_id' => $tile->id])->load('tile');
                
                if($loops >= $totalLoops) {
                    break;
                }
            }
        }
        return $player->currentTiles;
    }
    
}
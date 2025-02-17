<?php

use App\Http\Controllers\GameController;
use App\Models\Game;
use App\Models\Word;
use App\Services\ScrabbleService;
use App\Services\WordListService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function () {
    $service = new ScrabbleService();
    $game = Game::first() ?? null;
    if(!$game){
        $game = $service->createGame(['name'=>'Player 1', 'score' => 0]);
    }
    if($game->players()->count() < 1) {
        $game->players()->create(['name' => 'Player 1', 'score' => 0]);
    }

    $playerTiles = $service->generatePlayerHandFromGameTiles($game, $game->players()->first());
    dd($playerTiles->toArray());
});


Route::get('get-words', function(){
    echo "Getting Word List.";
    $service = new WordListService();
    $service->fetchWords();
});

Route::get('check-words', function(){
    $count = Word::count();
    echo $count.' words found.';

    foreach(Word::where('id','<=',25)->get() as $key => $word) {
        echo "<p>".($key+1).": ".$word->word.'</p>';
    }
    echo "<p>... And one or two more :)</p>";
});

Route::get('valid', function(){
    $service = new ScrabbleService();
    dump(Word::where('word', 'DO')->exists());
    dd($service->isValidWord('TO'));
});
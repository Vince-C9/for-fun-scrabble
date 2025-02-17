<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddPlayerRequest;
use App\Http\Requests\GameStoreRequest;
use App\Models\Game;
use App\Models\Player;
use App\Services\ScrabbleService;

class GameController extends Controller
{
    public function index(){
        return response()->json([
            'status' => 'success', 
            'data' => Game::with('players')->get()
        ], 200);
    }

    public function store(GameStoreRequest $request) {
        $service = new ScrabbleService();
        $game = $service->createGame(['name'=>$request->input('player'), 'score' => 0]);
        return response()->json(['status'=>'success', 'data'=>$game], 200);
    }

    public function show(Game $game) {
        return response()->json(['status'=>'success', 'data'=>$game], 200);
    }

    //Arguably could be an invoke! 
    public function update(AddPlayerRequest $request, Game $game) {
        $player = $game->players()->create(['name' => $request->input('player'), 'score' => 0]);
        return response()->json(['status'=>'success', 'data'=>$player], 200);
    }

    public function updateTurn(Game $game, Player $player) {
        Game::where('id', $game->id)->update(['player_turn_id' => $player->id]);
        return response()->json(['status'=>'success'], 200);
    }

}

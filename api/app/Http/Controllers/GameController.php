<?php

namespace App\Http\Controllers;

use App\Http\Requests\GameStoreRequest;
use App\Models\Game;
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

}

<?php
namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Services\ScrabbleService;
use Illuminate\Support\Facades\Request;

class TurnController extends Controller {
    public function __invoke(Request $request, Game $game, Player $player)
    {
        //Draw up to maximum tiles
        $service = new ScrabbleService();
        
        $tiles = $service->generatePlayerHandFromGameTiles($game, $player);
        return response()->json(['status'=>'success', 'tiles'=>$tiles], 200);
    }
}
<?php
namespace App\Http\Controllers;

use App\Http\Requests\SubmitWordRequest;
use App\Models\Game;
use App\Models\Player;
use App\Rules\ValidWordRule;
use App\Services\ScrabbleService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class SubmitWordController extends Controller {
    public function __invoke(SubmitWordRequest $request, Game $game, Player $player)
    {
        $words = $request->get('words');

        foreach ($words as $word) {
            $service = new ScrabbleService();

            $service->placeWord($game, $player, $word);
        }

        //Validate Score Is Accurate
        //Draw up to maximum tiles
        $service = new ScrabbleService();
        
        $tiles = $service->generatePlayerHandFromGameTiles($game, $player);
        return response()->json(['status'=>'success', 'tiles'=>$tiles], 200);
    }
}
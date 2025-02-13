<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
  
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\TurnController;
use App\Models\Game;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
  
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);
     
Route::middleware('auth:api')->group( function () {
    Route::resource('products', ProductController::class);
});

Route::prefix('game')->group(function () {
    Route::get('/', [GameController::class, 'index'])->name('game.list');
    Route::post('/create', [GameController::class, 'store'])->name('game.create');
    Route::get('turn/{game}/{player}', TurnController::class)->name('game.turn');
    Route::get('{game}', [GameController::class, 'show'])->name('game.show');
});
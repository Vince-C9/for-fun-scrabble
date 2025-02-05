<?php

use App\Models\Word;
use App\Services\WordListService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
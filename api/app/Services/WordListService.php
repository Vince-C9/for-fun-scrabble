<?php

namespace App\Services;

use App\Jobs\ProcessDictionary;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class WordListService
{
    protected Client $client;
    

    public function __construct()
    {
        $this->client = new Client();
    }

    public function fetchWords(): void
    {
        $file = 'dictionary.txt';
        if (Storage::exists($file)) {
            echo 'Dispatching...';
            dispatch(new ProcessDictionary($file));
        }
        else
        {
            response()->json(['message' => 'Wordlist not found'], 404);
        }

    }


}

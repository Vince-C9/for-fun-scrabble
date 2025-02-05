<?php

namespace App\Console\Commands;

use App\Services\WordListService;
use Illuminate\Console\Command;

class GetValidWords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-valid-words';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates the database with words from the Colins dictionary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $wordList = new WordListService();

        $wordList->fetchWords();
    }
}

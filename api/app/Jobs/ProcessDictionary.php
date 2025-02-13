<?php

namespace App\Jobs;

use App\Models\Word;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessDictionary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $chunkSize = 1000;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        if (!Storage::exists($this->filePath)) {
            Log::warning('Dictionary file not found.');
            return;
        }

        $handle = Storage::readStream($this->filePath);
        $batch = [];

        while (($word = fgets($handle)) !== false) {
            $word = trim($word);
            
            if (!empty($word) && ctype_alpha($word)) {
                $batch[] = ['word' => strtoupper($word)];
            }

            if (count($batch) >= $this->chunkSize) {
                Word::insertOrIgnore($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            Word::insertOrIgnore($batch);
        }

        fclose($handle);
    }
}

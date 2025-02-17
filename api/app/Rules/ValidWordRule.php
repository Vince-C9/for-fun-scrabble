<?php

namespace App\Rules;

use App\Services\ScrabbleService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidWordRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $scrabbleService = new ScrabbleService();

        if (!$scrabbleService->isValidWord($value)) {
            $fail('The :attribute is not a valid Scrabble word.');
        }
    }
}

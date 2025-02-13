<?php

namespace App\Http\Requests;

use App\Rules\ValidWordRule;
use App\Services\ScrabbleService;
use Illuminate\Foundation\Http\FormRequest;

class SubmitWordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'word' => ['required', 'string', new ValidWordRule],
        ];
    }
}

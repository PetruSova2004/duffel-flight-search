<?php

namespace App\Http\Requests\Flights;

use Illuminate\Foundation\Http\FormRequest;

class CityIndexRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'country' => ['required', 'string', 'size:2', 'alpha:ascii'],
        ];
    }
}

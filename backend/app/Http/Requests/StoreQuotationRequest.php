<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'age' => ['required', 'string'],
            'currency_id' => ['required', 'string', 'exists:currencies,code'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'currency_id.exists' => 'The selected currency is not supported.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRepairRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_name'  => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'phone'        => ['required', 'string', 'size:12', 'regex:/^\+7\d{10}$/'],
            'address'      => ['required', 'string', 'max:255'],
            'problem_text' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_name.regex' => 'ФИО должно содержать только буквы (можно пробел/дефис).',
            'phone.size' => 'Телефон должен быть в формате +7XXXXXXXXXX (10 цифр после +7).',
            'phone.regex' => 'Телефон должен быть в формате +7XXXXXXXXXX (10 цифр после +7).',
        ];
    }
}
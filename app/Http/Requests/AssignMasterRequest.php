<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AssignMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // доступ ограничим middleware role:dispatcher
    }

    public function rules(): array
    {
        return [
            'assigned_master_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', 'master'),
            ],
        ];
    }
}
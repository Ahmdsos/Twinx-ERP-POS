<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Accounting\Enums\AccountType;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:20',
                'unique:accounts,code',
                'regex:/^[A-Z0-9\-]+$/i', // Alphanumeric with dashes
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AccountType::class)],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_header' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Account code can only contain letters, numbers, and dashes.',
            'code.unique' => 'This account code is already in use.',
            'parent_id.exists' => 'The selected parent account does not exist.',
        ];
    }
}

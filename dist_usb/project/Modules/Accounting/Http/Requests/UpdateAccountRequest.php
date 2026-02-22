<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Accounting\Enums\AccountType;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $accountId = $this->route('account')->id;

        return [
            'code' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('accounts', 'code')->ignore($accountId),
                'regex:/^[A-Z0-9\-]+$/i',
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::enum(AccountType::class)],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_header' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}

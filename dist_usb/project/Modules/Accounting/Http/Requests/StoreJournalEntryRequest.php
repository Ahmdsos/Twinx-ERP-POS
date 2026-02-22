<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],

            // Lines validation
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.cost_center' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'lines.min' => 'A journal entry must have at least 2 lines.',
            'lines.*.account_id.required' => 'Each line must have an account.',
            'lines.*.account_id.exists' => 'Selected account does not exist.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);

            // Each line must have either debit or credit (not both > 0)
            foreach ($lines as $index => $line) {
                $debit = floatval($line['debit'] ?? 0);
                $credit = floatval($line['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add(
                        "lines.{$index}",
                        "Line cannot have both debit and credit amounts."
                    );
                }

                if ($debit == 0 && $credit == 0) {
                    $validator->errors()->add(
                        "lines.{$index}",
                        "Line must have either a debit or credit amount."
                    );
                }
            }
        });
    }
}

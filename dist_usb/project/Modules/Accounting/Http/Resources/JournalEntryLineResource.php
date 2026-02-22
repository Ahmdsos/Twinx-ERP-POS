<?php

namespace Modules\Accounting\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JournalEntryLineResource - API Resource for JournalEntryLine model
 */
class JournalEntryLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'account' => $this->whenLoaded('account', function () {
                return [
                    'id' => $this->account->id,
                    'code' => $this->account->code,
                    'name' => $this->account->name,
                ];
            }),
            'debit' => (float) $this->debit,
            'credit' => (float) $this->credit,
            'amount' => (float) $this->amount,
            'is_debit' => $this->isDebit(),
            'description' => $this->description,
            'cost_center' => $this->cost_center,
            'subledger_type' => $this->subledger_type,
            'subledger_id' => $this->subledger_id,
        ];
    }
}

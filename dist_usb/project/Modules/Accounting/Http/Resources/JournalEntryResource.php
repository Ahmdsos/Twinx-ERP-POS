<?php

namespace Modules\Accounting\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JournalEntryResource - API Resource for JournalEntry model
 */
class JournalEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entry_number' => $this->entry_number,
            'entry_date' => $this->entry_date->toDateString(),
            'reference' => $this->reference,
            'description' => $this->description,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'is_editable' => $this->isEditable(),
            'can_be_posted' => $this->canBePosted(),
            'can_be_reversed' => $this->canBeReversed(),

            'total_debit' => (float) $this->total_debit,
            'total_credit' => (float) $this->total_credit,
            'is_balanced' => $this->isBalanced(),

            'fiscal_year' => $this->whenLoaded('fiscalYear', function () {
                return [
                    'id' => $this->fiscalYear->id,
                    'name' => $this->fiscalYear->name,
                ];
            }),

            'lines' => JournalEntryLineResource::collection($this->whenLoaded('lines')),

            'posted_at' => $this->posted_at?->toIso8601String(),
            'posted_by' => $this->whenLoaded('postedByUser', function () {
                return $this->postedByUser?->name;
            }),

            'reversed_at' => $this->reversed_at?->toIso8601String(),
            'reversed_by_entry_id' => $this->reversed_by_entry_id,

            'created_by' => $this->whenLoaded('creator', fn() => $this->creator?->name),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

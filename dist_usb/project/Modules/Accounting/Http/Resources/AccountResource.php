<?php

namespace Modules\Accounting\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * AccountResource - API Resource for Account model
 */
class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'normal_balance' => $this->type->normalBalance(),
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', function () {
                return [
                    'id' => $this->parent->id,
                    'code' => $this->parent->code,
                    'name' => $this->parent->name,
                ];
            }),
            'description' => $this->description,
            'is_header' => $this->is_header,
            'is_active' => $this->is_active,
            'is_system' => $this->is_system,
            'balance' => (float) $this->balance,
            'path' => $this->path,
            'depth' => $this->depth,
            'children' => AccountResource::collection($this->whenLoaded('children')),
            'descendants' => AccountResource::collection($this->whenLoaded('descendants')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

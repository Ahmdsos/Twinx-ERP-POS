<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Inventory\Enums\ProductType;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'sku' => "sometimes|string|max:50|unique:products,sku,{$productId}",
            'barcode' => "nullable|string|max:50|unique:products,barcode,{$productId}",
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => ['sometimes', Rule::enum(ProductType::class)],
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'sometimes|exists:units,id',
            'purchase_unit_id' => 'nullable|exists:units,id',
            'cost_price' => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'min_selling_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'numeric|min:0|max:100',
            'is_tax_inclusive' => 'boolean',
            'reorder_level' => 'integer|min:0',
            'reorder_quantity' => 'integer|min:0',
            'min_stock' => 'integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'sales_account_id' => 'nullable|exists:accounts,id',
            'purchase_account_id' => 'nullable|exists:accounts,id',
            'inventory_account_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'is_purchasable' => 'boolean',
        ];
    }
}

<?php

namespace Modules\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\Quotation;
use Modules\Sales\Enums\QuotationStatus;
use Carbon\Carbon;

class POSPriceController extends Controller
{
    /**
     * Get active quotation prices for a specific customer
     */
    public function getCustomerPrices(Customer $customer)
    {
        $today = Carbon::today()->toDateString();

        // Find active, non-expired quotations for this customer
        // We look in: 
        // 1. Direct legacy customer_id
        // 2. New many-to-many relationship
        // 3. New target_customer_type
        $quotationIds = Quotation::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
                ->orWhereHas('customers', function ($sub) use ($customer) {
                    $sub->where('customers.id', $customer->id);
                })
                ->orWhere('target_customer_type', $customer->type);
        })
            ->whereIn('status', [QuotationStatus::SENT, QuotationStatus::ACCEPTED])
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $today);
            })
            ->pluck('id');

        if ($quotationIds->isEmpty()) {
            return response()->json([]);
        }

        // Get the latest price for each product from these quotations
        // In case of overlap, we take the most recently created quotation's price
        $specialPrices = \DB::table('quotation_lines')
            ->whereIn('quotation_id', $quotationIds)
            ->orderBy('created_at', 'desc')
            ->get(['product_id', 'unit_price'])
            ->unique('product_id')
            ->values();

        return response()->json($specialPrices);
    }
}

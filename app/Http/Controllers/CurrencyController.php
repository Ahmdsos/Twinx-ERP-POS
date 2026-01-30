<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

/**
 * Currency Controller for Multi-Currency Management
 */
class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::orderByDesc('is_default')->orderBy('code')->get();
        return view('currencies.index', compact('currencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'decimal_places' => 'required|integer|min:0|max:6',
        ]);

        $currency = Currency::create($validated);

        // Log initial exchange rate
        ExchangeRate::create([
            'currency_id' => $currency->id,
            'rate' => $validated['exchange_rate'],
            'effective_date' => now()->format('Y-m-d'),
        ]);

        return back()->with('success', 'تم إضافة العملة بنجاح');
    }

    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'decimal_places' => 'required|integer|min:0|max:6',
            'is_active' => 'boolean',
        ]);

        // Log new exchange rate if changed
        if ($currency->exchange_rate != $validated['exchange_rate']) {
            ExchangeRate::create([
                'currency_id' => $currency->id,
                'rate' => $validated['exchange_rate'],
                'effective_date' => now()->format('Y-m-d'),
            ]);
        }

        $currency->update($validated);

        return back()->with('success', 'تم تحديث العملة بنجاح');
    }

    public function setDefault(Currency $currency)
    {
        // Remove default from all
        Currency::where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        $currency->update(['is_default' => true]);

        return back()->with('success', 'تم تعيين العملة الافتراضية');
    }

    public function destroy(Currency $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', 'لا يمكن حذف العملة الافتراضية');
        }

        $currency->delete();
        return back()->with('success', 'تم حذف العملة');
    }

    public function convert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id' => 'required|exists:currencies,id',
        ]);

        $from = Currency::find($request->from_currency_id);
        $to = Currency::find($request->to_currency_id);

        $baseAmount = $from->toBase($request->amount);
        $converted = $to->fromBase($baseAmount);

        return response()->json([
            'original' => $from->format($request->amount),
            'converted' => $to->format($converted),
            'amount' => round($converted, $to->decimal_places),
        ]);
    }
}

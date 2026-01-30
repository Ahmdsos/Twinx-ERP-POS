<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyPoints;
use App\Models\LoyaltyTransaction;
use App\Models\LoyaltySettings;
use Modules\Sales\Models\Customer;
use Illuminate\Http\Request;

/**
 * Loyalty Controller for Loyalty Program Management
 */
class LoyaltyController extends Controller
{
    /**
     * Loyalty Program Dashboard
     */
    public function index()
    {
        $topCustomers = LoyaltyPoints::with('customer')
            ->orderByDesc('current_balance')
            ->limit(20)
            ->get();

        $recentTransactions = LoyaltyTransaction::with('customer')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $stats = [
            'total_points_issued' => LoyaltyPoints::sum('total_earned'),
            'total_points_redeemed' => LoyaltyPoints::sum('total_redeemed'),
            'total_current_balance' => LoyaltyPoints::sum('current_balance'),
            'active_members' => LoyaltyPoints::where('current_balance', '>', 0)->count(),
        ];

        return view('loyalty.index', compact('topCustomers', 'recentTransactions', 'stats'));
    }

    /**
     * Customer Loyalty Details
     */
    public function show(Customer $customer)
    {
        $loyalty = LoyaltyPoints::getOrCreate($customer->id);
        $transactions = LoyaltyTransaction::where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('loyalty.show', compact('customer', 'loyalty', 'transactions'));
    }

    /**
     * Add points manually
     */
    public function addPoints(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer|min:1',
            'description' => 'required|string|max:200',
        ]);

        $loyalty = LoyaltyPoints::getOrCreate($validated['customer_id']);
        $loyalty->addPoints($validated['points'], $validated['description'], 'Manual', 0);

        return back()->with('success', 'تم إضافة النقاط بنجاح');
    }

    /**
     * Redeem points
     */
    public function redeemPoints(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string|max:200',
        ]);

        $loyalty = LoyaltyPoints::getOrCreate($validated['customer_id']);

        if (!LoyaltySettings::canRedeem($validated['points'])) {
            return back()->with('error', 'الحد الأدنى للاستبدال لم يتحقق');
        }

        $transaction = $loyalty->redeemPoints(
            $validated['points'],
            $validated['description'] ?? 'استبدال نقاط',
            'Manual',
            0
        );

        if (!$transaction) {
            return back()->with('error', 'رصيد النقاط غير كافٍ');
        }

        $value = LoyaltySettings::calculatePointsValue($validated['points']);

        return back()->with('success', "تم استبدال {$validated['points']} نقطة بقيمة {$value} ج.م");
    }

    /**
     * Get loyalty settings page
     */
    public function settings()
    {
        $settings = [
            'points_per_amount' => LoyaltySettings::getValue('points_per_amount', 1),
            'amount_per_point' => LoyaltySettings::getValue('amount_per_point', 10),
            'points_value' => LoyaltySettings::getValue('points_value', 0.1),
            'min_redeem_points' => LoyaltySettings::getValue('min_redeem_points', 100),
            'expiry_days' => LoyaltySettings::getValue('expiry_days', 365),
        ];

        return view('loyalty.settings', compact('settings'));
    }

    /**
     * Update loyalty settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'points_per_amount' => 'required|numeric|min:0.01',
            'amount_per_point' => 'required|numeric|min:1',
            'points_value' => 'required|numeric|min:0.01',
            'min_redeem_points' => 'required|integer|min:1',
            'expiry_days' => 'required|integer|min:30',
        ]);

        foreach ($validated as $key => $value) {
            LoyaltySettings::setValue($key, $value);
        }

        return back()->with('success', 'تم تحديث إعدادات برنامج الولاء');
    }

    /**
     * API: Get customer loyalty info (for POS)
     */
    public function getCustomerLoyalty(Customer $customer)
    {
        $loyalty = LoyaltyPoints::getOrCreate($customer->id);

        return response()->json([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'current_balance' => $loyalty->current_balance,
            'points_value' => $loyalty->getPointsValue(),
            'tier' => $loyalty->tier,
            'tier_badge_class' => $loyalty->getTierBadgeClass(),
            'can_redeem' => LoyaltySettings::canRedeem($loyalty->current_balance),
            'min_redeem' => LoyaltySettings::getValue('min_redeem_points', 100),
        ]);
    }

    /**
     * Loyalty Report
     */
    public function report(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $earned = LoyaltyTransaction::where('type', 'earn')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('points');

        $redeemed = LoyaltyTransaction::where('type', 'redeem')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('points');

        $tierBreakdown = LoyaltyPoints::selectRaw('tier, COUNT(*) as count, SUM(current_balance) as total_points')
            ->groupBy('tier')
            ->get();

        $topEarners = LoyaltyTransaction::where('type', 'earn')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('customer')
            ->selectRaw('customer_id, SUM(points) as total')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('loyalty.report', compact('startDate', 'endDate', 'earned', 'redeemed', 'tierBreakdown', 'topEarners'));
    }
}

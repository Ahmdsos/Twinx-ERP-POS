<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceEmail;
use App\Mail\LowStockAlert;
use App\Mail\DailyReport;
use Modules\Sales\Models\SalesInvoice;
use Modules\Inventory\Models\Product;
use Modules\Sales\Models\Customer;

/**
 * NotificationsController
 * Handles email notifications and alerts at the Core module level
 */
class NotificationsController extends Controller
{
    /**
     * Send invoice to customer via email
     */
    public function sendInvoice(SalesInvoice $invoice)
    {
        if (!$invoice->customer?->email) {
            return back()->with('error', 'العميل ليس لديه بريد إلكتروني');
        }

        Mail::to($invoice->customer->email)->send(new InvoiceEmail($invoice));

        return back()->with('success', 'تم إرسال الفاتورة إلى ' . $invoice->customer->email);
    }

    /**
     * Send low stock alert to admin
     */
    public function sendLowStockAlert()
    {
        $products = Product::where('is_active', true)
            ->get()
            ->filter(function ($p) {
                $stock = $p->getTotalStock();
                return $stock <= $p->min_stock;
            });

        if ($products->isEmpty()) {
            return back()->with('info', 'لا توجد منتجات منخفضة المخزون');
        }

        $adminEmail = config('mail.admin_email', 'admin@twinx.com');
        Mail::to($adminEmail)->send(new LowStockAlert($products));

        return back()->with('success', 'تم إرسال تنبيه المخزون المنخفض');
    }

    /**
     * Send daily report to management
     */
    public function sendDailyReport()
    {
        $today = now()->format('Y-m-d');

        $stats = [
            'sales_total' => SalesInvoice::whereDate('invoice_date', $today)->sum('total_amount'),
            'sales_count' => SalesInvoice::whereDate('invoice_date', $today)->count(),
            'cash_collected' => SalesInvoice::whereDate('invoice_date', $today)->sum('paid_amount'),
            'new_customers' => Customer::whereDate('created_at', $today)->count(),
            'low_stock_count' => Product::where('is_active', true)
                ->get()
                ->filter(fn($p) => $p->getTotalStock() <= $p->min_stock)
                ->count(),
            'top_products' => $this->getTopProductsToday(),
        ];

        $adminEmail = config('mail.admin_email', 'admin@twinx.com');
        Mail::to($adminEmail)->send(new DailyReport($stats));

        return back()->with('success', 'تم إرسال التقرير اليومي');
    }

    /**
     * Get top selling products today
     */
    protected function getTopProductsToday(): array
    {
        $today = now()->format('Y-m-d');

        return \Modules\Sales\Models\SalesInvoiceLine::whereHas('invoice', function ($q) use ($today) {
            $q->whereDate('invoice_date', $today);
        })
            ->with('product')
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(line_total) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn($line) => [
                'name' => $line->product?->name ?? 'منتج',
                'quantity' => $line->total_qty,
                'revenue' => $line->total_revenue,
            ])
            ->toArray();
    }

    /**
     * Settings page for email notifications
     */
    public function settings()
    {
        return view('notifications.settings');
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            Mail::raw('هذه رسالة اختبار من نظام Twinx ERP', function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('اختبار البريد الإلكتروني - Twinx ERP');
            });

            return back()->with('success', 'تم إرسال رسالة الاختبار بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل الإرسال: ' . $e->getMessage());
        }
    }
}

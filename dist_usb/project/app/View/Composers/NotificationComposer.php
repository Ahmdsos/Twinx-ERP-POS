<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Modules\Inventory\Models\Product;
use Modules\Sales\Models\SalesOrder;
use Modules\Sales\Enums\SalesOrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Notifications\SystemAlert;

class NotificationComposer
{
    public function compose(View $view)
    {
        $user = Auth::user();
        if (!$user)
            return;

        // Perform Checks & Create DB Notifications if needed
        // (In a real production app, this should be a Scheduled Job, but for immediate effect here we check on load)
        // We use Cache to prevent running this heavy check on EVERY page load (e.g. run once every 5 mins per user)

        \Illuminate\Support\Facades\Cache::remember('notifications_check_' . $user->id, 300, function () use ($user) {

            // 1. Low Stock Logic
            $lowStockProducts = Product::where('reorder_level', '>', 0)
                ->where('is_active', true)
                ->get()
                ->filter(function ($product) {
                    return $product->total_stock <= $product->reorder_level;
                })
                ->take(5);

            foreach ($lowStockProducts as $product) {
                // Check if we already notified about this today
                $exists = $user->notifications()
                    ->where('type', SystemAlert::class)
                    ->where('data->title', 'like', "%{$product->name}%")
                    ->whereDate('created_at', today())
                    ->exists();

                if (!$exists) {
                    $user->notify(new SystemAlert(
                        'warning',
                        'bi-box-seam',
                        'تحذير مخزون: ' . $product->name,
                        'الكمية الحالية (' . $product->total_stock . ') أقل من الحد الأدنى.',
                        route('products.show', $product->id)
                    ));
                }
            }

            // 2. Pending Sales Orders
            $pendingOrders = SalesOrder::whereIn('status', [SalesOrderStatus::CONFIRMED])
                ->whereDate('created_at', '>=', now()->subHours(24)) // fresh ones
                ->take(5)
                ->get();

            foreach ($pendingOrders as $order) {
                $exists = $user->notifications()
                    ->where('type', SystemAlert::class)
                    ->where('data->url', 'like', "%" . $order->id)
                    ->exists();

                if (!$exists) {
                    $user->notify(new SystemAlert(
                        'info',
                        'bi-receipt',
                        'طلب بيع جديد: ' . $order->so_number,
                        'العميل: ' . ($order->customer->name ?? 'Unknown'),
                        route('sales-orders.show', $order->id)
                    ));
                }
            }
            return true;
        });

        // Pass Unread Notifications from DB
        $notifications = $user->unreadNotifications()->take(10)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'type' => $n->data['type'] ?? 'info',
                'icon' => $n->data['icon'] ?? 'bi-bell',
                'title' => $n->data['title'] ?? 'إشعار',
                'description' => $n->data['description'] ?? '',
                'time' => $n->created_at,
                'url' => $n->data['url'] ?? '#',
            ];
        });

        $view->with('dashboardNotifications', $notifications);
    }
}

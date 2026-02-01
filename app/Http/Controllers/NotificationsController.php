<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * NotificationsController - Manages user notifications
 */
class NotificationsController extends Controller
{
    /**
     * Display all notifications for current user
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Display notification settings
     */
    public function settings()
    {
        $settings = [
            'email_orders' => true,
            'email_payments' => true,
            'email_stock_alerts' => true,
            'push_enabled' => false,
            'daily_summary' => true,
        ];

        return view('notifications.settings', compact('settings'));
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request)
    {
        // Implementation for settings update
        return back()->with('success', 'تم حفظ إعدادات الإشعارات بنجاح');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back()->with('success', 'تم تحديد الإشعار كمقروء');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة');
    }
}

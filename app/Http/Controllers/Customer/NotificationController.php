<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        $unreadCount = $request->user()
            ->unreadNotifications()
            ->count();

        return view(
            'customer.notifications.index',
            compact(
                'notifications',
                'unreadCount',
            ),
        );
    }

    public function markAsRead(
        Request $request,
        string $notification,
    ): RedirectResponse {
        $databaseNotification = $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $databaseNotification->markAsRead();

        return redirect()
            ->route('customer.notifications.index')
            ->with(
                'notification_status',
                'The notification was marked as read.',
            );
    }

    public function markAllAsRead(
        Request $request,
    ): RedirectResponse {
        $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return redirect()
            ->route('customer.notifications.index')
            ->with(
                'notification_status',
                'All notifications were marked as read.',
            );
    }
}

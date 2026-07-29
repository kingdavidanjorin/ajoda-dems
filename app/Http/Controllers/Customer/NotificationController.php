<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

final class NotificationController extends Controller
{
    /**
     * Only these known notification events are allowed to generate links.
     *
     * @var array<string, string>
     */
    private const DESTINATION_ROUTES = [
        'inspection_scheduled' => 'customer.inspections.index',
        'inspection_reminder' => 'customer.inspections.index',
        'handover_scheduled' => 'customer.handovers.index',
        'handover_reminder' => 'customer.handovers.index',
    ];

    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        $notifications->getCollection()->each(
            function (DatabaseNotification $notification): void {
                $notification->setAttribute(
                    'destination_url',
                    $this->destinationUrl(
                        $notification->data,
                    ),
                );
            },
        );

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

    /**
     * @param array<string, mixed> $data
     */
    private function destinationUrl(array $data): ?string
    {
        $event = $data['event'] ?? null;

        if (! is_string($event)) {
            return null;
        }

        $routeName = self::DESTINATION_ROUTES[$event] ?? null;

        if ($routeName === null) {
            return null;
        }

        return route($routeName);
    }
}

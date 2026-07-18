<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Lessee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Mark a single notification of the authenticated lessee as read.
     */
    public function markAsRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        /** @var Lessee $lessee */
        $lessee = $request->user();

        abort_unless(
            $notification->notifiable_type === Lessee::class && $notification->notifiable_id === $lessee->id,
            404
        );

        $notification->markAsRead();

        return back();
    }

    /**
     * Mark every unread notification of the authenticated lessee as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}

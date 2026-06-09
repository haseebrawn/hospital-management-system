<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()
                ->latest()
                ->limit(12)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'read_at' => optional($notification->read_at)->toIso8601String(),
                    'created_at' => optional($notification->created_at)->toIso8601String(),
                    ...$notification->data,
                ])
                ->values(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => 'ok',
            'unread_count' => 0,
        ]);
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $notificationRecord = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if (is_null($notificationRecord->read_at)) {
            $notificationRecord->markAsRead();
        }

        return response()->json([
            'status' => 'ok',
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized or user not found.'
            ], 401);
        }

        $notifications = $user->notifications;

        if ($notifications->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No notifications found.',
                'notifications' => []
            ], 200);
        }

        return response()->json([
            'status' => true,
            'notifications' => $notifications
        ], 200);
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized or user not found.'
            ], 401);
        }

        if ($user->unreadNotifications->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No unread notifications found.'
            ], 200);
        }

        $user->unreadNotifications->markAsRead();

        return response()->json([
            'status' => true,
            'message' => 'All notifications marked as read successfully.'
        ], 200);
    }
}

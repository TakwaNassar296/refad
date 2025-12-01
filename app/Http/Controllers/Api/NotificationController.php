<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\NotificationResource;
use App\Models\UserNotification;

class NotificationController extends Controller
{
    /**
     * List all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $notifications = UserNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', $user::class)
            ->latest()
            ->get();

        $unreadCount = $notifications->whereNull('read_at')->count();

        return response()->json([
            'success' => true,
            'message' => $notifications->isEmpty()
                ? __('messages.no_notifications_found')
                : __('messages.list_retrieved'),
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'total' => $notifications->count(),
                'unread_count' => $unreadCount,
            ]
        ], 200);
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = UserNotification::where('id', $id)
            ->where('notifiable_id', Auth::id())
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => __('messages.marked_as_read'),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        UserNotification::where('notifiable_id', Auth::id())
            ->where('notifiable_type', Auth::user()::class)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => __('messages.all_marked_as_read'),
        ]);
    }

    /**
     * Delete a single notification
     */
    public function destroy(string $id): JsonResponse
    {
        $notification = UserNotification::where('id', $id)
            ->where('notifiable_id', Auth::id())
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.deleted'),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseNotificationService;

abstract class Controller
{
    // Send to any user
    protected function notifyUser(int $userId, string $title, string $message, array $data = [])
    {
        app(FirebaseNotificationService::class)->sendCustomNotification(
            title: $title,
            message: $message,
            userIds: [$userId],
            sendToAll: false,
            additionalData: $data
        );
    }

    // Send to admin only
    protected function notifyAdmin(string $title, string $message, array $data = [])
    {
        $admin = User::where('role', 'admin')
            ->whereNotNull('fcm_token')
            ->first();

        if (!$admin) {
            return;
        }

        $this->notifyUser(
            $admin->id,
            $title,
            $message,
            $data
        );
    }

    // Send to multiple users
    protected function notifyUsers(array $userIds, string $title, string $message, array $data = [])
    {
        if (empty($userIds)) return;

        app(FirebaseNotificationService::class)->sendCustomNotification(
            title: $title,
            message: $message,
            userIds: $userIds,
            sendToAll: false,
            additionalData: $data
        );
    }

}

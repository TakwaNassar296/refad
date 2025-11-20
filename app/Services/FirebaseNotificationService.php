<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Notification;
use App\Models\UserNotification;
use Google_Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient;

class FirebaseNotificationService
{
    protected $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/firebase/firebase_credentials.json');
    }

    public function sendCustomNotification(?string $title, string $message, array $userIds = [], bool $sendToAll = false, array $additionalData = []): array
    {
        $failedUsers = [];

        try {
            if ($sendToAll) {
                $users = User::whereNotNull('fcm_token')->get();
            } elseif (!empty($userIds)) {
                $users = User::whereIn('id', $userIds)->whereNotNull('fcm_token')->get();
            } else {
                throw new Exception(__('notifications.no_recipients'));
            }

            foreach ($users as $user) {
                try {
                    $this->sendToSingleUser(
                        $user,
                        $title ?? __('notifications.default_title'),
                        $message,
                        $additionalData
                    );
                } catch (Exception $e) {
                    $failedUsers[] = $user->name ?? "User #{$user->id}";
                    Log::warning(__('notifications.failed_user_log', ['id' => $user->id, 'error' => $e->getMessage()]));
                }
            }

            return [
                'success' => count($failedUsers) === 0,
                'failed_users' => $failedUsers,
                'message' => count($failedUsers) === 0
                    ? __('notifications.all_sent_success')
                    : __('notifications.some_failed', ['count' => count($failedUsers)]),
            ];

        } catch (Exception $e) {
            Log::error(__('notifications.firebase_error') . $e->getMessage());
            return [
                'success' => false,
                'failed_users' => ['System error: ' . $e->getMessage()],
                'message' => __('notifications.system_error'),
            ];
        }
    }

    private function sendToSingleUser(User $user, string $title, string $message, array $additionalData = [])
    {
        $this->saveToDatabase($user, $title, $message, 'admin_notification', $additionalData);
        
        if ($user->fcm_token) {
            $this->sendToDevice($user->fcm_token, $title, $message, $additionalData);
        }
    }

    private function saveToDatabase(User $user, string $title, string $message, string $type, array $additionalData = [])
    {
        $notificationData = [
            'title' => $title,
            'message' => $message, 
        ];

        if (!empty($additionalData)) {
            $notificationData = array_merge($notificationData, $additionalData);
        }

        UserNotification::create([
            'type' => $type,
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'notification_title' => $title,
            'data' => $message, 
            'read_at' => null,
        ]);
    }

    private function sendToDevice(string $fcmToken, string $title, string $body, array $additionalData = [])
    {
        if (!file_exists($this->credentialsPath)) {
            throw new Exception(__('notifications.credentials_missing'));
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($this->credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            $accessToken = $client->getAccessToken();

            if (empty($accessToken['access_token'])) {
                throw new Exception(__('notifications.token_missing'));
            }

            $fcmData = [
                'type' => 'chat_message',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'title' => (string) $title,    
                'body' => (string) $body,      
            ];

            if (!empty($additionalData)) {
                foreach ($additionalData as $key => $value) {
                    $fcmData[$key] = (string) $value; 
                }
            }

            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $fcmData,
                    'android' => [
                        'priority' => 'high',
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'content-available' => 1,
                            ],
                        ],
                        'headers' => [
                            'apns-priority' => '5',
                        ],
                    ],
                ],
            ];

            $httpClient = new GuzzleClient();
            $response = $httpClient->post("https://fcm.googleapis.com/v1/projects/" . env('FIREBASE_PROJECT_ID') . "/messages:send", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken['access_token'],
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 10,
            ]);

            Log::info(__('notifications.sent_success'));

        } catch (Exception $e) {
            Log::error(__('notifications.sending_error') . $e->getMessage());
            throw new Exception(__('notifications.device_send_failed'));
        }
    }
}
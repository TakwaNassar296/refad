<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function userActivityLogs(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('messages.user_not_found'),
                'data' => null,
            ], 404);
        }

        $logs = Activity::where('causer_id', $user->id)
            ->where('causer_type', User::class)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'description' => __($log->description),
                    'subject_type' => $log->subject_type,
                    'subject_id' => $log->subject_id,
                    'causer_id' => $log->causer_id,
                    'properties' => $log->properties,
                    'created_at' => $log->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => __('messages.activity_logs_fetched'),
            'data' => $logs,
        ], 200);
    }
}

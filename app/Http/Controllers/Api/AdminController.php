<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class AdminController extends Controller
{
    public function pendingDelegates(): JsonResponse
    {
        $delegates = User::where('role', 'delegate')
            ->where('status', 'pending')
            ->get();

        if ($delegates->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => __('auth.no_pending_delegates'),
                'data' => []
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => __('auth.pending_delegates_list'),
            'data' => UserResource::collection($delegates)
        ]);
    }

    public function approveDelegate(Request $request, User $delegate): JsonResponse
    {
        $request->validate([
            'camp_id' => 'required|exists:camps,id'
        ]);

        if (!$delegate->isDelegate() || !$delegate->isPending()) {
            return response()->json([
                'status' => false,
                'message' => __('auth.delegate_cannot_be_approved'),
                'data' => null
            ], 422);
        }

        $delegate->update([
            'status' => 'approved',
            'camp_id' => $request->camp_id
        ]);

        return response()->json([
            'status' => true,
            'message' => __('auth.delegate_approved_successfully'),
            'data' => new UserResource($delegate)
        ]);
    }

    public function rejectDelegate(User $delegate): JsonResponse
    {
        if (!$delegate->isDelegate() || !$delegate->isPending()) { 
            return response()->json([
                'status' => false,
                'message' => __('auth.delegate_cannot_be_rejected'),
                'data' => null
            ], 422);
        }

        $delegate->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'status' => true,
            'message' => __('auth.delegate_rejected_successfully'),
            'data' => new UserResource($delegate)
        ]);
    }
}

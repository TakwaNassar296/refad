<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\ContributionResource;

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


   /* public function decision(Request $request, $id): JsonResponse
    {
        $contribution = Contribution::find($id);

        if (!$contribution) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'delegate_id' => 'nullable|exists:users,id',
        ]);

        if ($validated['status'] === 'approved') {
            if (empty($validated['delegate_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.delegate_required_for_approval'),
                    'data' => null,
                ], 400);
            }

            $contribution->update([
                'status' => 'approved',
                'delegate_id' => $validated['delegate_id'],
            ]);

            $message = __('messages.contribution_approved');
        } else {
            $contribution->update([
                'status' => 'rejected',
                'delegate_id' => null,
            ]);

            $message = __('messages.contribution_rejected');
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => new ContributionResource($contribution->load(['user','delegate','project','families'])),
        ], 200);
    }*/

}

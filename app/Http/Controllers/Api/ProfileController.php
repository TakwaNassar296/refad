<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => __('messages.profile_retrieved_successfully'),
            'data' => new UserResource($request->user()),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|unique:users,phone,' . $user->id,
            'backup_phone' => 'sometimes|string|unique:users,backup_phone,' . $user->id,
            'id_number' => 'sometimes|string|unique:users,id_number,' . $user->id,
            'license_number' => 'sometimes|string|max:100',
            'admin_position' => 'sometimes|in:foundation,assistant,other',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => __('messages.profile_updated_successfully'),
            'data' => new UserResource($user),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|confirmed|min:8',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.current_password_incorrect'),
                'data' => null
            ], 422);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => __('messages.password_changed_successfully'),
            'data' => null
        ]);
    }
}

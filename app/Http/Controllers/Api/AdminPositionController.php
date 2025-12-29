<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\AdminPosition;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPositionResource;

class AdminPositionController extends Controller
{
    public function index(): JsonResponse
    {
        $positions = AdminPosition::all();
        return response()->json([
            'success' => true,
            'message' => __('messages.admin_positions_retrieved_successfully'),
            'data' => AdminPositionResource::collection($positions),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:admin_positions,name',
        ]);

        $position = AdminPosition::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.admin_position_added_successfully'),
            'data' => new AdminPositionResource($position),
        ], 201);
    }

    public function show(AdminPosition $adminPosition): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('messages.admin_position_retrieved_successfully'),
            'data' => new AdminPositionResource($adminPosition),
        ]);
    }

    public function update(Request $request, AdminPosition $adminPosition): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:admin_positions,name,' . $adminPosition->id,
        ]);

        $adminPosition->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.admin_position_updated_successfully'),
            'data' => new AdminPositionResource($adminPosition),
        ]);
    }

    public function destroy(AdminPosition $adminPosition): JsonResponse
    {
        $adminPosition->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.admin_position_deleted_successfully'),
            'data' => null,
        ]);
    }
}

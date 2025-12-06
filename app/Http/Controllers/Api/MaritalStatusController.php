<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MaritalStatus;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\MaritalStatusResource;
use App\Http\Requests\StoreMaritalStatusRequest;
use App\Http\Requests\UpdateMaritalStatusRequest;

class MaritalStatusController extends Controller
{
    public function index(): JsonResponse
    {
        $statuses = MaritalStatus::all();
        return response()->json([
            'success' => true,
            'message' => __('messages.marital_statuses_retrieved_successfully'),
            'data' => MaritalStatusResource::collection($statuses),
        ]);
    }

    public function store(StoreMaritalStatusRequest $request)
    {
        $name = $request->name;

        $existing = MaritalStatus::withTrashed()->where('name', $name)->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.marital_status_already_exists_restored'),
                'data' => new MaritalStatusResource($existing),
            ], 200);
        }

        $status = MaritalStatus::create(['name' => $name]);

        return response()->json([
            'success' => true,
            'message' => __('messages.marital_status_added_successfully'),
            'data' => new MaritalStatusResource($status),
        ], 201);
    }


    public function show(MaritalStatus $maritalStatus): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('messages.marital_status_retrieved_successfully'),
            'data' => new MaritalStatusResource($maritalStatus),
        ]);
    }

    public function update(UpdateMaritalStatusRequest $request, MaritalStatus $maritalStatus): JsonResponse
    {
        $maritalStatus->update($request->validated());
        return response()->json([
            'success' => true,
            'message' => __('messages.marital_status_updated_successfully'),
            'data' => new MaritalStatusResource($maritalStatus),
        ]);
    }

    public function destroy(MaritalStatus $maritalStatus): JsonResponse
    {
        $maritalStatus->delete();
        return response()->json([
            'success' => true,
            'message' => __('messages.marital_status_deleted_successfully'),
            'data' => null,
        ]);
    }
}

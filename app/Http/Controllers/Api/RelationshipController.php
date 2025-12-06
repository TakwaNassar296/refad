<?php

namespace App\Http\Controllers\Api;

use App\Models\Relationship;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRelationshipRequest;
use App\Http\Requests\UpdateRelationshipRequest;
use App\Http\Resources\RelationshipResource;

class RelationshipController extends Controller
{
    public function index(): JsonResponse
    {
        $relationships = Relationship::latest()->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.relationships_retrieved_successfully'),
            'data' => RelationshipResource::collection($relationships),
        ]);
    }

    public function store(StoreRelationshipRequest $request): JsonResponse
    {
        $name = $request->name;

        $relationship = Relationship::withTrashed()->where('name', $name)->first();

        if ($relationship?->trashed()) {
            $relationship->restore();

            return response()->json([
                'success' => true,
                'message' => __('messages.relationship_restored_successfully'),
                'data' => new RelationshipResource($relationship),
            ]);
        }

        $relationship = Relationship::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('messages.relationship_added_successfully'),
            'data' => new RelationshipResource($relationship),
        ]);
    }

    public function show(Relationship $relationship): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('messages.relationship_retrieved_successfully'),
            'data' => new RelationshipResource($relationship),
        ]);
    }

    public function update(UpdateRelationshipRequest $request, Relationship $relationship): JsonResponse
    {
        $relationship->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('messages.relationship_updated_successfully'),
            'data' => new RelationshipResource($relationship),
        ]);
    }

    public function destroy(Relationship $relationship): JsonResponse
    {
        $relationship->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.relationship_deleted_successfully'),
        ]);
    }
}

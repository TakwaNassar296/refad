<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MedicalCondition;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\MedicalConditionResource;
use App\Http\Requests\StoreMedicalConditionRequest;
use App\Http\Requests\UpdateMedicalConditionRequest;

class MedicalConditionController extends Controller
{
    public function index(): JsonResponse
    {
        $conditions = MedicalCondition::all();
        return response()->json([
            'success' => true,
            'message' => __('messages.medical_conditions_retrieved_successfully'),
            'data' => MedicalConditionResource::collection($conditions),
        ]);
    }

    public function store(StoreMedicalConditionRequest $request)
    {
        $name = $request->name;

        $existing = MedicalCondition::withTrashed()->where('name', $name)->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.medical_condition_already_exists_restored'),
                'data' => new MedicalConditionResource($existing),
            ], 200);
        }

        $condition = MedicalCondition::create(['name' => $name]);

        return response()->json([
            'success' => true,
            'message' => __('messages.medical_condition_added_successfully'),
            'data' => new MedicalConditionResource($condition),
        ], 201);
    }


    public function show(MedicalCondition $medicalCondition): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('messages.medical_condition_retrieved_successfully'),
            'data' => new MedicalConditionResource($medicalCondition),
        ]);
    }

    public function update(UpdateMedicalConditionRequest $request, MedicalCondition $medicalCondition): JsonResponse
    {
        $medicalCondition->update($request->validated());
        return response()->json([
            'success' => true,
            'message' => __('messages.medical_condition_updated_successfully'),
            'data' => new MedicalConditionResource($medicalCondition),
        ]);
    }

    public function destroy(MedicalCondition $medicalCondition): JsonResponse
    {
        $medicalCondition->delete();
        return response()->json([
            'success' => true,
            'message' => __('messages.medical_condition_deleted_successfully'),
            'data' => null,
        ]);
    }
}

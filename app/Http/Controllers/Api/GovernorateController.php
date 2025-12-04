<?php

namespace App\Http\Controllers\Api;

use App\Models\Governorate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\GovernorateResource;
use App\Http\Requests\StoreGovernorateRequest;
use App\Http\Requests\UpdateGovernorateRequest;

class GovernorateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $governorates = Governorate::all(); 

        return response()->json([
            'success' => true,
            'message' => __('messages.governorates_retrieved'),
            'data' => GovernorateResource::collection($governorates)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGovernorateRequest $request)
    {
        $governorate = Governorate::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('messages.governorate_created'),
            'data' => new GovernorateResource($governorate)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Governorate $governorate)
    {
        return response()->json([
            'success' => true,
            'message' => __('messages.governorate_retrieved'),
            'data' => new GovernorateResource($governorate)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGovernorateRequest $request, Governorate $governorate)
    {
        $governorate->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('messages.governorate_updated'),
            'data' => new GovernorateResource($governorate)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Governorate $governorate)
    {
        $governorate->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.governorate_deleted'),
            'data' => null
        ]);
    }
}

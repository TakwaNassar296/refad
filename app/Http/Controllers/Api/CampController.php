<?php

namespace App\Http\Controllers\Api;

use App\Models\Camp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CampResource;
use App\Http\Requests\StoreCampRequest;
use App\Http\Requests\UpdateCampRequest;

class CampController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->isAdmin()) {
            $camps = Camp::with('delegates')->get();
            return response()->json([
                'status' => true,
                'message' => __('messages.retrieved_successfully'),
                'data' => CampResource::collection($camps)
            ]);
        } elseif ($user->isDelegate()) {
            $camp = Camp::with('delegates')->find($user->camp_id);
            
            if (!$camp) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.no_assigned_camp'),
                    'data' => null
                ], 404);
            }
            
            return response()->json([
                'status' => true,
                'message' => __('messages.retrieved_successfully'),
                'data' => new CampResource($camp)
            ]);
        }
        
        return response()->json([
            'status' => false,
            'message' => __('auth.unauthorized'),
            'data' => null
        ], 403);
    }

    public function store(StoreCampRequest $request): JsonResponse
    {
        $camp = Camp::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => __('messages.created_successfully'),
            'data' => new CampResource($camp)
        ], 201);
    }

    public function show(Request $request, $slug): JsonResponse
    {
        $user = $request->user();
        
        $camp = Camp::where('slug', $slug)->first();

        if (!$camp) {
            return response()->json([
                'status' => false,
                'message' => __('messages.not_found'),
                'data' => null
            ], 404);
        }

        if ($user->isAdmin() || ($user->isDelegate() && $user->camp_id == $camp->id)) {
            return response()->json([
                'status' => true,
                'message' => __('messages.retrieved_successfully'),
                'data' => new CampResource($camp->load('delegates'))
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => __('auth.unauthorized'),
            'data' => null
        ], 403);
    }


    public function update(UpdateCampRequest $request, $slug): JsonResponse
    {
        $user = $request->user();
        $camp = Camp::where('slug', $slug)->first();

        if (!$camp) {
            return response()->json([
                'status' => false,
                'message' => __('messages.not_found'),
                'data' => null
            ], 404);
        }

        if ($user->isAdmin()) {
        } elseif ($user->isDelegate() && $user->camp_id == $camp->id) {
        } else {
            return response()->json([
                'status' => false,
                'message' => __('auth.unauthorized'),
                'data' => null
            ], 403);
        }

        $validated = $request->validated();
        $camp->update($validated);

        return response()->json([
            'status' => true,
            'message' => __('messages.updated_successfully'),
            'data' => new CampResource($camp->fresh()->load('delegates'))
        ]);
    }


    public function destroy(Request $request,  $slug): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'status' => false,
                'message' => __('auth.unauthorized'),
                'data' => null
            ], 403);
        }

        $camp = Camp::where('slug', $slug)->first();

        if (!$camp) {
            return response()->json([
                'status' => false,
                'message' => __('messages.not_found'),
                'data' => null
            ], 404);
        }

        $camp->delete();

        return response()->json([
            'status' => true,
            'message' => __('messages.deleted_successfully'),
            'data' => null
        ]);
    }

}

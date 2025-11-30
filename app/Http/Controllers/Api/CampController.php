<?php

namespace App\Http\Controllers\Api;

use App\Models\Camp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CampResource;
use App\Http\Requests\StoreCampRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateCampRequest;

class CampController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Camp::with(['projects' => function($q) {
           $q->whereIn('status', ['pending', 'in_progress','delivered'])->where('is_approved', true);
        }]);

        if ($request->has('name') && $request->name) {
            $query->where(function ($q) use ($request) {
                $q->where('name->ar', 'like', '%' . $request->name . '%')
                ->orWhere('name->en', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->has('location') && $request->location) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('family_count') && $request->family_count) {
            $query->where('family_count', $request->family_count);
        }

        $camps = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.camps_list_fetched'),
            'data' => CampResource::collection($camps),
        ]);
    }


    public function store(StoreCampRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('camp_img')) {
            $data['camp_img'] = $request->file('camp_img')->store('camps', 'public');
        }

        $camp = Camp::create($data);

        return response()->json([
            'status' => true,
            'message' => __('messages.created_successfully'),
            'data' => new CampResource($camp)
        ], 201);
    }

    public function show($slug): JsonResponse
    {
        
        $camp = Camp::where('slug', $slug)->first();

        if (!$camp) {
            return response()->json([
                'status' => false,
                'message' => __('messages.not_found'),
                'data' => null
            ], 404);
        }

        $camp->load(['projects' => function($q) {
           $q->whereIn('status', ['pending', 'in_progress' , 'delivered'])->where('is_approved', true);
        }]);



        return response()->json([
            'status' => true,
            'message' => __('messages.retrieved_successfully'),
            'data' => new CampResource($camp->load('projects'))
        ]);
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

        $data = $request->validated();

            if ($request->hasFile('camp_img')) {
            if ($camp->camp_img && Storage::disk('public')->exists($camp->camp_img)) {
                Storage::disk('public')->delete($camp->camp_img);
            }
            $data['camp_img'] = $request->file('camp_img')->store('camps', 'public');
        }

        $camp->update($data);
       

        return response()->json([
            'status' => true,
            'message' => __('messages.updated_successfully'),
            'data' => new CampResource($camp->fresh())
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

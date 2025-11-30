<?php

namespace App\Http\Controllers\Api;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FamilyResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreFamilyRequest;
use App\Http\Requests\UpdateFamilyRequest;
use Illuminate\Validation\ValidationException;

class FamilyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Family::with('camp');

        if ($user->role === 'delegate') {
            $query->where('camp_id', $user->camp_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where('family_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('total_members')) {
            $query->where('total_members', $request->total_members);
        }

        if ($request->has('medical_conditions_count')) {
            $query->where('medical_conditions_count', $request->medical_conditions_count);
        }

        $families = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.families_retrieved_successfully'),
            'data' => FamilyResource::collection($families), 
        ]);
    }

    public function store(StoreFamilyRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user->isDelegate()) {
                $campId = $user->camp_id;
            } elseif ($user->isAdmin()) {
                $request->validate([
                    'camp_id' => 'required|exists:camps,id'
                ]);
                $campId = $request->camp_id;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.unauthorized'),
                    'data' => null,
                ], 403);
            }

            $filePath = $request->hasFile('file') 
                ? $request->file('file')->store('families', 'public') 
                : null;

            $family = Family::create([
                'camp_id' => $campId,
                'added_by' => $user->id,
                'family_name' => $request->family_name,
                'father_name' => $request->father_name,
                'national_id' => $request->national_id,
                'dob' => $request->dob,
                'phone' => $request->phone,
                'email' => $request->email,
                'total_members' => $request->total_members,
                'elderly_count' => $request->elderly_count,
                'medical_conditions_count' => $request->medical_conditions_count,
                'children_count' => $request->children_count,
                'tent_number' => $request->tent_number,
                'location' => $request->location,
                'notes' => $request->notes,
                'file' => $filePath,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.family_added_successfully'),
                'data' => new FamilyResource($family->load('camp')),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        }
    }


    public function show($id): JsonResponse
    {
        $family = Family::with(['camp', 'members'])->find($id);

        if (!$family) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_found')
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate'&& $family->camp_id !== $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied')
            ], 403);
        }
        return response()->json([
            'success' => true,
            'message' => __('messages.family_retrieved_successfully'),
            'data' => new FamilyResource($family),
        ]);
    }

    public function update(UpdateFamilyRequest $request, $id): JsonResponse
    {
        $family = Family::find($id);

        if (!$family) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_found')
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' &&  $family->camp_id !== $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied')
            ], 403);
        }

        $data = $request->validated();

        if ($user->role === 'admin' && $request->has('camp_id')) {
            $data['camp_id'] = $request->camp_id;
        } elseif ($user->role === 'delegate') {
            $data['camp_id'] = $user->camp_id;
        }

        if ($request->hasFile('file')) {
            if ($family->file && Storage::disk('public')->exists($family->file)) {
                Storage::disk('public')->delete($family->file);
            }
            $data['file'] = $request->file('file')->store('families', 'public');
        } else {
            $data['file'] = $family->file;
        }

        $family->update($data);

        return response()->json([
            'success' => true,
            'message' => __('messages.family_updated_successfully'),
            'data' => new FamilyResource($family->load('camp')),
        ]);
    }


    public function destroy($id): JsonResponse
    {
        $family = Family::find($id);

        if (!$family) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_found')
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $family->added_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied')
            ], 403);
        }

        $family->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.family_deleted_successfully')
        ]);
    }
}
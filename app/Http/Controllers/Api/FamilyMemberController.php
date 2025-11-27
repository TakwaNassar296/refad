<?php

namespace App\Http\Controllers\Api;

use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FamilyMemberResource;
use App\Http\Requests\StoreFamilyMemberRequest;
use App\Http\Requests\UpdateFamilyMemberRequest;

class FamilyMemberController extends Controller
{
    public function index($familyId): JsonResponse
    {
        $family = Family::find($familyId);

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

        $members = $family->members()->latest()->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.family_members_retrieved_successfully'),
            'data' => FamilyMemberResource::collection($members)
        ]);
    }


    public function store(StoreFamilyMemberRequest $request, $familyId): JsonResponse
    {
        $family = Family::find($familyId);

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

        $member = $family->members()->create($request->validated());



        return response()->json([
            'success' => true,
            'message' => __('messages.family_member_added_successfully'),
            'data' => new FamilyMemberResource($member)
        ], 201);
    }

    public function show($familyId, $memberId): JsonResponse
    {
        $family = Family::find($familyId);

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

        $member = FamilyMember::where('family_id', $familyId)->find($memberId);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_member_not_found')
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.family_member_retrieved_successfully'),
            'data' => new FamilyMemberResource($member)
        ]);
    }

    public function update(UpdateFamilyMemberRequest $request, $familyId, $memberId): JsonResponse
    {
        $family = Family::find($familyId);

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

        $member = FamilyMember::where('family_id', $familyId)->find($memberId);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_member_not_found')
            ], 404);
        }

        $member->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('messages.family_member_updated_successfully'),
            'data' => new FamilyMemberResource($member)
        ]);
    }

    public function destroy($familyId, $memberId): JsonResponse
    {
        $family = Family::find($familyId);

        if (!$family) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_found')
            ], 404);
        }

        $member = FamilyMember::where('family_id', $familyId)->find($memberId);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_member_not_found')
            ], 404);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.family_member_deleted_successfully')
        ]);
    }
}
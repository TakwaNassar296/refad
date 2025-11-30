<?php

namespace App\Http\Controllers\Api;

use App\Models\Camp;
use App\Models\Project;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CampResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\FamilyResource;
use App\Http\Resources\ContributionResource;
use Illuminate\Support\Facades\Auth;

class ContributorController extends Controller
{

    public function campFamilies(Request $request, $campId): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'contributor') {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $camp = Camp::with('families')->find($campId);

        if (!$camp) {
            return response()->json([
                'success' => false,
                'message' => __('messages.camp_not_found'),
                'data' => null,
            ], 404);
        }

        $familiesQuery = $camp->families();

        if ($request->filled('search')) {
            $search = $request->search;
            $familiesQuery->where('family_name', 'like', "%{$search}%");
        }

        if ($request->filled('medical')) {
            $familiesQuery->where('medical_conditions_count', '>', 0);
        }

        if ($request->filled('children')) {
            $familiesQuery->where('children_count', '>', 0);
        }

        if ($request->filled('elderly')) {
            $familiesQuery->where('elderly_count', '>', 0);
        }

        if ($request->filled('orphans')) {
            $familiesQuery->whereHas('members', function ($q) {
                $q->where('status', 'orphan');
            });
        }


        $families = $familiesQuery->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.camp_families_list_fetched'),
            'data' => FamilyResource::collection($families),
        ], 200);
    }



    public function contribute(Request $request, $projectId): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'contributor') {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $project = Project::with('camp')->find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_found'),
                'data' => null,
            ], 404);
        }

        if (!$project->is_approved) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_approved'),
                'data' => null,
            ], 403);
        }

        if (!in_array($project->status, ['pending', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_open_for_contribution'),
                'data' => null,
            ], 403);
        }

        $validated = $request->validate([
            'contributedQuantity' => 'required|integer|min:1',
            'families' => 'nullable|array',
            'families.*' => 'exists:families,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['contributedQuantity'] > $project->total_remaining) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_exceeds_project_remaining'),
                'data' => null,
            ], 422);
        }

        $validFamilies = [];
        if (!empty($validated['families'])) {
            $validFamilies = $project->camp->families()
                ->whereIn('id', $validated['families'])
                ->pluck('id')
                ->toArray();

            if (empty($validFamilies)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.invalid_families_for_camp'),
                    'data' => null,
                ], 422);
            }
        }

        $contribution = Contribution::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'total_quantity' => $validated['contributedQuantity'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        if (!empty($validFamilies)) {
            $contribution->contributorFamilies()->attach($validFamilies);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.contribution_added_successfully'),
            'data' => new ContributionResource($contribution->load('contributorFamilies')),
        ], 201);
    }

    public function history(): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'contributor') {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $contributions = Contribution::with(['project', 'contributorFamilies'])
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.contribution_history_fetched'),
            'data' => ContributionResource::collection($contributions),
        ], 200);
    }

}

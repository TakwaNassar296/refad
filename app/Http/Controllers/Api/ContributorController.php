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


    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'contributor') {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $query = Camp::with(['projects', 'delegates']);

        if ($request->has('name') && $request->name) {
            $query->where('name->ar', 'like', '%' . $request->name . '%')
                ->orWhere('name->en', 'like', '%' . $request->name . '%');
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



   
    public function projects(Request $request, $campId): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'contributor') {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $query = Project::with(['camp', 'delegate'])
            ->where('camp_id', $campId)
            ->whereIn('status', ['pending', 'in_progress']);

        if ($request->filled('family_name')) {
            $query->whereHas('beneficiaryFamilies', function ($q) use ($request) {
                $q->where('family_name', 'like', '%' . $request->family_name . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $projects = $query->get();

        if ($projects->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.no_projects_found_in_camp'),
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.projects_list_fetched'),
            'data' => ProjectResource::collection($projects),
        ], 200);
    }


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

        $validated = $request->validate([
            'contributedQuantity' => 'required|integer|min:1',
            'families' => 'nullable|array',
            'families.*' => 'exists:families,id',
            'notes' => 'nullable|string|max:1000',
        ]);

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
            'delegate_id' => $project->added_by,
            'total_quantity' => $validated['contributedQuantity'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        if (!empty($validFamilies)) {
            $contribution->families()->attach($validFamilies);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.contribution_added_successfully'),
            'data' => new ContributionResource($contribution->load(['project', 'families'])),
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

        $contributions = Contribution::with(['project', 'families'])
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.contribution_history_fetched'),
            'data' => ContributionResource::collection($contributions),
        ], 200);
    }

   
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'contributor') {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $contribution = Contribution::find($id);

        if (!$contribution || $contribution->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
                'data' => null,
            ], 404);
        }

        if ($contribution->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => __('messages.cannot_edit_approved_contribution'),
                'data' => null,
            ], 400);
        }

        $validated = $request->validate([
            'contributedQuantity' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $contribution->update([
            'total_quantity' => $validated['contributedQuantity'] ?? $contribution->total_quantity,
            'notes' => $validated['notes'] ?? $contribution->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.contribution_updated_successfully'),
            'data' => new ContributionResource($contribution->load(['project', 'families'])),
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'contributor') {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $contribution = Contribution::find($id);

        if (!$contribution || $contribution->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
                'data' => null,
            ], 404);
        }

        if ($contribution->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => __('messages.cannot_delete_approved_contribution'),
                'data' => null,
            ], 400);
        }

        $contribution->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.contribution_deleted_successfully'),
            'data' => null,
        ], 200);
    }
}

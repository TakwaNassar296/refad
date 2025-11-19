<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Family;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProjectResource;

use function Symfony\Component\Clock\now;

class ProjectFamilyController extends Controller
{
    public function index($projectId): JsonResponse
    {
        $project = Project::with(['beneficiaryFamilies'])->find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_found'),
                'data' => null,
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $project->added_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.projects_retrieved_successfully'),
            'data' => new ProjectResource($project),
        ], 200);
    }

    public function store(Request $request, $projectId): JsonResponse
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_found'),
                'data' => null,
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $project->added_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $request->validate([
            'family_id' => 'required|exists:families,id',
            'received_quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $family = Family::find($request->family_id);
        if ($family->camp_id !== $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_in_your_camp'),
                'data' => null,
            ], 403);
        }

        if ($project->beneficiaryFamilies()->where('family_id', $request->family_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_already_added_to_project'),
                'data' => null,
            ], 400);
        }

        $project->beneficiaryFamilies()->attach($request->family_id, [
            'requested_quantity' => null,
            'received_quantity' => $request->received_quantity  ??null,
            'received' => true,
            'support_date' => now(),
            'notes' => $request->notes,
        ]);

        $project->load(['camp', 'delegate', 'beneficiaryFamilies']);

        return response()->json([
            'success' => true,
            'message' => __('messages.family_added_to_project_successfully'),
            'data' => new ProjectResource($project),
        ], 201);
    }

    public function destroy($projectId, $familyId): JsonResponse
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_found'),
                'data' => null,
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $project->added_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $project->beneficiaryFamilies()->detach($familyId);

        return response()->json([
            'success' => true,
            'message' => __('messages.family_removed_from_project_successfully'),
            'data' => null,
        ], 200);
    }

   /* public function markAsBeneficial(Request $request, $projectId): JsonResponse
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_found'),
                'data' => null,
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $project->added_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $request->validate([
            'families' => 'required|array',
            'families.*.id' => 'required|exists:families,id',
            'families.*.received_quantity' => 'required|integer|min:1',
        ]);

        foreach ($request->families as $familyData) {
            $project->beneficiaryFamilies()->updateExistingPivot($familyData['id'], [
                'received' => true,
                'received_quantity' => $familyData['received_quantity'] ?? null,
                'support_date' => now(),
            ]);
        }

        $project->load('beneficiaryFamilies');

        return response()->json([
            'success' => true,
            'message' => __('messages.families_marked_as_beneficial'),
            'data' => new ProjectResource($project),
        ], 200);
    }*/
}

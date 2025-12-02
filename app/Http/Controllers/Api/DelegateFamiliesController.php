<?php

namespace App\Http\Controllers\Api;

use App\Models\Family;
use App\Models\Project;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ContributionResource;

class DelegateFamiliesController extends Controller
{
    public function index($contributionId): JsonResponse
    {
        $contribution = Contribution::with(['delegateFamilies', 'project','contributorFamilies'])->find($contributionId);

        if (!$contribution) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
                'data' => null,
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $contribution->project->camp_id !== $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.contribution_retrieved_successfully'),
            'data' => new ContributionResource($contribution->load('delegateFamilies')),
        ], 200);
    }

    public function store(Request $request, $contributionId): JsonResponse
    {
        $contribution = Contribution::with('project')->find($contributionId);

        if (!$contribution) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
                'data' => null,
            ], 404);
        }


        if ($contribution->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_approved'),
                'data' => null,
            ], 403);
        }


        if (!$contribution->project->is_approved) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_approved_yet'),
                'data' => null,
            ], 400);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $contribution->project->camp_id !== $user->camp_id) {
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

        if ($contribution->delegateFamilies()->where('family_id', $request->family_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_already_added_to_contribution'),
                'data' => null,
            ], 400);
        }

        $contribution->delegateFamilies()->attach($request->family_id, [
            'received_quantity' => $request->received_quantity,
            'notes' => $request->notes,
        ]);
        $project = $contribution->project;

        $project->total_contributions = $project->contributions()->count();

       
        $totalDistributed = 0;
        foreach ($project->contributions as $projectContribution) {
            $totalDistributed += $projectContribution->delegateFamilies()->sum('received_quantity');
        }

        $project->total_received = $totalDistributed; 
        $project->total_remaining = max(0, $project->college - $totalDistributed);

        if ($totalDistributed >= $project->college) {
            $project->status = 'delivered';
        } elseif ($totalDistributed > 0) {
            $project->status = 'in_progress';
        } else {
            $project->status = 'pending';
        }

        $project->save();

        $contribution->load(['delegateFamilies', 'project']);

        $projectName = $contribution->project->name;

        if ($project->status === 'delivered') {
            $adminMessage = __('messages.contribution_updated_admin_delivered', ['project' => $projectName]);
        } else {
            $adminMessage = __('messages.contribution_updated_admin', ['project' => $projectName]);
        }

        $this->notifyAdmin($adminMessage, $adminMessage);

        $this->notifyUser(
            $contribution->user_id,
            __('messages.contribution_updated_contributor', ['project' => $projectName]),
            __('messages.contribution_updated_contributor', ['project' => $projectName])
        );



        return response()->json([
            'success' => true,
            'message' => __('messages.family_added_to_contribution_successfully'),
            'data' => new ContributionResource($contribution->load('delegateFamilies')),
        ], 201);
    }

    public function destroy($contributionId, $familyId): JsonResponse
    {
        $contribution = Contribution::with('project')->find($contributionId);

        if (!$contribution) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
                'data' => null,
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $contribution->project->camp_id !== $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $contribution->delegateFamilies()->detach($familyId);

        $project = $contribution->project;
        $project->total_contributions = $project->contributions()->count();
        
        $totalDistributed = 0;
        foreach ($project->contributions as $projectContribution) {
            $totalDistributed += $projectContribution->delegateFamilies()->sum('received_quantity');
        }
        
        $project->total_received = $totalDistributed;
        $project->total_remaining = max(0, $project->college - $totalDistributed);
        
        if ($totalDistributed >= $project->college) {
            $project->status = 'delivered';
        } elseif ($totalDistributed > 0) {
            $project->status = 'in_progress';
        } else {
            $project->status = 'pending';
        }
        
        $project->save();

        $contribution->load(['delegateFamilies', 'project']);

        return response()->json([
            'success' => true,
            'message' => __('messages.family_removed_from_contribution_successfully'),
            'data' => new ContributionResource($contribution->load('delegateFamilies')),
        ], 200);
    }




}

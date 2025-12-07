<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Camp;
use App\Models\Project;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CampResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FamilyResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ContributionResource;

class ContributorController extends Controller
{

    public function campFamilies(Request $request, $campId = null): JsonResponse
    {
        $user = Auth::user();

        if ($user->isContributor()) {
            if (!$campId) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.camp_id_required'),
                ], 422);
            }
        }

        if ($user->isDelegate()) {
            if (!$user->camp_id) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.delegate_has_no_camp'),
                ], 403);
            }
            $campId = $user->camp_id;
        }

        $camp = Camp::find($campId);
        if (!$camp) {
            return response()->json([
                'success' => false,
                'message' => __('messages.camp_not_found'),
            ], 404);
        }

        $query = $camp->families();

        if ($request->filled('search')) {
            $query->where('family_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('total_members')) {
            $query->where('total_members', $request->total_members);
        }

        if ($request->has('marital_status') && $request->marital_status) {
            $query->whereHas('maritalStatus', function ($q) use ($request) {
                $q->where('name', $request->marital_status);
            });
        }

        if ($request->has('medical_condition') && $request->medical_condition) {
            $query->whereHas('members', function ($q) use ($request) {
                $q->whereHas('medicalCondition', function ($mq) use ($request) {
                    $mq->where('name', $request->medical_condition);
                });
            });
        }

        if ($request->filled('has_children') && $request->has_children) {
            $fourYearsAgo = Carbon::now()->subYears(4)->startOfDay();
            $today = Carbon::now()->endOfDay();

            $query->whereHas('members', function ($q) use ($fourYearsAgo, $today) {
                $q->whereBetween('dob', [$fourYearsAgo, $today]);
            });
        }

        if ($request->filled('year_from') && $request->filled('year_to')) {
            $from = Carbon::createFromDate($request->year_from)->startOfYear();
            $to = Carbon::createFromDate($request->year_to)->endOfYear();

            $query->where(function ($query) use ($from, $to) {
                $query->whereBetween('dob', [$from, $to])
                    ->orWhereHas('members', function ($q) use ($from, $to) {
                        $q->whereBetween('dob', [$from, $to]);
                    });
            });
        }

        $families = $query->get();

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

        $this->notifyAdmin(
            __('messages.new_contribution_title'),
            __('messages.new_contribution_body', [
                'name' => $user->name,
                'project' => $project->name,
                'quantity' => $validated['contributedQuantity'],
            ]),
            [
                'type' => 'new_contribution',
                'contribution_id' => $contribution->id,
                'project_id' => $project->id,
            ]
        );

        $delegate = $project->camp->delegates()->first();
        if ($delegate && $delegate->fcm_token) {
            $this->notifyUser(
                $delegate->id,
                __('messages.new_contribution_title'),
                __('messages.new_contribution_body_delegate', [
                    'name' => $user->name,
                    'project' => $project->name,
                ]),
                [
                    'type' => 'new_contribution',
                    'contribution_id' => $contribution->id,
                    'project_id' => $project->id,
                    'families' => $validFamilies,
                ]
            );
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

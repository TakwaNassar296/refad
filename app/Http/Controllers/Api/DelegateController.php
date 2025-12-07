<?php

namespace App\Http\Controllers\Api;

use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ContributionResource;

class DelegateController extends Controller
{
    public function contributions(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isDelegate()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
            ], 403);
        }

        $status = $request->query('status'); 

        $contributionsQuery = Contribution::with(['contributor', 'contributorFamilies'])
            ->whereHas('project', function ($q) use ($user) {
                $q->where('camp_id', $user->camp_id);
            });

        if ($status && in_array($status, ['pending', 'approved'])) {
            $contributionsQuery->where('status', $status);
        }

        $contributions = $contributionsQuery->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.contributions_fetched'),
            'data' => ContributionResource::collection(
                $contributions->each(fn($c) => $c->makeHidden('total_quantity'))
            ),
        ]);
    }


    public function confirmContribution(Request $request, $contributionId): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isDelegate()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
            ], 403);
        }

        $contribution = Contribution::with('contributorFamilies', 'project', 'contributor')
            ->find($contributionId);

        if (!$contribution || $contribution->project->camp_id != $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
            ], 404);
        }

        if ($contribution->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_already_processed'),
            ], 400);
        }

        $validated = $request->validate([
            'confirmed_quantity' => 'required|integer|min:1',
        ]);

        $contribution->confirmed_quantity = $validated['confirmed_quantity'];

        if ($contribution->confirmed_quantity === $contribution->total_quantity) {
            $contribution->status = 'approved';
            $contribution->save();

            $this->updateProjectAfterDelegateConfirmation($contribution);

            $this->notifyAdmin(
                __('messages.contribution_approved_title'),
                __('messages.contribution_approved_body', [
                    'contribution_id' => $contribution->id,
                    'contributor' => $contribution->contributor->name,
                ]),
                ['contribution_id' => $contribution->id]
            );

            $this->notifyUser(
                $contribution->user_id,
                __('messages.contribution_confirmed_title'),
                __('messages.contribution_confirmed_body', [
                    'contribution_id' => $contribution->id,
                    'delegate' => $user->name,
                ]),
                ['contribution_id' => $contribution->id]
            );

        } else {
            $contribution->status = 'pending';
            $contribution->save();

            $this->notifyAdmin(
                __('messages.contribution_mismatch_title'),
                __('messages.contribution_mismatch_body', [
                    'contribution_id' => $contribution->id,
                    'contributor' => $contribution->contributor->name,
                ]),
                ['contribution_id' => $contribution->id]
            );
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.contribution_confirmed_successfully'),
            'data' => new  ContributionResource($contribution),
        ]);
    }

    public function addFamiliesToContribution(Request $request, $contributionId): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isDelegate()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
            ], 403);
        }

        $contribution = Contribution::with('project', 'contributorFamilies')->find($contributionId);

        if (!$contribution || $contribution->project->camp_id != $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
            ], 404);
        }

        $validated = $request->validate([
            'families' => 'required|array|min:1',
            'families.*.id' => 'required|exists:families,id',
            'families.*.quantity' => 'required|integer|min:1',
        ]);

        $campFamilies = $contribution->project->camp->families()->pluck('id')->toArray();
        $attachData = [];

        foreach ($validated['families'] as $family) {
            if (!in_array($family['id'], $campFamilies)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.invalid_families_for_camp'),
                ], 422);
            }

            if ($contribution->contributorFamilies->contains($family['id'])) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.family_already_added'),
                    'familyId' => $family['id'],
                ], 422);
            }

            $attachData[$family['id']] = [
                'quantity' => $family['quantity']
            ];
        }

        if (!empty($attachData)) {
            $contribution->contributorFamilies()->attach($attachData);
        }

        $contribution->load('contributorFamilies');

        return response()->json([
            'success' => true,
            'message' => __('messages.families_added_to_contribution_successfully'),
            'data' => new ContributionResource($contribution),
        ]);
    }



    protected function updateProjectAfterDelegateConfirmation(Contribution $contribution)
    {
        $project = $contribution->project;

        $totalReceived = $project->contributions()->sum('confirmed_quantity');

        $project->total_received = $totalReceived;
        $project->total_remaining = max(0, $project->college - $totalReceived);

        if ($totalReceived >= $project->college) {
            $project->status = 'delivered';
        } elseif ($totalReceived > 0) {
            $project->status = 'in_progress';
        } else {
            $project->status = 'pending';
        }

        $project->save();
    }

    public function updateFamilyQuantity(Request $request, $contributionId, $familyId): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isDelegate()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
            ], 403);
        }

        $contribution = Contribution::with('contributorFamilies')->find($contributionId);

        if (!$contribution || $contribution->project->camp_id != $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
            ], 404);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if (!$contribution->contributorFamilies->contains($familyId)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_in_contribution'),
            ], 422);
        }

        $contribution->contributorFamilies()->updateExistingPivot($familyId, [
            'quantity' => $validated['quantity']
        ]);

        $contribution->load('contributorFamilies');

        return response()->json([
            'success' => true,
            'message' => __('messages.family_quantity_updated_successfully'),
            'data' => new ContributionResource($contribution),
        ]);
    }

    public function removeFamilyFromContribution(Request $request, $contributionId, $familyId): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isDelegate()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
            ], 403);
        }

        $contribution = Contribution::with('contributorFamilies')->find($contributionId);

        if (!$contribution || $contribution->project->camp_id != $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
            ], 404);
        }

        if (!$contribution->contributorFamilies->contains($familyId)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_in_contribution'),
            ], 422);
        }

        $contribution->contributorFamilies()->detach($familyId);

        $contribution->load('contributorFamilies');

        return response()->json([
            'success' => true,
            'message' => __('messages.family_removed_from_contribution_successfully'),
            'data' => new ContributionResource($contribution),
        ]);
    }



}

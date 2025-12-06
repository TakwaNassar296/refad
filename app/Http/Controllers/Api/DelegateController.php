<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DelegateController extends Controller
{
    public function contributionsPending(): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isDelegate()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
            ], 403);
        }

        $contributions = Contribution::with(['user', 'contributorFamilies'])
            ->whereHas('project', function ($q) use ($user) {
                $q->where('camp_id', $user->camp_id);
            })
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.pending_contributions_fetched'),
            'data' => $contributions,
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

        $contribution = Contribution::with('contributorFamilies', 'project', 'user')
            ->find($contributionId);

        if (!$contribution || $contribution->project->camp_id != $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
            ], 404);
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
                    'contributor' => $contribution->user->name,
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
                    'contributor' => $contribution->user->name,
                ]),
                ['contribution_id' => $contribution->id]
            );
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.contribution_confirmed_successfully'),
            'data' => $contribution,
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

        $contribution = Contribution::with('project', 'contributorFamilies')
            ->find($contributionId);

        if (!$contribution || $contribution->project->camp_id != $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
            ], 404);
        }

        $validated = $request->validate([
            'families' => 'required|array',
            'families.*' => 'exists:families,id',
        ]);

        $validFamilies = $contribution->project->camp->families()
            ->whereIn('id', $validated['families'])
            ->pluck('id')
            ->toArray();

        if (empty($validFamilies)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.invalid_families_for_camp'),
            ], 422);
        }

        $contribution->contributorFamilies()->sync($validFamilies);

        return response()->json([
            'success' => true,
            'message' => __('messages.families_added_to_contribution_successfully'),
            'data' => $contribution->load('contributorFamilies'),
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


}

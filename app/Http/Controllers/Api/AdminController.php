<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Project;
use App\Mail\UserStatusMail;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ContributionResource;
use App\Http\Requests\AdminCreateUserRequest;

class AdminController extends Controller
{
    public function pendingUsers(Request $request): JsonResponse
    {
        $query = User::whereIn('role', ['delegate', 'contributor'])
            ->where('status', 'pending');

        if ($request->has('role') && in_array($request->role, ['delegate', 'contributor'])) {
            $query->where('role', $request->role);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => __('auth.no_pending_users'),
                'data' => []
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => __('auth.pending_users_list'),
            'data' => UserResource::collection($users)
        ]);
    }

    public function approveUser(Request $request, User $user): JsonResponse
    {
        if (!$user->isPending()) {
            return response()->json([
                'status' => false,
                'message' => __('auth.user_cannot_be_approved'),
                'data' => null
            ], 422);
        }

        if ($user->isDelegate()) {
            if (!$user->camp_id) {
                $request->validate([
                    'camp_id' => 'required|exists:camps,id'
                ]);

                $existingDelegate = User::where('camp_id', $request->camp_id)
                    ->where('role', 'delegate')
                    ->first();

                if ($existingDelegate) {
                    return response()->json([
                        'status' => false,
                        'message' => __('messages.camp_already_has_delegate'),
                        'data' => null
                    ], 422);
                }

                $user->update([
                    'status' => 'approved',
                    'camp_id' => $request->camp_id
                ]);

              /*  $this->notifyUser(
                    $user->id,
                    __('messages.user_approved_delegate'),
                    __('messages.user_approved_delegate')
                );*/

                Mail::to($user->email)->send(new UserStatusMail($user, 'accepted'));

                return response()->json([
                    'status' => true,
                    'message' => __('auth.delegate_approved_successfully'),
                    'data' => new UserResource($user)
                ]);
            }

            $existingDelegate = User::where('camp_id', $user->camp_id)
                ->where('role', 'delegate')
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingDelegate) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.camp_already_has_delegate'),
                    'data' => null
                ], 422);
            }

            $user->update([
                'status' => 'approved'
            ]);

            Mail::to($user->email)->send(new UserStatusMail($user, 'accepted'));

            return response()->json([
                'status' => true,
                'message' => __('auth.delegate_approved_successfully'),
                'data' => new UserResource($user)
            ]);
        }

        if ($user->isContributor()) {
            $user->update([
                'status' => 'approved'
            ]);

            Mail::to($user->email)->send(new UserStatusMail($user, 'accepted'));

            return response()->json([
                'status' => true,
                'message' => __('auth.contributor_approved_successfully'),
                'data' => new UserResource($user)
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => __('auth.user_role_not_supported'),
            'data' => null
        ], 422);
    }


    public function rejectUser(User $user): JsonResponse
    {
        if (!$user->isPending()) {
            return response()->json([
                'status' => false,
                'message' => __('auth.user_cannot_be_rejected'),
                'data' => null
            ], 422);
        }

        $user->update([
            'status' => 'rejected'
        ]);

        $messageKey = $user->isDelegate() 
            ? 'delegate_rejected_successfully' 
            : ($user->isContributor() ? 'contributor_rejected_successfully' : 'user_role_not_supported');


        if ($messageKey !== 'user_role_not_supported') {
            Mail::to($user->email)->send(new UserStatusMail($user, 'rejected'));
        }
        
        return response()->json([
            'status' => true,
            'message' => __('auth.' . $messageKey),
            'data' => new UserResource($user)
        ]);
    }

    public function approveProject(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
            ], 403);
        }

        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_found'),
            ], 404);
        }

        if ($project->is_approved) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_already_approved'),
            ], 400);
        }

        $project->is_approved = true;
        $project->save();

        if ($project->addedBy && $project->addedBy->isDelegate()) {
            $this->notifyUser(
                $project->addedBy->id,
                __('messages.project_approved_title'),
                __('messages.project_approved_body', [
                    'project' => $project->name
                ])
            );
        }


        return response()->json([
            'success' => true,
            'message' => __('messages.project_approved_successfully'),
            'data' => new ProjectResource($project->load(['camp', 'addedBy'])),
        ]);
    }


    public function allContributions(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $status = $request->query('status');
        $campId = $request->query('camp_id');
        $projectId = $request->query('project_id');

        $query = Contribution::with(['project', 'contributorFamilies', 'contributor']);

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($campId) {
            $query->whereHas('project', function ($q) use ($campId) {
                $q->where('camp_id', $campId);
            });
        }

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $contributions = $query->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.all_contributions_fetched'),
            'data' => ContributionResource::collection($contributions),
        ], 200);
    }



   /* public function updateContributionStatus(Request $request, $contributionId): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied'),
                'data' => null,
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $contribution = Contribution::find($contributionId);

        if (!$contribution) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contribution_not_found'),
                'data' => null,
            ], 404);
        }

        $contribution->update([
            'status' => $validated['status'],
        ]);

        $statusText = $validated['status'] === 'approved' 
            ? __('messages.contribution_approved') 
            : __('messages.contribution_rejected');

        $delegateIds = $contribution->project->camp->delegates()
            ->where('role', 'delegate')
            ->pluck('id')
            ->toArray();

        $this->notifyUsers(
            $delegateIds,
            __('messages.contribution_status_title'),
            __('messages.contribution_status_body', [
                'contributor' => $contribution->contributor->name,
                'quantity' => $contribution->total_quantity,
                'project' => $contribution->project->name,
                'status' => $statusText
            ])
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.contribution_status_updated'),
            'data' => new ContributionResource($contribution->load(['project', 'contributorFamilies' ])),
        ], 200);
    }*/

    public function createUser(AdminCreateUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $existingUser = User::withTrashed()
            ->where('email', $data['email'])
            ->orWhere('id_number', $data['id_number'])
            ->orWhere('phone', $data['phone'])
            ->first();

        if ($existingUser) {
            if ($existingUser->trashed()) {
                $existingUser->forceDelete();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.email_already_taken'),
                    'data' => null,
                ], 422);
            }
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'backup_phone' => $data['backup_phone'] ?? null,
            'id_number' => $data['id_number'],
            'role' => $data['role'],
            'camp_id' => $data['role'] === 'delegate' ? $data['camp_id'] : null,
            'password' => Hash::make($data['password']),
            'admin_position_id' => $data['role'] === 'delegate' ? $data['admin_position_id'] : null,
            'admin_position' => $data['role'] === 'contributor' ? $data['admin_position'] : null,
            'license_number' =>   $data['license_number'] ?? null,
            'is_approved' => true,
            'accept_terms' => true,
            'status' => 'approved',
        ]);

        return response()->json([
            'success' => true,
            'message' => __('auth.user_created_successfully'),
            'data' => new UserResource($user),
        ], 201);
    }

    public function getUsers(Request $request): JsonResponse
    {
        $query = User::whereIn('role', ['delegate', 'contributor']);


        if ($request->has('role') && in_array($request->role, ['delegate', 'contributor'])) {
            $query->where('role', $request->role);
        }

        if ($request->has('status') && in_array($request->status, ['approved', 'pending', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $users = $query->get();

        return response()->json([
            'success' => true,
            'message' => __('auth.users_list_fetched'),
            'data' => UserResource::collection($users),
        ]);
    }


    public function deleteUser(User $user): JsonResponse
    {
        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => __('auth.cannot_delete_admin'),
                'data' => null,
            ], 403);
        }

        $user->tokens()->delete(); 
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => __('auth.user_deleted_successfully'),
            'data' => null,
        ]);
    }




}

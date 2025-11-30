<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ContributionResource;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Project::with(['camp'])
            ->where('is_approved', true);

        if ($user->role === 'delegate') {
            $query->where('camp_id', $user->camp_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }


        if ($request->filled('family_name')) {
           $query->whereHas('contributions', function($q) use ($request) {
                $q->whereHas('delegateFamilies', function($q) use ($request) {
                    $q->where('family_name', 'like', '%' . $request->family_name . '%');
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('beneficiary_count')) {
            $query->where('beneficiary_count', $request->beneficiary_count);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $projects = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'message' => __('messages.projects_retrieved_successfully'),
            'data' => ProjectResource::collection($projects),
            'meta' => [
                'currentPage' => $projects->currentPage(),
                'lastPage' => $projects->lastPage(),
                'perPage' => $projects->perPage(),
                'total' => $projects->total(),
            ]
        ]);

    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
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

        $project = Project::create([
            'camp_id' =>  $campId,
            'added_by' => $user->id,
            'name' => $request->name,
            'type' => $request->type,
            'beneficiary_count' => $request->beneficiary_count ?? 0,
            'college' => $request->college,
            'project_number' => $request->project_number,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);
        
        if ($request->hasFile('file')) {
            $this->handleFileUpload($project, $request->file('file'));
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.project_added_successfully'),
            'data' => new ProjectResource($project->load(['camp'])),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $project = Project::with(['camp', 'contributions'])->find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_found')
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate'&& $project->camp_id !== $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied')
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project),
        ]);
    }

    public function update(UpdateProjectRequest $request, $id): JsonResponse
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_found')
            ], 404);
        }

        $user = Auth::user();

        if ($user->role === 'delegate' &&  $project->camp_id !== $user->camp_id) {
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

        $project->update($data);

        if ($request->hasFile('file')) {
            $this->handleFileUpload($project, $request->file('file'));
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.project_updated_successfully'),
            'data' => new ProjectResource($project->load(['camp', 'addedBy'])),
        ]);
    }


    public function destroy($id): JsonResponse
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_found')
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $project->camp_id !== $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied')
            ], 403);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.project_deleted_successfully')
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Project::with([
            'camp',
            'contributions.delegateFamilies', 
        ]);

        if ($user->role === 'delegate') {
            $query->where('camp_id', $user->camp_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('family_name')) {
           $query->whereHas('contributions', function($q) use ($request) {
                $q->whereHas('delegateFamilies', function($q) use ($request) {
                    $q->where('family_name', 'like', '%' . $request->family_name . '%');
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('beneficiary_count')) {
            $query->where('beneficiary_count', $request->beneficiary_count);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $projects = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => ProjectResource::collection($projects),
            'exported_at' => now()->toDateTimeString(),
            'total_projects' => $projects->count(),
            'total_beneficiaries' => $projects->sum('beneficiary_count'),
        ]);
    }

    private function handleFileUpload(Project $project, $file): void
    {
        $path = $file->store('projects', 'public');
        
        $project->update([
            'file_path' => $path,
            'file_original_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

}
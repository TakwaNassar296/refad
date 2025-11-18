<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Project::with(['camp', 'delegate', 'beneficiaryFamilies']);

        if ($user->role === 'delegate') {
            $query->where('added_by', $user->id);
        }

        // البحث باسم المشروع
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // البحث بالحالة
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // البحث بعدد العائلات المستفيدة
        if ($request->has('beneficiary_count')) {
            $query->where('beneficiary_count', $request->beneficiary_count);
        }

        // البحث بنوع المشروع
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $projects = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => ProjectResource::collection($projects),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ]
        ]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $user = Auth::user();

        $project = Project::create([
            'camp_id' => $user->camp_id,
            'added_by' => $user->id,
            'name' => $request->name,
            'type' => $request->type,
            'beneficiary_count' => $request->beneficiary_count ?? 0,
            'college' => $request->college,
            'project_number' => $request->project_number,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
        ]);

        // ربط العائلات المستفيدة إذا وجدت
        if ($request->has('families')) {
            $project->beneficiaryFamilies()->attach($request->families);
            $project->updateBeneficiaryCount();
        }

        // رفع الملف إذا وجد
        if ($request->hasFile('file')) {
            $this->handleFileUpload($project, $request->file('file'));
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.project_added_successfully'),
            'data' => new ProjectResource($project->load(['camp', 'delegate', 'beneficiaryFamilies'])),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $project = Project::with(['camp', 'delegate', 'beneficiaryFamilies'])->find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => __('messages.project_not_found')
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $project->added_by !== $user->id) {
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
        if ($user->role === 'delegate' && $project->added_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied')
            ], 403);
        }

        $project->update($request->validated());

        // تحديث العائلات المستفيدة إذا وجدت
        if ($request->has('families')) {
            $project->beneficiaryFamilies()->sync($request->families);
            $project->updateBeneficiaryCount();
        }

        // تحديث الملف إذا وجد
        if ($request->hasFile('file')) {
            $this->handleFileUpload($project, $request->file('file'));
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.project_updated_successfully'),
            'data' => new ProjectResource($project->load(['camp', 'delegate', 'beneficiaryFamilies'])),
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
        if ($user->role === 'delegate' && $project->added_by !== $user->id) {
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
        $query = Project::with(['camp', 'delegate', 'beneficiaryFamilies']);

        if ($user->role === 'delegate') {
            $query->where('added_by', $user->id);
        }

        // تطبيق نفس عوامل التصفية مثل index
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
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
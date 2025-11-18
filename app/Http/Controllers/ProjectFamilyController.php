<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FamilyResource;

class ProjectFamilyController extends Controller
{
    public function index($projectId): JsonResponse
    {
        $project = Project::find($projectId);

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

        $families = $project->beneficiaryFamilies()->with(['camp', 'members'])->get();

        return response()->json([
            'success' => true,
            'data' => FamilyResource::collection($families)
        ]);
    }

    public function store(Request $request, $projectId): JsonResponse
    {
        $project = Project::find($projectId);

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

        $request->validate([
            'family_id' => 'required|exists:families,id',
            'support_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // التحقق من أن العائلة تنتمي لنفس مخيم النائب
        $family = Family::find($request->family_id);
        if ($family->camp_id !== $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_in_your_camp')
            ], 403);
        }

        // إضافة العائلة للمشروع
        $project->beneficiaryFamilies()->attach($request->family_id, [
            'support_date' => $request->support_date,
            'notes' => $request->notes,
        ]);

        // تحديث عدد المستفيدين
        $project->updateBeneficiaryCount();

        return response()->json([
            'success' => true,
            'message' => __('messages.family_added_to_project_successfully')
        ], 201);
    }

    public function destroy($projectId, $familyId): JsonResponse
    {
        $project = Project::find($projectId);

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

        // إزالة العائلة من المشروع
        $project->beneficiaryFamilies()->detach($familyId);

        // تحديث عدد المستفيدين
        $project->updateBeneficiaryCount();

        return response()->json([
            'success' => true,
            'message' => __('messages.family_removed_from_project_successfully')
        ]);
    }

    public function syncFamilies(Request $request, $projectId): JsonResponse
    {
        $project = Project::find($projectId);

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

        $request->validate([
            'families' => 'required|array',
            'families.*.family_id' => 'required|exists:families,id',
            'families.*.support_date' => 'nullable|date',
            'families.*.notes' => 'nullable|string|max:1000',
        ]);

        // تحضير البيانات للـ sync
        $familiesData = [];
        foreach ($request->families as $familyData) {
            $family = Family::find($familyData['family_id']);
            
            // التحقق من أن العائلة تنتمي لنفس مخيم النائب
            if ($family->camp_id !== $user->camp_id) {
                continue; // تخطي العائلات التي لا تنتمي لمخيم النائب
            }

            $familiesData[$familyData['family_id']] = [
                'support_date' => $familyData['support_date'] ?? null,
                'notes' => $familyData['notes'] ?? null,
            ];
        }

        // مزامنة العائلات
        $project->beneficiaryFamilies()->sync($familiesData);

        // تحديث عدد المستفيدين
        $project->updateBeneficiaryCount();

        return response()->json([
            'success' => true,
            'message' => __('messages.project_families_synced_successfully'),
            'data' => [
                'attached_count' => count($familiesData),
                'total_beneficiaries' => $project->beneficiary_count,
            ]
        ]);
    }

    public function availableFamilies($projectId): JsonResponse
    {
        $project = Project::find($projectId);

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

        // الحصول على العائلات المتاحة (غير مضافين للمشروع)
        $availableFamilies = Family::where('camp_id', $user->camp_id)
            ->whereDoesntHave('beneficiaryProjects', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->with(['camp', 'members'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => FamilyResource::collection($availableFamilies)
        ]);
    }
}
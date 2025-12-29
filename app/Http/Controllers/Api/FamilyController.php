<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Family;
use Illuminate\Http\Request;
use App\Exports\FamiliesExport;
use App\Models\MedicalCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\FamilyResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreFamilyRequest;
use App\Http\Requests\UpdateFamilyRequest;
use Illuminate\Validation\ValidationException;

class FamilyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Family::with('camp');

        if ($user->role === 'delegate') {
            $query->where('camp_id', $user->camp_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where('family_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('total_members')) {
            $query->where('total_members', $request->total_members);
        }

        if ($request->has('marital_status') && $request->marital_status) {
            $query->whereHas('maritalStatus', function ($q) use ($request) {
                $q->where('name', $request->marital_status);
            });
        }

        if ($request->filled('medical_condition')) {
            $query->whereHas('members.medicalCondition', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->medical_condition. '%');
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



        $families = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.families_retrieved_successfully'),
            'data' => FamilyResource::collection($families), 
        ]);
    }

    public function store(StoreFamilyRequest $request): JsonResponse
    {
        try {
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

            $family = DB::transaction(function () use ($request, $campId, $user) {
                $filePath = $request->hasFile('file') 
                    ? $request->file('file')->store('families', 'public') 
                    : null;

                $family = Family::create([
                    'camp_id' => $campId,
                    'added_by' => $user->id,
                    'family_name' => $request->family_name,
                    'national_id' => $request->national_id,
                    'dob' => $request->dob,
                    'phone' => $request->phone,
                    'backup_phone' => $request->backup_phone,
                    'total_members' => $request->total_members,
                    'tent_number' => $request->tent_number,
                    'location' => $request->location,
                    'notes' => $request->notes,
                    'file' => $filePath,
                    'marital_status_id' => $request->marital_status_id,
                ]);

                if ($request->has('members') && is_array($request->members)) {
                    foreach ($request->members as $member) {
                        $memberFile = $member['file'] ?? null;
                        if ($memberFile) {
                            $memberFile = $memberFile->store('family_members', 'public');
                        }

                        $medicalConditionId = $member['medical_condition_id'] ?? null;

                        if (
                            !$medicalConditionId &&
                            !empty($member['medical_condition'])
                        ) {
                            $conditionName = trim($member['medical_condition']);

                            $medicalCondition = MedicalCondition::firstOrCreate([
                                'name' => $conditionName,
                            ]);

                            $medicalConditionId = $medicalCondition->id;
                        }
                        $family->members()->create([
                            'name' => $member['name'],
                            'gender' => $member['gender'],
                            'dob' => $member['dob'],
                            'national_id' => $member['national_id'],
                            'relationship_id' => $member['relationship_id'],
                            'medical_condition_id' => $medicalConditionId ?? null,
                            'file' => $memberFile,
                        ]);
                    }
                }

                return $family;
            });

            return response()->json([
                'success' => true,
                'message' => __('messages.family_added_successfully'),
                'data' => new FamilyResource($family->load(['camp', 'members'])),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        }
    }




    public function show($id): JsonResponse
    {
        $family = Family::with(['camp', 'members'])->find($id);

        if (!$family) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_found')
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate'&& $family->camp_id !== $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied')
            ], 403);
        }
        return response()->json([
            'success' => true,
            'message' => __('messages.family_retrieved_successfully'),
            'data' => new FamilyResource($family),
        ]);
    }

    public function update(UpdateFamilyRequest $request, $id): JsonResponse
    {
        $family = Family::find($id);

        if (!$family) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_found')
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' && $family->camp_id !== $user->camp_id) {
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

        if ($request->hasFile('file')) {
            if ($family->file && Storage::disk('public')->exists($family->file)) {
                Storage::disk('public')->delete($family->file);
            }
            $data['file'] = $request->file('file')->store('families', 'public');
        } else {
            $data['file'] = $family->file;
        }

        $family->update($data);

        if ($request->has('members') && is_array($request->members)) {
            foreach ($request->members as $memberInput) {
                if (isset($memberInput['id'])) {
                    $member = $family->members()->find($memberInput['id']);
                    if ($member) {
                        if (isset($memberInput['file'])) {
                            if ($member->file) {
                                Storage::disk('public')->delete($member->file);
                            }
                            $memberInput['file'] = $memberInput['file']->store('family_members/files', 'public');
                        }
                        $medicalConditionId = $memberInput['medical_condition_id'] ?? $member->medical_condition_id;

                        if (
                            empty($memberInput['medical_condition_id']) &&
                            !empty($memberInput['medical_condition'])
                        ) {
                            $conditionName = trim($memberInput['medical_condition']);

                            $medicalCondition = MedicalCondition::firstOrCreate([
                                'name' => $conditionName,
                            ]);

                            $medicalConditionId = $medicalCondition->id;
                        }
                        $member->update([
                            'name' => $memberInput['name'] ?? $member->name,
                            'gender' => $memberInput['gender'] ?? $member->gender,
                            'dob' => $memberInput['dob'] ?? $member->dob,
                            'national_id' => $memberInput['national_id'] ?? $member->national_id,
                            'relationship_id' => $memberInput['relationship_id'] ?? $member->relationship_id,
                            'medical_condition_id' => $medicalConditionId,
                            'file' => $memberInput['file'] ?? $member->file,
                        ]);

                    }
                } else {
                    $memberFile = isset($memberInput['file'])
                        ? $memberInput['file']->store('family_members/files', 'public')
                        : null;

                    $medicalConditionId = $memberInput['medical_condition_id'] ?? null;

                    if (
                        !$medicalConditionId &&
                        !empty($memberInput['medical_condition'])
                    ) {
                        $conditionName = trim($memberInput['medical_condition']);

                        $medicalCondition = MedicalCondition::firstOrCreate([
                            'name' => $conditionName,
                        ]);

                        $medicalConditionId = $medicalCondition->id;
                    }


                    $family->members()->create([
                        'name' => $memberInput['name'] ?? null,
                        'gender' => $memberInput['gender'] ?? null,
                        'dob' => $memberInput['dob'] ?? null,
                        'national_id' => $memberInput['national_id'] ?? null,
                        'relationship_id' => $memberInput['relationship_id'] ?? null,
                        'medical_condition_id' => $medicalConditionId,
                        'file' => $memberFile,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.family_updated_successfully'),
            'data' => new FamilyResource($family->load(['camp', 'members'])),
        ]);
    }


    public function destroy($id): JsonResponse
    {
        $family = Family::find($id);

        if (!$family) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_found')
            ], 404);
        }

        $user = Auth::user();
        if ($user->role === 'delegate' &&  $family->camp_id !== $user->camp_id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.access_denied')
            ], 403);
        }

        $family->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.family_deleted_successfully')
        ]);
    }

    public function exportFamilies(Request $request)
    {
        $user = Auth::user();

        $query = Family::with('camp', 'delegate');

        if ($user->role === 'delegate') {
            $query->where('camp_id', $user->camp_id);
        }

        if ($request->filled('search')) {
            $query->where('family_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('total_members')) {
            $query->where('total_members', $request->total_members);
        }

        if ($request->filled('marital_status')) {
            $query->whereHas('maritalStatus', function ($q) use ($request) {
                $q->where('name', $request->marital_status);
            });
        }

        $fileName = 'families_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        return Excel::download(new FamiliesExport($query), $fileName);
    }


    public function statistics($id)
    {
        $family = Family::with('members')->find($id);

        if (!$family) {
            return response()->json([
                'success' => false,
                'message' => __('messages.family_not_found'),
            ], 404);
        }

        $stats = $family->getStatistics();

        return response()->json([
            'success' => true,
            'message' => __('messages.family_statistics_retrieved'),
            'data' => [
                'familyId' => $family->id,
                'familyName' => $family->family_name,
                'totalMembers' => $stats['total'],
                'malesCount' => $stats['males'],
                'femalesCount' => $stats['females'],
            ]
        ]);
    }



}
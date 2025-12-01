<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Resources\ComplaintResource;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.complaints_retrieved'),
            'data' => ComplaintResource::collection($complaints),
        ]);
    }

    public function show($id)
    {
        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => __('messages.complaint_not_found'),
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.complaint_retrieved'),
            'data' => new ComplaintResource($complaint),
        ]);
    }

    public function store(StoreComplaintRequest $request)
    {
        $complaint = Complaint::create($request->validated());

        $this->notifyAdmin(
            __('messages.new_complaint_title'),
            __('messages.new_complaint_body', [
                'name' => $complaint->name,
                'subject' => $complaint->subject ?? 'بدون موضوع'
            ]),
            [
                'type' => 'new_complaint',
                'complaint_id' => $complaint->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.complaint_created'),
            'data' => new ComplaintResource($complaint),
        ], 201);
    }

    public function destroy($id)
    {
        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => __('messages.complaint_not_found'),
                'data' => null,
            ], 404);
        }

        $complaint->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.complaint_deleted'),
            'data' => null,
        ]);
    }
}

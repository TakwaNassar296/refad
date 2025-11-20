<?php

namespace App\Http\Controllers\Api;

use App\Models\Complaint;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactUsController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20|regex:/^\+?\d{7,20}$/',
            'topic' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $submission = ContactUs::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.contact_us_sent'),
            'data' => [
                'id' => $submission->id,
                'name' => $submission->name,
                'email' => $submission->email,
                'topic' => $submission->topic,
                'phone' => $submission->phone,
            ]
        ], 201);
    }

    public function complaint(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email:rfc,dns|max:255',
            'camp_id' => 'nullable|exists:camps,id',
            'topic' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $complaint = Complaint::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.complaint_submitted'),
            'data' => [
                'id' => $complaint->id,
                'name' => $complaint->name,
                'email' => $complaint->email,
                'phone' => $complaint->phone,
                'topic' => $complaint->topic,
                'camp' => $complaint->camp ? $complaint->camp->name : null
            ]
        ], 201);
    }
}

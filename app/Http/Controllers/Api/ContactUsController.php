<?php

namespace App\Http\Controllers\Api;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContactUsResource;
use App\Http\Requests\StoreContactUsRequest;

class ContactUsController extends Controller
{
    public function index()
    {
        $contacts = ContactUs::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.contacts_retrieved'),
            'data' => ContactUsResource::collection($contacts),
        ]);
    }

    public function show($id)
    {
        $contact = ContactUs::find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contact_not_found'),
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.contact_retrieved'),
            'data' => new ContactUsResource($contact),
        ]);
    }

    public function store(StoreContactUsRequest $request)
    {
        $contact = ContactUs::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('messages.contact_created'),
            'data' => new ContactUsResource($contact),
        ], 201);
    }

    public function destroy($id)
    {
        $contact = ContactUs::find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => __('messages.contact_not_found'),
                'data' => null,
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.contact_deleted'),
            'data' => null,
        ]);
    }
}

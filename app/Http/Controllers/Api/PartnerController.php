<?php

namespace App\Http\Controllers\Api;

use App\Models\Partner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Http\Requests\StorePartnerRequest;
use App\Http\Requests\UpdatePartnerRequest;

class PartnerController extends Controller
{

    public function index()
    {
        $partners = Partner::orderBy('order')->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.partners_retrieved'),
            'data' => PartnerResource::collection($partners),
        ]);
    }
    public function show($id)
    {
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => __('messages.partner_not_found'),
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.partner_retrieved'),
            'data' => new PartnerResource($partner),
        ]);
    }


    public function update(UpdatePartnerRequest $request, $id)
    {
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => __('messages.partner_not_found'),
                'data' => null,
            ], 404);
        }

        $data = $request->except('logo');

        if ($request->file('logo')) {
            $data['logo'] = $request->file('logo')->store('partners', 'public');
        }

        $partner->update($data);

        return response()->json([
            'success' => true,
            'message' => __('messages.partner_updated'),
            'data' => new PartnerResource($partner),
        ]);
    }


    public function destroy($id)
    {
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => __('messages.partner_not_found'),
                'data' => null,
            ], 404);
        }

        $partner->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.partner_deleted'),
            'data' => null,
        ]);
    }


    public function store(StorePartnerRequest $request)
    {
        $data = $request->except('logo');

        if ($request->file('logo')) {
            $data['logo'] = $request->file('logo')->store('partners', 'public');
        }

        $partner = Partner::create($data);

        return response()->json([
            'success' => true,
            'message' => __('messages.partner_created'),
            'data' => new PartnerResource($partner),
        ], 201);
    }




}

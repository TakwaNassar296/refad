<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Http\Resources\TestimonialResource;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('order')->get();

        return response()->json([
            'success' => true,
            'message' => __('messages.testimonials_retrieved'),
            'data' => TestimonialResource::collection($testimonials),
        ]);
    }

    public function show($id)
    {
        $testimonial = Testimonial::find($id);

        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => __('messages.testimonial_not_found'),
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.testimonial_retrieved'),
            'data' => new TestimonialResource($testimonial),
        ]);
    }

    public function store(StoreTestimonialRequest $request)
    {
        $testimonial = new Testimonial();
        $testimonial->user_name = $request->user_name;
        if ($request->file('user_image')) {
            $testimonial->user_image = $request->file('user_image')->store('testimonials', 'public');
        }
        $testimonial->order = $request->order;
        $testimonial->setTranslation('opinion', 'ar', $request->opinion['ar']);
        $testimonial->setTranslation('opinion', 'en', $request->opinion['en']);
        $testimonial->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.testimonial_created'),
            'data' => new TestimonialResource($testimonial),
        ], 201);
    }



    public function update(UpdateTestimonialRequest $request, $id)
    {
        $testimonial = Testimonial::find($id);

        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => __('messages.testimonial_not_found'),
                'data' => null,
            ], 404);
        }

        if ($request->has('user_name')) {
            $testimonial->user_name = $request->user_name;
        }

        if ($request->has('order')) {
            $testimonial->order = $request->order;
        }

        if ($request->file('user_image')) {
            $testimonial->user_image = $request->file('user_image')->store('testimonials', 'public');
        }

        if ($request->has('opinion')) {
            foreach ($request->opinion as $locale => $value) {
                $testimonial->setTranslation('opinion', $locale, $value);
            }
        }

        $testimonial->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.testimonial_updated'),
            'data' => new TestimonialResource($testimonial),
        ]);
    }


    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);

        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => __('messages.testimonial_not_found'),
                'data' => null,
            ], 404);
        }

        $testimonial->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.testimonial_deleted'),
            'data' => null,
        ]);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Models\Page;
use App\Models\Partner;
use App\Models\PageSection;
use App\Models\Testimonial;
use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Http\Requests\UpdatePageRequest;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::all();

        return response()->json([
            'success' => true,
            'message' => __('messages.pages_retrieved'),
            'data' => PageResource::collection($pages),
        ]);
    }

    public function show($type)
    {
        $page = Page::where('type', $type)->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => __('messages.page_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.page_retrieved'),
            'data' => new PageResource($page),
        ]);
    }


    public function update(UpdatePageRequest $request, $type)
    {
        $page = Page::where('type', $type)->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => __('messages.page_not_found'),
            ], 404);
        }

        if ($request->has('title') && is_array($request->title)) {
            foreach ($request->title as $locale => $value) {
                $page->setTranslation('title', $locale, $value);
            }
        }

        if ($request->has('description') && is_array($request->description)) {
            foreach ($request->description as $locale => $value) {
                $page->setTranslation('description', $locale, $value);
            }
        }

        if ($request->hasFile('image')) {
            $page->image = $request->file('image')->store('pages', 'public');
        }

        if ($request->hasFile('file')) {
            $page->file = $request->file('file')->store('pages/files', 'public');
        }

        $page->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.page_updated'),
            'data' => new PageResource($page),
        ]);
    }


   

    public function testimonial()
    {
        $testimonials = Testimonial::orderBy('order')
            ->get()
            ->map(function ($testimonial) {
                return [
                    'userName' => $testimonial->user_name,
                    'userImage' => $testimonial->user_image ? asset('storage/' . $testimonial->user_image) : null,
                    'opinion' => $testimonial->getTranslation('opinion', app()->getLocale()),
                    'createdAt' => $testimonial->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => __('messages.testimonials_retrieved'),
            'data' => $testimonials,
        ]);
    }


}

<?php

namespace App\Http\Controllers\API;

use App\Models\Page;
use App\Models\Partner;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::with(['sections' => fn($q) => $q->orderBy('order')])
            ->where('is_active', true)
            ->get()
            ->map(fn($page) => [
                'pageType' => $page->type,
                'sections' => $page->sections->map(fn($section) => [
                    'title' => $section->getTranslation('title', app()->getLocale()),
                    'description' => $section->getTranslation('description', app()->getLocale()),
                    'image' => $section->image ? asset('storage/' . $section->image) : null,
                ]),
            ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.pages_retrieved'),
            'data' => $pages,
        ]);
    }

    public function show($type)
    {
        $page = Page::with(['sections' => fn($q) => $q->orderBy('order')])
            ->where('type', $type)
            ->where('is_active', true)
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => __('messages.page_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.page_retrieved'),
            'data' => [
                'pageType' => $page->type,
                'sections' => $page->sections->map(fn($section) => [
                    'title' => $section->getTranslation('title', app()->getLocale()),
                    'description' => $section->getTranslation('description', app()->getLocale()),
                    'image' => $section->image ? asset('storage/' . $section->image) : null,
                ]),
            ],
        ]);
    }

    public function partner()
    {
        $partners = Partner::where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'logo' => $partner->logo ? asset('storage/' . $partner->logo) : null,
                    'website' => $partner->website,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => __('messages.partners_retrieved'),
            'data' => $partners,
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

<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Models\Homepage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\HomepageResource;
use App\Http\Requests\UpdateHomepageRequest;
use App\Http\Requests\CreateHomepageSlideRequest;

class HomepageController extends Controller
{
    public function show(Request $request)
    {
        $homepage = Homepage::with('slides')->first();
        
        if (!$homepage) {
            return response()->json([
                'success' => false,
                'message' => __('messages.homepage_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.homepage_retrieved'),
            'data' => new HomepageResource($homepage),
        ]);
    }

    public function update(UpdateHomepageRequest $request)
    {
        $homepage = Homepage::first();

        if (!$homepage) {
            return response()->json([
                'success' => false,
                'message' => __('messages.homepage_not_found'),
            ], 404);
        }

        if ($request->has('slides')) {
            foreach ($request->slides as $slideData) {
                if (isset($slideData['id'])) {
                    $slide = $homepage->slides()->find($slideData['id']);
                    if (!$slide) {
                        return response()->json([
                            'success' => false,
                            'message' => __('messages.slide_not_found', ['id' => $slideData['id']]),
                        ], 404);
                    }

                    $slide->update([
                        'hero_title' => $slideData['hero_title'] ?? $slide->hero_title,
                        'hero_description' => $slideData['hero_description'] ?? $slide->hero_description,
                        'hero_subtitle' => $slideData['hero_subtitle'] ?? $slide->hero_subtitle,
                        'hero_image' => isset($slideData['hero_image']) ? $slideData['hero_image']->store('homepage', 'public') : $slide->hero_image,
                        'small_hero_image' => isset($slideData['small_hero_image']) ? $slideData['small_hero_image']->store('homepage', 'public') : $slide->small_hero_image,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.homepage_updated'),
            'data' => new HomepageResource($homepage),
        ]);
    }


    public function createSlide(CreateHomepageSlideRequest $request)
    {
        $homepage = Homepage::first();

        if (!$homepage) {
            return response()->json([
                'success' => false,
                'message' => __('messages.homepage_not_found'),
            ], 404);
        }

        $slide = $homepage->slides()->create([
            'hero_title' => $request->hero_title,
            'hero_description' => $request->hero_description,
            'hero_subtitle' => $request->hero_subtitle ?? null,
            'hero_image' => $request->file('hero_image') ? $request->file('hero_image')->store('homepage', 'public') : null,
            'small_hero_image' => $request->file('small_hero_image') ? $request->file('small_hero_image')->store('homepage', 'public') : null,
        ]);

        $homepage->load('slides'); 

        return response()->json([
            'success' => true,
            'message' => __('messages.slide_created'),
            'data' => new HomepageResource($homepage),
        ]);
    }








   
}

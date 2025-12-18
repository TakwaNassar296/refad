<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Models\Homepage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\HomepageResource;
use App\Http\Requests\UpdateHomepageRequest;
use App\Http\Requests\CreateHomepageSlideRequest;
use App\Http\Requests\CreateHomepageSectionRequest;

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

        $homepage->update([
            'title' => $request->input('title', $homepage->title),
            'description' => $request->input('description', $homepage->description),
        ]);

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
        if ($request->has('sections')) {
            foreach ($request->sections as $sectionData) {
                if (isset($sectionData['id'])) {
                    $section = $homepage->sections()->find($sectionData['id']);

                    if (!$section) {
                        return response()->json([
                            'success' => false,
                            'message' => __('messages.section_not_found', ['id' => $sectionData['id']]),
                        ], 404);
                    }

                    $section->setTranslations('title', $sectionData['title'] ?? $section->title);
                    $section->setTranslations('description', $sectionData['description'] ?? $section->description);

                    if (isset($sectionData['image'])) {
                        $section->image = $sectionData['image']->store('homepage', 'public');
                    }

                    $section->save();
                }
            }
        }


        if ($request->file('complaint_image')) {
            $homepage->complaint_image = $request->file('complaint_image')->store('homepage', 'public');
        }

        if ($request->file('contact_image')) {
            $homepage->contact_image = $request->file('contact_image')->store('homepage', 'public');
        }

        $homepage->save();


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


    public function deleteSlide($id)
    {
        $homepage = Homepage::first();

        if (!$homepage) {
            return response()->json([
                'success' => false,
                'message' => __('messages.homepage_not_found'),
            ], 404);
        }

        $slide = $homepage->slides()->find($id);

        if (!$slide) {
            return response()->json([
                'success' => false,
                'message' => __('messages.slide_not_found', ['id' => $id]),
            ], 404);
        }

        if ($slide->hero_image) {
            Storage::disk('public')->delete($slide->hero_image);
        }
        if ($slide->small_hero_image) {
            Storage::disk('public')->delete($slide->small_hero_image);
        }

        $slide->delete();

        $homepage->load('slides');

        return response()->json([
            'success' => true,
            'message' => __('messages.slide_deleted'),
        ]);
    }


    public function createSection(CreateHomepageSectionRequest $request)
    {
        $homepage = Homepage::first();

        if ($homepage->sections()->count() >= 6) {
            return response()->json([
                'success' => false,
                'message' => __('messages.homepage_sections_limit_reached'),
            ], 422);
        }

        $section = $homepage->sections()->create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $request->file('image')
                ? $request->file('image')->store('homepage', 'public')
                : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.section_created'),
            'data' => new HomepageResource($homepage),
        ]);
    }










   
}

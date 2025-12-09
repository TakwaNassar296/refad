<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Models\Homepage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\HomepageResource;
use App\Http\Requests\UpdateHomepageRequest;

class HomepageController extends Controller
{
    public function show(Request $request)
    {
        $homepage = Homepage::first();
        
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

    public function update(UpdateHomepageRequest $request, Homepage $homepage)
    {
        if ($request->has('hero_title')) {
            foreach ($request->hero_title as $locale => $value) {
                $homepage->setTranslation('hero_title', $locale, $value);
            }
        }

        if ($request->has('hero_description')) {
            foreach ($request->hero_description as $locale => $value) {
                $homepage->setTranslation('hero_description', $locale, $value);
            }
        }

        if ($request->has('hero_subtitle')) {
            foreach ($request->hero_subtitle as $locale => $value) {
                $homepage->setTranslation('hero_subtitle', $locale, $value);
            }
        }

        if ($request->file('hero_image')) {
            $homepage->hero_image = $request->file('hero_image')->store('homepage', 'public');
        }

        if ($request->file('small_hero_image')) {
            $homepage->small_hero_image = $request->file('small_hero_image')->store('homepage', 'public');
        }

        $homepage->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.homepage_updated'),
            'data' => new HomepageResource($homepage),
        ]);
    }


   
}

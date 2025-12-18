<?php

namespace App\Http\Controllers\Api;

use App\Models\AboutUs;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAboutUsRequest;
use App\Http\Resources\AboutUsResource;

class AboutUsController extends Controller
{
    public function index()
    {
        $about = AboutUs::first();

        return response()->json([
            'success' => true,
            'message' => __('messages.about_retrieved'),
            'data' => new AboutUsResource($about),
        ]);
    }

    public function update(UpdateAboutUsRequest $request)
    {
        $about = AboutUs::first();

        if ($request->has('title')) {
            foreach ($request->title as $locale => $value) {
                $about->setTranslation('title', $locale, $value);
            }
        }

        if ($request->has('description')) {
            foreach ($request->description as $locale => $value) {
                $about->setTranslation('description', $locale, $value);
            }
        }

        if ($request->file('image')) {
            $about->image = $request->file('image')->store('about', 'public');
        }

        if ($request->file('file')) {
            $about->file = $request->file('file')->store('about/files', 'public');
        }


        $about->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.about_updated'),
            'data' => new AboutUsResource($about),
        ]);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Models\Setting;
use App\Models\Homepage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomepageController extends Controller
{
    public function index(Request $request)
    {
        $homepage = Homepage::where('is_active', true)->first();
        
        if (!$homepage) {
            return response()->json([
                'success' => false,
                'message' => __('messages.homepage_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.homepage_success'),
            'data' => [
                'heroSection' => [
                    'title' => $homepage->getTranslation('hero_title', app()->getLocale()),
                    'description' => $homepage->getTranslation('hero_description', app()->getLocale()),
                    'image' => $homepage->hero_image ? asset('storage/' . $homepage->hero_image) : null,
                ],
                'statistics' => [
                    'camps' => $homepage->camps_count,
                    'contributors' => $homepage->contributors_count,
                    'projects' => $homepage->projects_count,
                    'families' => $homepage->families_count,
                ],
            ],
        ]);
    }


    public function setting()
    {
        $settings = Setting::first();

        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => __('messages.settings_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.settings_retrieved'),
            'data' => [
                'siteName' => $settings->getTranslation('site_name', app()->getLocale()),
                'siteLogo' => $settings->site_logo ? asset('storage/' . $settings->site_logo) : null,
                'favicon' => $settings->favicon ? asset('storage/' . $settings->favicon) : null,
                'phone' => $settings->phone,
                'email' => $settings->email,
                'facebook' => $settings->facebook,
                'twitter' => $settings->twitter,
                'instagram' => $settings->instagram,
                'linkedin' => $settings->linkedin,
                'youtube' => $settings->youtube,
                'whatsapp' => $settings->whatsapp,
            ],
        ]);
    }
}

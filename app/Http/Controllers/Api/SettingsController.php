<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;

class SettingsController extends Controller
{
    public function show()
    {
        $settings = Setting::first();

        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => __('messages.settings_not_found'),
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.settings_retrieved'),
            'data' => new SettingResource($settings),
        ]);
    }

    public function store(StoreSettingRequest $request)
    {
        $settings = Setting::create($request->except('site_name', 'site_logo', 'favicon'));

        foreach ($request->site_name as $locale => $value) {
            $settings->setTranslation('site_name', $locale, $value);
        }

        if ($request->file('site_logo')) {
            $settings->site_logo = $request->file('site_logo')->store('settings', 'public');
        }

        if ($request->file('favicon')) {
            $settings->favicon = $request->file('favicon')->store('settings', 'public');
        }

        $settings->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.settings_created'),
            'data' => new SettingResource($settings),
        ]);
    }

    public function update(UpdateSettingRequest $request, Setting $setting)
    {
        if ($request->has('site_name')) {
            foreach ($request->site_name as $locale => $value) {
                $setting->setTranslation('site_name', $locale, $value);
            }
        }

        if ($request->file('site_logo')) {
            $setting->site_logo = $request->file('site_logo')->store('settings', 'public');
        }

        if ($request->file('favicon')) {
            $setting->favicon = $request->file('favicon')->store('settings', 'public');
        }

        $setting->fill($request->except('site_name', 'site_logo', 'favicon'))->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.settings_updated'),
            'data' => new SettingResource($setting),
        ]);
    }
}

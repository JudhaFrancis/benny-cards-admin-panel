<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    /**
     * Display the company settings.
     */
    public function show(): JsonResponse
    {
        $setting = Setting::first();

        return response()->json([
            'success' => true,
            'data' => $setting,
        ]);
    }

    /**
     * Update the company settings.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_des' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'logo' => 'nullable|string',
            'photo' => 'nullable|string',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
        ]);

        $setting = Setting::first();
        $data = $request->all();

        // Handle logo and photo file storage
        foreach (['logo', 'photo'] as $field) {
            if (!empty($data[$field]) && str_starts_with($data[$field], 'data:image')) {
                // Delete old file if it exists
                if ($setting && $setting->$field && File::exists(public_path($setting->$field))) {
                    File::delete(public_path($setting->$field));
                }

                $image_service_str = $data[$field];
                $extension = explode('/', explode(':', substr($image_service_str, 0, strpos($image_service_str, ';')))[1])[1];
                $replace = substr($image_service_str, 0, strpos($image_service_str, ',') + 1);
                $image = str_replace($replace, '', $image_service_str);
                $image = str_replace(' ', '+', $image);
                $imageName = $field . '_' . Str::random(10) . '.' . $extension;

                $path = 'uploads/settings/' . $imageName;
                File::put(public_path($path), base64_decode($image));

                $data[$field] = $path;
            }
        }

        if (!$setting) {
            $setting = Setting::create($data);
        } else {
            $setting->update($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Company settings updated successfully.',
            'data' => $setting,
        ]);
    }

    /**
     * Get the company logo for public use (ex: Login page).
     */
    public function getPublicLogo(): JsonResponse
    {
        $setting = Setting::first(['logo', 'company_name']);

        return response()->json([
            'success' => true,
            'data' => [
                'logo' => $setting?->logo,
                'company_name' => $setting?->company_name
            ]
        ]);
    }
}

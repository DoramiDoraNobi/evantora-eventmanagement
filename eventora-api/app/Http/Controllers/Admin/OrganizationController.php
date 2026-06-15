<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function edit()
    {
        $organization = app('current_organization');
        return view('admin.organization.edit', compact('organization'));
    }

    public function update(Request $request)
    {
        $organization = app('current_organization');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations,slug,' . $organization->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'primary_color' => 'required|string|max:7',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string',
            'smtp_from_email' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
        ]);

        $settings = $organization->settings ?? [];
        $settings['notify_daily_sales'] = $request->has('notify_daily_sales');
        $settings['notify_new_order'] = $request->has('notify_new_order');
        $validated['settings'] = $settings;

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($organization->logo);
            }
            $file = $request->file('logo');
            $filename = uniqid('logo_') . '.webp';
            $path = 'organizations/' . $filename;

            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->decode($file->getPathname());
            $image->scaleDown(width: 500);
            $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(80));
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $encoded->toString());

            $validated['logo'] = $path;
        }

        $organization->update($validated);

        return redirect()->back()->with('status', 'organization-updated');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SiteSettingController extends Controller
{
    use AuthorizesRequests; // ✅ FIX (VERY IMPORTANT)

    public function index()
    {
        $this->authorize('settings.index');
        SiteSetting::ensureDefaults();

        $groups = ['general', 'landing_page', 'customer_dashboard', 'contact', 'social', 'seo', 'system'];
        $settings = [];

        foreach ($groups as $group) {
            $settings[$group] = SiteSetting::getGroup($group);
        }

        return view('admin.settings.index', compact('settings', 'groups'));
    }

    public function update(Request $request)
    {
        $this->authorize('settings.index');

        foreach ($request->settings ?? [] as $key => $value) {
            if (!$request->hasFile("files.$key")) {
                SiteSetting::set($key, $value);
            }
        }

        $request->validate([
            'files.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,svg|max:4096',
        ]);

        foreach ($request->file('files', []) as $key => $file) {
            $oldValue = SiteSetting::get($key);

            if ($oldValue) {
                Storage::disk('public')->delete('settings/' . $oldValue);
            }

            $filename = $key . '_' . time() . '_' . uniqid() . '.' . $file->extension();
            $file->storeAs('settings', $filename, 'public');

            SiteSetting::set($key, $filename);
        }

        // Handle boolean checkboxes
        $booleanKeys = ['maintenance_mode', 'registration_enabled'];

        foreach ($booleanKeys as $bKey) {
            if (!$request->has("settings.$bKey")) {
                SiteSetting::set($bKey, '0');
            }
        }

        // Clear cache
        SiteSetting::clearCache();

        // Log activity
        ActivityLog::log('updated', 'Updated site settings');

        return back()->with('success', 'Site settings updated successfully!');
    }
}

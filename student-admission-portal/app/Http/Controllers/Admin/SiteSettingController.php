<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $settings = SiteSetting::all()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:5000',
        ]);

        foreach ($request->settings as $key => $value) {
            SiteSetting::where('key', $key)->update(['value' => $value]);
        }

        SiteSetting::clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}

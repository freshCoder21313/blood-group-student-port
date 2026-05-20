<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use App\Models\SiteSetting;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = SitePage::where('slug', $slug)->published()->firstOrFail();

        // Contact page gets special treatment with contact info
        if ($slug === 'contact') {
            return view('pages.contact', [
                'page' => $page,
                'contactInfo' => [
                    'email' => SiteSetting::get('school_email', 'info@school.edu'),
                    'phone' => SiteSetting::get('school_phone', '+254 700 000 000'),
                    'address' => SiteSetting::get('school_address', 'Nairobi, Kenya'),
                    'website' => SiteSetting::get('school_website', ''),
                ],
            ]);
        }

        return view('pages.show', compact('page'));
    }
}

<?php

use App\Models\SitePage;
use App\Models\SiteSetting;
use App\Models\User;

beforeEach(function () {
    // Seed default settings and pages
    $this->seed(\Database\Seeders\SiteSettingsSeeder::class);
});

// ============================================================
// PUBLIC PAGES
// ============================================================

test('public visitor can view published terms page', function () {
    $response = $this->get('/page/terms');

    $response->assertStatus(200);
    $response->assertSee('Terms of Service');
    $response->assertSee('Acceptance of Terms');
});

test('public visitor can view published privacy page', function () {
    $response = $this->get('/page/privacy');

    $response->assertStatus(200);
    $response->assertSee('Privacy Policy');
    $response->assertSee('Information We Collect');
});

test('public visitor can view contact page with contact info', function () {
    $response = $this->get('/page/contact');

    $response->assertStatus(200);
    $response->assertSee('Get in Touch');
    $response->assertSee('info@sks.edu');
    $response->assertSee('+254 700 000 000');
    $response->assertSee('P.O. Box 12345, Nairobi, Kenya');
});

test('public visitor gets 404 for non-existent page', function () {
    $response = $this->get('/page/non-existent-page');

    $response->assertStatus(404);
});

test('public visitor cannot view unpublished page', function () {
    SitePage::create([
        'title' => 'Draft Page',
        'slug' => 'draft-page',
        'content' => '<p>Secret content</p>',
        'is_published' => false,
    ]);

    $response = $this->get('/page/draft-page');

    $response->assertStatus(404);
});

test('footer contains links to terms privacy and contact pages', function () {
    $response = $this->get('/');

    $response->assertSee(route('page.show', 'terms'));
    $response->assertSee(route('page.show', 'privacy'));
    $response->assertSee(route('page.show', 'contact'));
});

test('footer displays copyright from site settings', function () {
    SiteSetting::set('footer_copyright', '© {year} TestSchool. All rights reserved.');

    $response = $this->get('/');

    $response->assertSee('© '.date('Y').' TestSchool. All rights reserved.');
});

// ============================================================
// ADMIN SITE SETTINGS
// ============================================================

test('guest cannot access admin settings', function () {
    $response = $this->get('/admin/settings');

    $response->assertRedirect('/login');
});

test('non-admin user cannot access settings', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/admin/settings');

    $response->assertStatus(403);
});

test('admin can view site settings page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertSee('Site Settings');
    $response->assertSee('General Settings');
    $response->assertSee('Contact Information');
    $response->assertSee('Footer Settings');
    $response->assertSee('SKS Academy');
    $response->assertSee('info@sks.edu');
});

test('admin can update site settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'settings' => [
            'school_name' => 'Updated Academy',
            'school_email' => 'updated@school.edu',
        ],
    ]);

    $response->assertRedirect(route('admin.settings.index'));
    $response->assertSessionHas('success');

    expect(SiteSetting::where('key', 'school_name')->first()->value)->toBe('Updated Academy');
    expect(SiteSetting::where('key', 'school_email')->first()->value)->toBe('updated@school.edu');
});

// ============================================================
// ADMIN PAGE MANAGEMENT
// ============================================================

test('guest cannot access admin pages', function () {
    $response = $this->get('/admin/pages');

    $response->assertRedirect('/login');
});

test('non-admin user cannot access page management', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/admin/pages');

    $response->assertStatus(403);
});

test('admin can view page management index', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/pages');

    $response->assertStatus(200);
    $response->assertSee('Page Management');
    $response->assertSee('Terms of Service');
    $response->assertSee('Privacy Policy');
    $response->assertSee('Contact Us');
});

test('admin can view create page form', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/pages/create');

    $response->assertStatus(200);
    $response->assertSee('Create Page');
});

test('admin can create a new page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/admin/pages', [
        'title' => 'About Us',
        'slug' => 'about-us',
        'content' => '<h2>About Our School</h2><p>We are a great school.</p>',
        'is_published' => true,
    ]);

    $response->assertRedirect(route('admin.pages.index'));
    $response->assertSessionHas('success');

    $page = SitePage::where('slug', 'about-us')->first();
    expect($page)->not->toBeNull();
    expect($page->title)->toBe('About Us');
    expect($page->is_published)->toBeTrue();
    expect($page->updated_by)->toBe($admin->id);
});

test('admin cannot create page with duplicate slug', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/admin/pages', [
        'title' => 'Another Terms',
        'slug' => 'terms',
        'content' => '<p>Duplicate</p>',
        'is_published' => true,
    ]);

    $response->assertSessionHasErrors('slug');
});

test('admin can edit existing page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $page = SitePage::where('slug', 'terms')->first();

    $response = $this->actingAs($admin)->get("/admin/pages/{$page->slug}/edit");

    $response->assertStatus(200);
    $response->assertSee('Edit Page');
    $response->assertSee('Terms of Service');
});

test('admin can update existing page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $page = SitePage::where('slug', 'terms')->first();

    $response = $this->actingAs($admin)->put("/admin/pages/{$page->slug}", [
        'title' => 'Updated Terms',
        'slug' => 'terms',
        'content' => '<h2>Updated Terms</h2><p>New content.</p>',
        'is_published' => true,
    ]);

    $response->assertRedirect(route('admin.pages.index'));
    $response->assertSessionHas('success');

    $page->refresh();
    expect($page->title)->toBe('Updated Terms');
    expect($page->updated_by)->toBe($admin->id);
});

test('admin cannot delete system pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $page = SitePage::where('slug', 'terms')->first();

    $response = $this->actingAs($admin)->delete("/admin/pages/{$page->slug}");

    $response->assertRedirect();
    $response->assertSessionHas('error', 'System pages cannot be deleted.');

    expect(SitePage::where('slug', 'terms')->exists())->toBeTrue();
});

test('admin can delete custom pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $page = SitePage::create([
        'title' => 'FAQ',
        'slug' => 'faq',
        'content' => '<p>FAQ content</p>',
        'is_published' => true,
        'is_system' => false,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/pages/{$page->slug}");

    $response->assertRedirect(route('admin.pages.index'));
    $response->assertSessionHas('success');

    expect(SitePage::where('slug', 'faq')->exists())->toBeFalse();
});

// ============================================================
// MODEL TESTS
// ============================================================

test('SiteSetting get returns cached value', function () {
    $value = SiteSetting::get('school_name');

    expect($value)->toBe('SKS Academy');
});

test('SiteSetting get returns default for missing key', function () {
    $value = SiteSetting::get('nonexistent_key', 'fallback');

    expect($value)->toBe('fallback');
});

test('SiteSetting set creates or updates value', function () {
    SiteSetting::set('new_key', 'new_value', 'custom');

    $setting = SiteSetting::where('key', 'new_key')->first();
    expect($setting->value)->toBe('new_value');
    expect($setting->group)->toBe('custom');
});

test('SitePage published scope filters correctly', function () {
    SitePage::create([
        'title' => 'Hidden',
        'slug' => 'hidden',
        'content' => 'hidden',
        'is_published' => false,
    ]);

    $published = SitePage::published()->get();
    $allPages = SitePage::all();

    expect($published->count())->toBeLessThan($allPages->count());
    expect($published->pluck('slug'))->not->toContain('hidden');
});

test('SitePage uses slug for route model binding', function () {
    $page = SitePage::where('slug', 'terms')->first();

    expect($page->getRouteKeyName())->toBe('slug');
});

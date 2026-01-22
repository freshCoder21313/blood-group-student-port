<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Application;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_applications_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Application::factory(5)->create();

        $response = $this->actingAs($admin)->get(route('admin.applications.index'));

        $response->assertStatus(200);
        $response->assertViewHas('applications');
    }

    public function test_admin_can_view_application_details()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $application = Application::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.applications.show', $application));

        $response->assertStatus(200);
        $response->assertViewHas('application');
    }

    public function test_admin_can_approve_application()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $application = Application::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($admin)->post(route('admin.applications.approve', $application));

        $response->assertRedirect(route('admin.applications.index'));
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_application()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $application = Application::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($admin)->post(route('admin.applications.reject', $application), [
            'rejection_reason' => 'Invalid documents',
        ]);

        $response->assertRedirect(route('admin.applications.index'));
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'rejected',
            'rejection_reason' => 'Invalid documents',
        ]);
    }

    public function test_admin_can_view_pending_payments()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Payment::factory()->create(['status' => Payment::STATUS_PENDING_VERIFICATION]);

        $response = $this->actingAs($admin)->get(route('admin.payments.index'));

        $response->assertStatus(200);
        $response->assertViewHas('payments');
    }

    public function test_admin_can_approve_payment()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = Payment::factory()->create(['status' => Payment::STATUS_PENDING_VERIFICATION]);

        $response = $this->actingAs($admin)->post(route('admin.payments.approve', $payment));

        $response->assertRedirect(route('admin.payments.index'));
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_COMPLETED,
        ]);
    }
}

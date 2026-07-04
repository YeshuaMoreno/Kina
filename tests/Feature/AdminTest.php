<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_adult_confirmed' => true]);
    }

    private function normal(): User
    {
        return User::factory()->create(['is_admin' => false, 'is_adult_confirmed' => true]);
    }

    private function report(User $reporter, User $reported): Report
    {
        return Report::create([
            'reporter_id' => $reporter->id,
            'reported_id' => $reported->id,
            'reason' => 'acoso',
            'status' => 'pending',
        ]);
    }

    public function test_usuario_no_admin_no_puede_entrar_al_panel(): void
    {
        $this->actingAs($this->normal())->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_puede_entrar_al_panel(): void
    {
        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_puede_ver_usuarios(): void
    {
        $this->normal(); // hay a quién listar

        $this->actingAs($this->admin())->get(route('admin.users.index'))->assertOk();
    }

    public function test_admin_puede_suspender_usuario(): void
    {
        $admin = $this->admin();
        $target = $this->normal();

        $this->actingAs($admin)->post(route('admin.users.suspend', $target))->assertRedirect();

        $target->refresh();
        $this->assertTrue($target->is_suspended);
        $this->assertNotNull($target->suspended_at);
    }

    public function test_admin_puede_reactivar_usuario(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['is_suspended' => true, 'suspended_at' => now()]);

        $this->actingAs($admin)->post(route('admin.users.reactivate', $target))->assertRedirect();

        $target->refresh();
        $this->assertFalse($target->is_suspended);
        $this->assertNull($target->suspended_at);
    }

    public function test_admin_no_puede_suspenderse_a_si_mismo(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.suspend', $admin))->assertRedirect();

        $this->assertFalse($admin->fresh()->is_suspended);
    }

    public function test_usuario_suspendido_no_puede_acceder_al_dashboard(): void
    {
        $suspended = User::factory()->create(['is_suspended' => true, 'suspended_at' => now()]);

        $this->actingAs($suspended)->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_admin_puede_ver_reportes(): void
    {
        $this->report($this->normal(), $this->normal());

        $this->actingAs($this->admin())->get(route('admin.reports.index'))->assertOk();
    }

    public function test_admin_puede_revisar_reporte_y_registra_revisor(): void
    {
        $admin = $this->admin();
        $report = $this->report($this->normal(), $this->normal());

        $this->actingAs($admin)->post(route('admin.reports.review', $report), ['status' => 'resolved'])
            ->assertRedirect(route('admin.reports.show', $report));

        $report->refresh();
        $this->assertSame('resolved', $report->status);
        $this->assertSame($admin->id, $report->reviewed_by);
        $this->assertNotNull($report->reviewed_at);
    }

    public function test_revisar_reporte_valida_status(): void
    {
        $admin = $this->admin();
        $report = $this->report($this->normal(), $this->normal());

        $this->actingAs($admin)->post(route('admin.reports.review', $report), ['status' => 'inventado'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $report->fresh()->status);
    }

    public function test_puede_suspender_reportado_desde_el_reporte(): void
    {
        $admin = $this->admin();
        $reported = $this->normal();
        $report = $this->report($this->normal(), $reported);

        $this->actingAs($admin)->post(route('admin.reports.review', $report), [
            'status' => 'resolved',
            'suspend_reported' => '1',
        ])->assertRedirect(route('admin.reports.show', $report));

        $this->assertTrue($reported->fresh()->is_suspended);
    }

    public function test_usuario_no_admin_no_puede_revisar_reportes(): void
    {
        $report = $this->report($this->normal(), $this->normal());

        $this->actingAs($this->normal())->post(route('admin.reports.review', $report), ['status' => 'resolved'])
            ->assertForbidden();

        $this->assertSame('pending', $report->fresh()->status);
    }
}

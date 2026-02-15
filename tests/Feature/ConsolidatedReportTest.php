<?php

namespace Tests\Feature;

use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentCategory;
use App\Models\StudentObligation;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsolidatedReportTest extends TestCase
{
    use RefreshDatabase;

    private Unit $mi;
    private Unit $ra;
    private User $superAdmin;
    private User $operatorTu;
    private Student $miStudent;
    private Student $raStudent;
    private Transaction $miTransaction;
    private Transaction $raTransaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UnitSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->mi = Unit::where('code', 'MI')->first();
        $this->ra = Unit::where('code', 'RA')->first();

        $this->superAdmin = User::factory()->create(['unit_id' => $this->mi->id]);
        $this->superAdmin->assignRole('super_admin');

        $this->operatorTu = User::factory()->create(['unit_id' => $this->mi->id]);
        $this->operatorTu->assignRole('operator_tu');

        // Create properly scoped students
        $miClass = SchoolClass::factory()->create(['unit_id' => $this->mi->id]);
        $miCat = StudentCategory::factory()->create(['unit_id' => $this->mi->id]);
        $this->miStudent = Student::factory()->create([
            'unit_id' => $this->mi->id,
            'class_id' => $miClass->id,
            'category_id' => $miCat->id,
        ]);

        $raClass = SchoolClass::factory()->create(['unit_id' => $this->ra->id]);
        $raCat = StudentCategory::factory()->create(['unit_id' => $this->ra->id]);
        $this->raStudent = Student::factory()->create([
            'unit_id' => $this->ra->id,
            'class_id' => $raClass->id,
            'category_id' => $raCat->id,
        ]);

        // Create transactions in both units with correct students
        $this->miTransaction = Transaction::factory()->create([
            'unit_id' => $this->mi->id,
            'student_id' => $this->miStudent->id,
            'status' => 'completed',
            'type' => 'income',
            'transaction_date' => today(),
            'total_amount' => 100000,
            'created_by' => $this->superAdmin->id,
        ]);

        $this->raTransaction = Transaction::factory()->create([
            'unit_id' => $this->ra->id,
            'student_id' => $this->raStudent->id,
            'status' => 'completed',
            'type' => 'income',
            'transaction_date' => today(),
            'total_amount' => 200000,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    private function actAsUnit(User $user, Unit $unit): self
    {
        return $this->actingAs($user)->withSession(['current_unit_id' => $unit->id]);
    }

    // ─── Daily Report ────────────────────────────────────────────

    public function test_super_admin_daily_report_consolidated_sees_all_units(): void
    {
        $this->actAsUnit($this->superAdmin, $this->mi)
            ->get(route('reports.daily', ['scope' => 'all', 'date' => today()->toDateString()]))
            ->assertOk()
            ->assertSee($this->miTransaction->transaction_number)
            ->assertSee($this->raTransaction->transaction_number)
            ->assertSee('All Units');
    }

    public function test_super_admin_daily_report_default_scope_sees_own_unit(): void
    {
        $this->actAsUnit($this->superAdmin, $this->mi)
            ->get(route('reports.daily', ['date' => today()->toDateString()]))
            ->assertOk()
            ->assertSee($this->miTransaction->transaction_number)
            ->assertDontSee($this->raTransaction->transaction_number);
    }

    public function test_non_super_admin_daily_report_scope_all_ignored(): void
    {
        $this->actAsUnit($this->operatorTu, $this->mi)
            ->get(route('reports.daily', ['scope' => 'all', 'date' => today()->toDateString()]))
            ->assertOk()
            ->assertSee($this->miTransaction->transaction_number)
            ->assertDontSee($this->raTransaction->transaction_number)
            ->assertDontSee('All Units');
    }

    // ─── Monthly Report ──────────────────────────────────────────

    public function test_super_admin_monthly_report_consolidated_sees_all_units(): void
    {
        $this->actAsUnit($this->superAdmin, $this->mi)
            ->get(route('reports.monthly', [
                'scope' => 'all',
                'month' => today()->month,
                'year' => today()->year,
            ]))
            ->assertOk()
            ->assertSee($this->miTransaction->transaction_number)
            ->assertSee($this->raTransaction->transaction_number)
            ->assertSee('All Units');
    }

    public function test_non_super_admin_monthly_report_scope_all_ignored(): void
    {
        $this->actAsUnit($this->operatorTu, $this->mi)
            ->get(route('reports.monthly', [
                'scope' => 'all',
                'month' => today()->month,
                'year' => today()->year,
            ]))
            ->assertOk()
            ->assertSee($this->miTransaction->transaction_number)
            ->assertDontSee($this->raTransaction->transaction_number);
    }

    // ─── Arrears Report ──────────────────────────────────────────

    public function test_super_admin_arrears_report_consolidated_sees_all_units(): void
    {
        $miFeeType = FeeType::factory()->create(['unit_id' => $this->mi->id]);
        $raFeeType = FeeType::factory()->create(['unit_id' => $this->ra->id]);

        $miObligation = StudentObligation::factory()->create([
            'unit_id' => $this->mi->id,
            'student_id' => $this->miStudent->id,
            'is_paid' => false,
            'month' => today()->month,
            'year' => today()->year,
            'fee_type_id' => $miFeeType->id,
        ]);

        $raObligation = StudentObligation::factory()->create([
            'unit_id' => $this->ra->id,
            'student_id' => $this->raStudent->id,
            'is_paid' => false,
            'month' => today()->month,
            'year' => today()->year,
            'fee_type_id' => $raFeeType->id,
        ]);

        $this->actAsUnit($this->superAdmin, $this->mi)
            ->get(route('reports.arrears', [
                'scope' => 'all',
                'month' => today()->month,
                'year' => today()->year,
            ]))
            ->assertOk()
            ->assertSee($this->miStudent->name)
            ->assertSee($this->raStudent->name)
            ->assertSee('All Units');
    }

    public function test_non_super_admin_arrears_report_scope_all_ignored(): void
    {
        $miFeeType = FeeType::factory()->create(['unit_id' => $this->mi->id]);
        $raFeeType = FeeType::factory()->create(['unit_id' => $this->ra->id]);

        StudentObligation::factory()->create([
            'unit_id' => $this->mi->id,
            'student_id' => $this->miStudent->id,
            'is_paid' => false,
            'month' => today()->month,
            'year' => today()->year,
            'fee_type_id' => $miFeeType->id,
        ]);

        StudentObligation::factory()->create([
            'unit_id' => $this->ra->id,
            'student_id' => $this->raStudent->id,
            'is_paid' => false,
            'month' => today()->month,
            'year' => today()->year,
            'fee_type_id' => $raFeeType->id,
        ]);

        $this->actAsUnit($this->operatorTu, $this->mi)
            ->get(route('reports.arrears', [
                'scope' => 'all',
                'month' => today()->month,
                'year' => today()->year,
            ]))
            ->assertOk()
            ->assertSee($this->miStudent->name)
            ->assertDontSee($this->raStudent->name);
    }

    // ─── Dashboard ───────────────────────────────────────────────

    public function test_super_admin_dashboard_consolidated_sees_all_units(): void
    {
        $this->actAsUnit($this->superAdmin, $this->mi)
            ->get(route('dashboard', ['scope' => 'all']))
            ->assertOk()
            ->assertSee('All Units')
            ->assertSee('Per-Unit Breakdown')
            ->assertSee('Current Unit');
    }

    public function test_non_super_admin_dashboard_scope_all_ignored(): void
    {
        $this->actAsUnit($this->operatorTu, $this->mi)
            ->get(route('dashboard', ['scope' => 'all']))
            ->assertOk()
            ->assertDontSee('All Units')
            ->assertDontSee('Per-Unit Breakdown');
    }

    public function test_super_admin_dashboard_default_scope_no_breakdown(): void
    {
        $this->actAsUnit($this->superAdmin, $this->mi)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Per-Unit Breakdown');
    }

    // ─── Toggle preserves filters ────────────────────────────────

    public function test_daily_toggle_preserves_date_filter(): void
    {
        $response = $this->actAsUnit($this->superAdmin, $this->mi)
            ->get(route('reports.daily', ['date' => '2026-01-15', 'scope' => 'all']))
            ->assertOk();

        $response->assertSee('scope=unit');
        $response->assertSee('date=2026-01-15');
    }

    public function test_monthly_toggle_preserves_month_year_filter(): void
    {
        $response = $this->actAsUnit($this->superAdmin, $this->mi)
            ->get(route('reports.monthly', ['month' => 3, 'year' => 2026, 'scope' => 'all']))
            ->assertOk();

        $response->assertSee('scope=unit');
        $response->assertSee('month=3');
        $response->assertSee('year=2026');
    }
}

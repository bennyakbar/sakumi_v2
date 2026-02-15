<?php

namespace Tests\Feature;

use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ReceiptService;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class TransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UnitSeeder::class);
    }

    public function test_user_can_create_income_transaction_with_multiple_items(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        session(['current_unit_id' => $user->unit_id]);

        $this->mock(ReceiptService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generate')->once()->andReturn('receipts/mock.pdf');
        });

        $class = SchoolClass::query()->create([
            'name' => '1A',
            'level' => 1,
            'academic_year' => '2025/2026',
            'is_active' => true,
        ]);

        $category = StudentCategory::query()->create([
            'code' => 'REG',
            'name' => 'Regular',
            'discount_percentage' => 0,
        ]);

        $student = Student::query()->create([
            'nis' => '10001',
            'nisn' => '20001',
            'name' => 'Budi',
            'class_id' => $class->id,
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $feeA = FeeType::query()->create([
            'code' => 'SPP',
            'name' => 'SPP Februari',
            'is_monthly' => true,
            'is_active' => true,
        ]);

        $feeB = FeeType::query()->create([
            'code' => 'BOOK',
            'name' => 'Buku Paket',
            'is_monthly' => false,
            'is_active' => true,
        ]);

        $response = $this->post(route('transactions.store'), [
            'student_id' => $student->id,
            'transaction_date' => '2026-02-14',
            'payment_method' => 'cash',
            'description' => 'Pembayaran gabungan',
            'items' => [
                ['fee_type_id' => $feeA->id, 'amount' => 100000, 'description' => 'SPP Februari'],
                ['fee_type_id' => $feeB->id, 'amount' => 50000, 'description' => 'Buku semester'],
            ],
        ]);

        $transaction = Transaction::query()->firstOrFail();

        $response->assertRedirect(route('transactions.show', $transaction));

        $this->assertSame('income', $transaction->type);
        $this->assertSame('completed', $transaction->status);
        $this->assertSame('NF-2026-000001', $transaction->transaction_number);
        $this->assertSame('150000.00', $transaction->total_amount);

        $this->assertDatabaseCount('transaction_items', 2);
        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'fee_type_id' => $feeA->id,
            'amount' => '100000.00',
        ]);
    }

    public function test_receipt_print_page_uses_a5_landscape_layout_with_signature_block(): void
    {
        $user = User::factory()->create(['name' => 'Admin TU']);
        $this->actingAs($user);
        session(['current_unit_id' => $user->unit_id]);

        $class = SchoolClass::query()->create([
            'name' => '2A',
            'level' => 2,
            'academic_year' => '2025/2026',
            'is_active' => true,
        ]);

        $category = StudentCategory::query()->create([
            'code' => 'VIP',
            'name' => 'VIP',
            'discount_percentage' => 0,
        ]);

        $student = Student::query()->create([
            'nis' => '10002',
            'nisn' => '20002',
            'name' => 'Siti',
            'class_id' => $class->id,
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $feeType = FeeType::query()->create([
            'code' => 'REG',
            'name' => 'Daftar Ulang',
            'is_monthly' => false,
            'is_active' => true,
        ]);

        $transaction = Transaction::query()->create([
            'transaction_number' => 'NF-2026-000010',
            'transaction_date' => '2026-02-14',
            'type' => 'income',
            'student_id' => $student->id,
            'payment_method' => 'transfer',
            'total_amount' => 250000,
            'description' => 'Pembayaran registrasi',
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        $transaction->items()->create([
            'fee_type_id' => $feeType->id,
            'amount' => 250000,
            'description' => 'Registrasi ulang',
        ]);

        $this->get(route('receipts.print', $transaction))
            ->assertOk()
            ->assertSee('A5 landscape', false)
            ->assertSee('RECEIPT PEMBAYARAN', false)
            ->assertSee('Digital Signature', false)
            ->assertSee('Admin TU', false);
    }
}

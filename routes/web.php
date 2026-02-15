<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\ClassController;
use App\Http\Controllers\Master\FeeMatrixController;
use App\Http\Controllers\Master\FeeTypeController;
use App\Http\Controllers\Master\StudentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public liveness probe
Route::get('/health/live', [HealthCheckController::class, 'live']);

// Authenticated diagnostics
Route::get('/health', [HealthCheckController::class, 'check'])
    ->middleware(['auth', 'role:super_admin']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/unit/switch', \App\Http\Controllers\UnitSwitchController::class)->name('unit.switch');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('master')->name('master.')->group(function () {
        Route::middleware('role:super_admin,operator_tu')->group(function () {
            Route::get('students/import', [StudentController::class, 'import'])
                ->middleware('can:master.students.import')
                ->name('students.import');
            Route::post('students/import', [StudentController::class, 'processImport'])
                ->middleware('can:master.students.import')
                ->name('students.processImport');
            Route::get('students/export', [StudentController::class, 'export'])
                ->middleware('can:master.students.export')
                ->name('students.export');
            Route::get('students/template', [StudentController::class, 'downloadTemplate'])
                ->middleware('can:master.students.import')
                ->name('students.template');
            Route::resource('students', StudentController::class);
            Route::resource('classes', ClassController::class);
            Route::resource('categories', CategoryController::class);
        });

        Route::middleware('role:super_admin,bendahara')->group(function () {
            Route::resource('fee-types', FeeTypeController::class);
            Route::resource('fee-matrix', FeeMatrixController::class);
        });
    });

    // Transactions
    Route::middleware('role:super_admin,bendahara,kepala_sekolah,operator_tu,auditor')->group(function () {
        Route::resource('transactions', \App\Http\Controllers\Transaction\TransactionController::class);
        Route::get('/receipts/{transaction}/print', [\App\Http\Controllers\ReceiptController::class, 'print'])->name('receipts.print');
    });

    // Invoices
    Route::prefix('invoices')->name('invoices.')->middleware('role:super_admin,bendahara,kepala_sekolah,operator_tu,auditor')->group(function () {
        Route::get('/', [\App\Http\Controllers\Invoice\InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Invoice\InvoiceController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Invoice\InvoiceController::class, 'store'])->name('store');
        Route::get('/generate', [\App\Http\Controllers\Invoice\InvoiceController::class, 'generate'])->name('generate');
        Route::post('/generate', [\App\Http\Controllers\Invoice\InvoiceController::class, 'runGeneration'])->name('runGeneration');
        Route::get('/{invoice}', [\App\Http\Controllers\Invoice\InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/print', [\App\Http\Controllers\Invoice\InvoiceController::class, 'print'])->name('print');
        Route::delete('/{invoice}', [\App\Http\Controllers\Invoice\InvoiceController::class, 'destroy'])->name('destroy');
    });

    // Settlements
    Route::prefix('settlements')->name('settlements.')->middleware('role:super_admin,bendahara,kepala_sekolah,operator_tu,auditor')->group(function () {
        Route::get('/', [\App\Http\Controllers\Settlement\SettlementController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Settlement\SettlementController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Settlement\SettlementController::class, 'store'])->name('store');
        Route::get('/{settlement}', [\App\Http\Controllers\Settlement\SettlementController::class, 'show'])->name('show');
        Route::delete('/{settlement}', [\App\Http\Controllers\Settlement\SettlementController::class, 'destroy'])->name('destroy');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('role:super_admin,bendahara,kepala_sekolah,operator_tu,auditor')->group(function () {
        Route::get('/daily', [\App\Http\Controllers\Report\ReportController::class, 'daily'])->name('daily');
        Route::get('/monthly', [\App\Http\Controllers\Report\ReportController::class, 'monthly'])->name('monthly');
        Route::get('/arrears', [\App\Http\Controllers\Report\ReportController::class, 'arrears'])->name('arrears');
    });
});

require __DIR__ . '/auth.php';

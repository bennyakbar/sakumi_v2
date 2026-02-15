<?php

namespace App\Console\Commands;

use App\Events\ObligationGenerated;
use App\Models\Unit;
use App\Services\ArrearsService;
use Illuminate\Console\Command;

class GenerateMonthlyObligations extends Command
{
    protected $signature = 'obligations:generate
                            {--month= : Month number (1-12), defaults to current month}
                            {--year= : Year, defaults to current year}
                            {--unit= : Unit code (MI, RA, DTA). Required.}';

    protected $description = 'Generate monthly student obligations based on fee matrix';

    public function handle(ArrearsService $arrearsService): int
    {
        $month = (int) ($this->option('month') ?? now()->month);
        $year = (int) ($this->option('year') ?? now()->year);

        $unitCode = $this->option('unit');
        if (! $unitCode) {
            $this->error('--unit is required (e.g. --unit=MI)');

            return self::FAILURE;
        }

        $unit = Unit::where('code', $unitCode)->where('is_active', true)->first();
        if (! $unit) {
            $this->error("Unit '{$unitCode}' not found or inactive.");

            return self::FAILURE;
        }

        session(['current_unit_id' => $unit->id]);

        $this->info("Generating obligations for {$unitCode} {$month}/{$year}...");

        $count = $arrearsService->generateMonthlyObligations($month, $year);

        $this->info("Created {$count} obligation(s).");

        if ($count > 0) {
            ObligationGenerated::dispatch($month, $year, $count);
        }

        return self::SUCCESS;
    }
}

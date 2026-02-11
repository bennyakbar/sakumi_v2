<?php

namespace App\Console\Commands;

use App\Events\ObligationGenerated;
use App\Services\ArrearsService;
use Illuminate\Console\Command;

class GenerateMonthlyObligations extends Command
{
    protected $signature = 'obligations:generate
                            {--month= : Month number (1-12), defaults to current month}
                            {--year= : Year, defaults to current year}';

    protected $description = 'Generate monthly student obligations based on fee matrix';

    public function handle(ArrearsService $arrearsService): int
    {
        $month = (int) ($this->option('month') ?? now()->month);
        $year = (int) ($this->option('year') ?? now()->year);

        $this->info("Generating obligations for {$month}/{$year}...");

        $count = $arrearsService->generateMonthlyObligations($month, $year);

        $this->info("Created {$count} obligation(s).");

        if ($count > 0) {
            ObligationGenerated::dispatch($month, $year, $count);
        }

        return self::SUCCESS;
    }
}

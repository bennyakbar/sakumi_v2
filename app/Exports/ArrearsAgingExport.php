<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ArrearsAgingExport implements FromCollection
{
    public function __construct(
        private readonly array $rows,
    ) {}

    public function collection(): Collection
    {
        return collect($this->rows)->map(fn (array $row) => $row);
    }
}


<?php

namespace App\Models\Concerns;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUnit
{
    public static function bootBelongsToUnit(): void
    {
        static::addGlobalScope('unit', function (Builder $builder): void {
            if ($unitId = session('current_unit_id')) {
                $builder->where(
                    $builder->getModel()->qualifyColumn('unit_id'),
                    $unitId
                );
            }
        });

        static::creating(function (Model $model): void {
            if (! $model->unit_id && $unitId = session('current_unit_id')) {
                $model->unit_id = $unitId;
            }
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}

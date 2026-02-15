<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnitSwitchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
        ]);

        $unit = Unit::where('id', $request->unit_id)
            ->where('is_active', true)
            ->first();

        if (! $unit) {
            return back()->withErrors(['unit_id' => 'Unit tidak aktif.']);
        }

        $user = $request->user();

        if (! $user->hasRole('super_admin') && $user->unit_id !== $unit->id) {
            abort(403, 'Anda tidak memiliki izin untuk berpindah unit.');
        }

        session(['current_unit_id' => $unit->id]);

        return back();
    }
}

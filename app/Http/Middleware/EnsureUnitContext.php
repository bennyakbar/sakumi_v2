<?php

namespace App\Http\Middleware;

use App\Models\Unit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureUnitContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! session()->has('current_unit_id')) {
            if (! $user->unit_id) {
                abort(403, 'Akun Anda belum ditetapkan ke unit manapun. Hubungi administrator.');
            }
            session(['current_unit_id' => $user->unit_id]);
        }

        $currentUnit = Unit::find(session('current_unit_id'));

        $switchableUnits = $user->hasRole('super_admin')
            ? Unit::where('is_active', true)->orderBy('code')->get()
            : collect();

        View::share('currentUnit', $currentUnit);
        View::share('switchableUnits', $switchableUnits);

        return $next($request);
    }
}

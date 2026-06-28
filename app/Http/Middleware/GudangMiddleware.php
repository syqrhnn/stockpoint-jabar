<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GudangMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $userRole = session('role');
        
        if (in_array($userRole, ['admin_gudang', 'manajer_operasional'])) {
            return $next($request);
        }

        $sessionGudangId = session('gudang_id');
        $requestedGudangId = $request->route('gudang_id') ?? $request->route('gudang') ?? $request->input('gudang_id');

        if ($requestedGudangId && $requestedGudangId != $sessionGudangId) {
            abort(403, 'Anda tidak memiliki akses ke data gudang ini.');
        }

        return $next($request);
    }
}

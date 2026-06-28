<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        // Check if user is active
        $isActive = \Illuminate\Support\Facades\DB::table('users')
            ->where('id', session('user_id'))
            ->value('is_active');

        if (!$isActive) {
            session()->flush();
            return redirect('/login')->withErrors(['error' => 'Akun Anda telah dinonaktifkan.']);
        }

        return $next($request);
    }
}

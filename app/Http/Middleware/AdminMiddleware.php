<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses area Admin / POS.');
        }

        // Only Admin and Staff are allowed
        if (!auth()->user()->isAdmin() && !auth()->user()->isStaff()) {
            return redirect()->route('menu')->with('error', '⛔ Akses Ditolak! Akun Anda tidak memiliki izin untuk mengakses area Admin / POS Kasir.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictSoftwareEngineer
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'software engineer') {
            return redirect('/')
                ->with('error', 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
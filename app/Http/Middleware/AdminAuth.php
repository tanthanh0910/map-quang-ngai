<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->get('is_admin')) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}

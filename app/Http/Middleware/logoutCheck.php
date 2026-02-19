<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class logoutCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return $next($request);
        }else{
            return redirect()->route('admin.dashboard')->with('error', 'You are already logged in.');
        }

    }
}

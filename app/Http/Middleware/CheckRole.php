<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            // If API request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login first.',
                ], 401);
            }
            
            // If web request, redirect to login
            return redirect()->route('login');
        }

        // Check if user has required role
        if (!$request->user()->hasRole($role)) {
            // If API request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. This action requires ' . $role . ' role.',
                ], 403);
            }
            
            // If web request, abort
            abort(403, 'Unauthorized. This action requires ' . $role . ' role.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || !$request->user()->role) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            return redirect()->route('login');
        }

        if ($request->user()->role->name !== $role) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized - Insufficient permissions'
                ], 403);
            }
            
            // Redirect to their respective dashboard based on role
            if ($request->user()->role->name === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($request->user()->role->name === 'project_manager') {
                return redirect()->route('manager.dashboard');
            } elseif ($request->user()->role->name === 'client') {
                return redirect()->route('client.dashboard');
            }
            
            return redirect()->route('login');
        }

        return $next($request);
    }
}
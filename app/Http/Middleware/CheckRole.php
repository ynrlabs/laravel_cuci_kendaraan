<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('login'));
        }

        $user = Auth::user();

        // Support comma separated roles as well as variable arguments
        $allowedRoles = [];
        $roleAliases = [
            'kasir' => 'cashier',
            'admin_cabang' => 'admin_branch',
            'owner' => 'super_admin',
        ];

        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = trim($r);
                $allowedRoles[] = $trimmed;
                if (isset($roleAliases[$trimmed])) {
                    $allowedRoles[] = $roleAliases[$trimmed];
                }
            }
        }
        $allowedRoles = array_unique($allowedRoles);

        $hasAccess = false;
        foreach ($allowedRoles as $role) {
            if ($user->hasRole($role)) {
                $hasAccess = true;
                break;
            }
        }


        if (!$hasAccess) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Forbidden. Insufficient role permissions.'], 403);
            }
            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}


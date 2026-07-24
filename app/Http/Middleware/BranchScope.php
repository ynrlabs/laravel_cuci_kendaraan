<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class BranchScope
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Super admin can access all branches
        if ($user && $user->hasRole('super_admin')) {
            return $next($request);
        }
        
        // For admin_branch, cashier, operator - scope to their branch
        if ($user) {
            $branchId = $user->getBranchId();
            
            if ($branchId) {
                // Add branch_id to request for controllers to use
                $request->merge(['branch_scope_id' => $branchId]);
            }
        }
        
        return $next($request);
    }
}

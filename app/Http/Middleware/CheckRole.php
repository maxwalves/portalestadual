<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!$request->user()) {
            abort(403, 'Acesso não autorizado.');
        }

        $user = $request->user();
        
        // Se o role contém |, significa múltiplos roles
        if (strpos($role, '|') !== false) {
            $roles = explode('|', $role);
            $hasAnyRole = false;
            
            foreach ($roles as $singleRole) {
                if ($user->hasRole(trim($singleRole))) {
                    $hasAnyRole = true;
                    break;
                }
            }
            
            if (!$hasAnyRole) {
                abort(403, 'Acesso não autorizado.');
            }
        } else {
            // Role único
            if (!$user->hasRole($role)) {
                abort(403, 'Acesso não autorizado.');
            }
        }

        return $next($request);
    }
} 
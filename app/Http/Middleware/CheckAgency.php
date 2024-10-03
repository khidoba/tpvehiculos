<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAgency
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Si el usuario es Administrador de Sistema, permitir acceso a todo
            if ($user->hasRole('Administrador de Sistema')) {
                return $next($request);
            }

            // Para otros roles, verificar la agencia
            if ($request->route('agencia') && $request->route('agencia') !== $user->agencia) {
                abort(403, 'No tienes permiso para acceder a esta agencia.');
            }

            // Añadir la agencia del usuario a la solicitud para usarla en los controladores
            $request->merge(['user_agencia' => $user->agencia]);
        }

        return $next($request);
    }
}

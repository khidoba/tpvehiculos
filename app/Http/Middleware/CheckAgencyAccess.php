<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAgencyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->hasRole('Administrador de Sistema')) {
            return $next($request);
        }

        $agenciaId = $request->route('agencia') ?? $request->input('agencia_id');

        if ($agenciaId && $user->agencia_id != $agenciaId) {
            abort(403, 'No tienes permiso para acceder a esta agencia.');
        }

        return $next($request);
    }
}

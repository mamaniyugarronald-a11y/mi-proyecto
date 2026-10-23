<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UsuarioModel;

class AuthCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('idusuario')) {
            return redirect()->route('login')->with('error_login', 'Debe iniciar sesión para acceder al sistema.');
        }

        $usuario = UsuarioModel::find(session('idusuario'));
        if (!$usuario || (int) $usuario->estado !== 1) {
            session()->flush();
            return redirect()->route('login')->with('error_login', 'Su cuenta fue desactivada.');
        }

        return $next($request);
    }
}

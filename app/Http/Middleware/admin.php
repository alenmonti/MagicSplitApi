<?php

namespace App\Http\Middleware;

use App\Models\Group;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $group = Group::find($request->id);
        if (!$request->user()->isAdminOfGroup($group)) {
            return response()->json(['message' => 'No tienes permisos para realizar esta acción'], 403);
        }
        return $next($request);
    }
}

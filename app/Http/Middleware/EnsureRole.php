<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureRole
{
    /**
     * @param  list<string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $roleValue = is_object($user->role) && property_exists($user->role, 'value')
            ? $user->role->value
            : (string) $user->role;

        if (!in_array($roleValue, $roles, true)) {
            abort(403, 'Недостаточно прав для доступа к этой странице.');
        }

        return $next($request);
    }
}
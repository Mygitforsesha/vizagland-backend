<?php

namespace App\Modules\Auth\Middleware;

use App\Constants\HttpStatus;
use App\Enums\ApiResponseStatus;
use App\Modules\User\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $this->deny('Unauthenticated.', HttpStatus::UNAUTHORIZED);
        }

        if (! $user->isActive()) {
            return $this->deny('Your account is inactive.', HttpStatus::FORBIDDEN);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $allowedRoles = array_map(
            fn (string $role) => UserRole::tryFrom($role)?->value ?? $role,
            $roles,
        );

        if (! $user->hasAnyRole($allowedRoles)) {
            return $this->deny('You do not have permission to access this resource.', HttpStatus::FORBIDDEN);
        }

        return $next($request);
    }

    private function deny(string $message, int $statusCode): Response
    {
        return response()->json([
            'status' => ApiResponseStatus::Error->value,
            'message' => $message,
        ], $statusCode);
    }
}

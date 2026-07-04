<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Redirige al onboarding a quien aún no lo ha completado.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $profile = $user->profile;

            if (! $profile || ! $profile->onboarding_completed) {
                return redirect()->route('onboarding.basico');
            }
        }

        return $next($request);
    }
}

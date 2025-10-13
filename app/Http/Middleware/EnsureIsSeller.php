<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSeller
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            \Log::info('EnsureIsSeller: No user authenticated');

            return redirect()->route('login');
        }

        \Log::info('EnsureIsSeller check', [
            'user_id' => $request->user()->id,
            'user_email' => $request->user()->email,
            'has_seller_role' => $request->user()->hasRole('seller'),
            'roles' => $request->user()->roles->pluck('name')->toArray(),
        ]);

        if (! $request->user()->hasRole('seller')) {
            \Log::warning('EnsureIsSeller: Access denied', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
            ]);
            abort(403, 'Acesso negado. Você precisa ser um vendedor para acessar esta área.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerApproved
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\Seller|null $seller */
        $seller = $request->user()->seller;

        if (! $seller) {
            return redirect()->route('seller.register')
                ->with('info', 'Você precisa criar um perfil de vendedor primeiro.');
        }

        if (! $seller->isApproved()) {
            abort(403, 'Seu perfil de vendedor ainda não foi aprovado. Aguarde a análise do administrador.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display seller dashboard with statistics.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        /** @var \App\Models\Seller|null $seller */
        $seller = $user->seller;

        if (! $seller) {
            return redirect()->route('seller.register')
                ->with('info', 'Você precisa criar um perfil de vendedor primeiro.');
        }

        // Calculate statistics
        $stats = [
            'total_products' => $seller->products()->count(),
            'published_products' => $seller->products()->where('status', 'published')->count(),
            'total_orders' => $seller->orders()->count(),
            'pending_orders' => $seller->orders()->whereIn('status', ['awaiting_payment', 'preparing'])->count(),
            'completed_orders' => $seller->orders()->where('status', 'delivered')->count(),
            'total_revenue' => $seller->orders()
                ->whereIn('status', ['preparing', 'shipped', 'delivered'])
                ->sum('total'),
            'monthly_revenue' => $seller->orders()
                ->whereIn('status', ['preparing', 'shipped', 'delivered'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
        ];

        return view('seller.dashboard', compact('seller', 'stats'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Services\SellerService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct(
        private SellerService $sellerService
    ) {}

    /**
     * Display a listing of sellers.
     */
    public function index(Request $request)
    {
        $query = Seller::query()->with(['user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by store name, document, user name, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', '%'.$search.'%')
                    ->orWhere('document_number', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        // Sort (new system with whitelist)
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Whitelist sortable columns (security)
        $allowedSorts = ['store_name', 'status', 'created_at'];
        if (! in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortDirection);

        // Get all sellers (Alpine.js will handle pagination)
        $sellers = $query->get();

        return view('admin.sellers.index', compact('sellers', 'sortField', 'sortDirection'));
    }

    /**
     * Display the specified seller.
     */
    public function show(Seller $seller)
    {
        $seller->load(['user', 'products', 'orders']);

        $stats = $this->sellerService->getSellerStats($seller);

        return view('admin.sellers.show', compact('seller', 'stats'));
    }

    /**
     * Approve seller.
     */
    public function approve(Seller $seller)
    {
        // Authorization via admin middleware
        try {
            $this->sellerService->approveSeller($seller);

            return back()->with('success', 'Vendedor aprovado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Suspend seller.
     */
    public function suspend(Seller $seller)
    {
        // Authorization via admin middleware
        try {
            $this->sellerService->suspendSeller($seller);

            return back()->with('success', 'Vendedor suspenso com sucesso! Todos os produtos foram despublicados.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reactivate seller.
     */
    public function reactivate(Seller $seller)
    {
        // Authorization via admin middleware
        try {
            $this->sellerService->reactivateSeller($seller);

            return back()->with('success', 'Vendedor reativado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

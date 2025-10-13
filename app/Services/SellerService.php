<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\SellerAddress;
use App\Models\SellerPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SellerService
{
    /**
     * Create seller profile.
     */
    public function createSeller(User $user, array $data): Seller
    {
        // Check if user already has a seller profile
        if ($user->seller) {
            throw new \Exception('Usuário já possui cadastro como vendedor');
        }

        return DB::transaction(function () use ($user, $data) {
            // Extract address data
            $addressData = [
                'postal_code' => $data['postal_code'] ?? null,
                'street' => $data['street'] ?? null,
                'number' => $data['number'] ?? null,
                'complement' => $data['complement'] ?? null,
                'neighborhood' => $data['neighborhood'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
            ];

            // Remove address fields from seller data
            $sellerData = collect($data)->except([
                'type',
                'postal_code',
                'street',
                'number',
                'complement',
                'neighborhood',
                'city',
                'state',
            ])->toArray();

            // Generate unique slug from store_name
            $sellerData['slug'] = $this->generateUniqueSlug($sellerData['store_name']);

            // Set user_id and default status
            $sellerData['user_id'] = $user->id;
            $sellerData['status'] = 'pending';

            $seller = Seller::create($sellerData);

            // Create business address if address data provided
            if (! empty($addressData['postal_code'])) {
                SellerAddress::create([
                    'seller_id' => $seller->id,
                    'type' => $data['type'] ?? 'business',
                    'is_default' => true,
                    ...$addressData,
                ]);
            }

            // Assign seller role if not already assigned
            if (! $user->hasRole('seller')) {
                $user->assignRole('seller');
            }

            return $seller;
        });
    }

    /**
     * Update seller profile.
     */
    public function updateSeller(Seller $seller, array $data): Seller
    {
        return DB::transaction(function () use ($seller, $data) {
            // Update slug if store name changed
            if (! empty($data['store_name']) && $data['store_name'] !== $seller->store_name) {
                $data['slug'] = $this->generateUniqueSlug($data['store_name'], $seller->id);
            }

            $seller->update($data);

            return $seller->fresh();
        });
    }

    /**
     * Approve seller.
     */
    public function approveSeller(Seller $seller): Seller
    {
        if ($seller->isApproved()) {
            throw new \Exception('Vendedor já está aprovado.');
        }

        $seller->update([
            'status' => 'active',
            'approved_at' => now(),
        ]);

        return $seller->fresh();
    }

    /**
     * Reject seller.
     */
    public function rejectSeller(Seller $seller): Seller
    {
        $seller->update([
            'status' => 'inactive',
            'approved_at' => null,
        ]);

        return $seller->fresh();
    }

    /**
     * Suspend seller.
     */
    public function suspendSeller(Seller $seller, ?string $reason = null): Seller
    {
        if ($seller->status === 'suspended') {
            throw new \Exception('Vendedor já está suspenso.');
        }

        DB::transaction(function () use ($seller) {
            $seller->update([
                'status' => 'suspended',
                'approved_at' => null,
            ]);

            // Unpublish all seller products
            $seller->products()->where('status', 'published')->update(['status' => 'draft']);

            // TODO: Store suspension reason in audit log or separate table
            // For now, reason parameter is accepted for future implementation
        });

        return $seller->fresh();
    }

    /**
     * Reactivate suspended seller.
     */
    public function reactivateSeller(Seller $seller): Seller
    {
        if ($seller->status !== 'suspended') {
            throw new \Exception('Apenas vendedores suspensos podem ser reativados.');
        }

        $seller->update([
            'status' => 'active',
            'approved_at' => now(),
        ]);

        return $seller->fresh();
    }

    /**
     * Get all sellers (admin view).
     */
    public function getAllSellers(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Seller::with(['user']);

        // Filter by status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by search
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        // Sort by approval date
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        return $query->orderBy($sortBy, $sortDirection)->paginate($perPage);
    }

    /**
     * Get approved sellers for marketplace.
     */
    public function getApprovedSellers(int $perPage = 20): LengthAwarePaginator
    {
        return Seller::approved()
            ->with(['products' => function ($query) {
                $query->where('status', 'published')->limit(4);
            }])
            ->latest('approved_at')
            ->paginate($perPage);
    }

    /**
     * Get pending sellers.
     */
    public function getPendingSellers()
    {
        return Seller::where('status', 'pending')
            ->with(['user'])
            ->latest()
            ->get();
    }

    /**
     * Get seller by slug.
     */
    public function getSellerBySlug(string $slug): ?Seller
    {
        return Seller::where('slug', $slug)->first();
    }

    /**
     * Check if seller is approved.
     */
    public function isApproved(Seller $seller): bool
    {
        return $seller->isApproved();
    }

    /**
     * Calculate total sales for seller.
     * Includes paid, preparing, shipped, and delivered orders.
     */
    public function calculateTotalSales(Seller $seller): string
    {
        $total = $seller->orders()
            ->whereIn('status', ['paid', 'preparing', 'shipped', 'delivered'])
            ->sum('total');

        return number_format($total, 2, '.', '');
    }

    /**
     * Calculate earnings for seller.
     * Includes paid, preparing, shipped, and delivered orders.
     */
    public function calculateEarnings(Seller $seller): string
    {
        $totalSales = $seller->orders()
            ->whereIn('status', ['paid', 'preparing', 'shipped', 'delivered'])
            ->sum('total');

        $commissionPercentage = $seller->commission_percentage ?? 10.00;
        $commission = ($totalSales * $commissionPercentage) / 100;
        $earnings = $totalSales - $commission;

        return number_format($earnings, 2, '.', '');
    }

    /**
     * Get products count for seller.
     */
    public function getProductsCount(Seller $seller): int
    {
        return $seller->products()->count();
    }

    /**
     * Get orders count for seller.
     */
    public function getOrdersCount(Seller $seller): int
    {
        return $seller->orders()->count();
    }

    /**
     * Get sales report for seller.
     * Includes paid, preparing, shipped, and delivered orders.
     */
    public function getSalesReport(Seller $seller): array
    {
        $validStatuses = ['paid', 'preparing', 'shipped', 'delivered'];

        $totalOrders = $seller->orders()->whereIn('status', $validStatuses)->count();
        $totalSales = $seller->orders()->whereIn('status', $validStatuses)->sum('total');
        $commissionPercentage = $seller->commission_percentage ?? 10.00;
        $commissionPaid = ($totalSales * $commissionPercentage) / 100;
        $totalEarnings = $totalSales - $commissionPaid;

        return [
            'total_sales' => number_format($totalSales, 2, '.', ''),
            'total_orders' => $totalOrders,
            'total_earnings' => number_format($totalEarnings, 2, '.', ''),
            'commission_paid' => number_format($commissionPaid, 2, '.', ''),
        ];
    }

    /**
     * Calculate seller commission.
     */
    public function calculateCommission(float $orderSubtotal, ?float $commissionPercentage = null): array
    {
        // Get commission percentage from settings or use default
        $commissionPercentage = $commissionPercentage ?? 10.00;

        $commissionAmount = ($orderSubtotal * $commissionPercentage) / 100;
        $sellerAmount = $orderSubtotal - $commissionAmount;

        return [
            'subtotal' => round($orderSubtotal, 2),
            'commission_percentage' => $commissionPercentage,
            'commission_amount' => round($commissionAmount, 2),
            'seller_amount' => round($sellerAmount, 2),
        ];
    }

    /**
     * Create seller payment record.
     */
    public function createSellerPayment(
        Seller $seller,
        int $orderId,
        float $amount,
        string $status = 'pending',
        ?string $externalPaymentId = null
    ): SellerPayment {
        return SellerPayment::create([
            'seller_id' => $seller->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'status' => $status,
            'external_payment_id' => $externalPaymentId,
        ]);
    }

    /**
     * Update seller payment status.
     */
    public function updatePaymentStatus(SellerPayment $payment, string $status, ?string $externalPaymentId = null): SellerPayment
    {
        $allowedStatuses = ['pending', 'processing', 'completed', 'failed'];

        if (! in_array($status, $allowedStatuses)) {
            throw new \Exception("Status de pagamento inválido: {$status}");
        }

        $updateData = ['status' => $status];

        if ($status === 'completed') {
            $updateData['paid_at'] = now();
        }

        if ($externalPaymentId) {
            $updateData['external_payment_id'] = $externalPaymentId;
        }

        $payment->update($updateData);

        return $payment->fresh();
    }

    /**
     * Get seller earnings report.
     */
    public function getSellerEarningsReport(Seller $seller, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = SellerPayment::where('seller_id', $seller->id);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $payments = $query->get();

        $totalEarnings = $payments->where('status', 'completed')->sum('amount');
        $pendingEarnings = $payments->where('status', 'pending')->sum('amount');
        $processingEarnings = $payments->where('status', 'processing')->sum('amount');
        $failedEarnings = $payments->where('status', 'failed')->sum('amount');

        return [
            'total_earnings' => round($totalEarnings, 2),
            'pending_earnings' => round($pendingEarnings, 2),
            'processing_earnings' => round($processingEarnings, 2),
            'failed_earnings' => round($failedEarnings, 2),
            'total_payments' => $payments->count(),
            'completed_payments' => $payments->where('status', 'completed')->count(),
        ];
    }

    /**
     * Get seller sales statistics.
     */
    public function getSellerStats(Seller $seller): array
    {
        $orders = $seller->orders()
            ->whereIn('status', ['paid', 'preparing', 'shipped', 'delivered'])
            ->get();

        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('subtotal');

        // Get product count
        $totalProducts = $seller->products()->count();
        $publishedProducts = $seller->products()->where('status', 'published')->count();

        // Get earnings data
        $earningsData = $this->getSellerEarningsReport($seller);

        return [
            'total_sales' => $totalOrders,
            'total_revenue' => round($totalRevenue, 2),
            'total_products' => $totalProducts,
            'published_products' => $publishedProducts,
            'pending_earnings' => $earningsData['pending_earnings'],
            'approval_status' => $seller->status,
            'is_approved' => $seller->isApproved(),
        ];
    }

    /**
     * Get pending seller approvals.
     */
    public function getPendingApprovals(): Collection
    {
        return Seller::where('status', 'pending')
            ->with(['user'])
            ->latest()
            ->get();
    }

    /**
     * Generate unique slug for seller.
     */
    private function generateUniqueSlug(string $storeName, ?int $excludeId = null): string
    {
        $slug = str()->slug($storeName);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists.
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Seller::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}

<?php

namespace App\Policies;

use App\Models\Seller;
use App\Models\User;

class SellerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('sellers.view-all');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Seller $seller): bool
    {
        // Anyone can view approved seller profiles (public)
        if ($seller->isApproved()) {
            return true;
        }

        // Unauthenticated users cannot view unapproved sellers
        if (! $user) {
            return false;
        }

        // Admin can view all sellers
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can view their own seller profile
        if ($user->hasRole('seller') && $seller->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only users without seller profile can create one
        // Admin can create for testing purposes
        if ($user->hasRole('admin')) {
            return true;
        }

        return ! $user->seller && $user->can('sellers.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Seller $seller): bool
    {
        // Admin can update any seller
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can only update their own profile
        if ($user->hasRole('seller') && $seller->user_id === $user->id) {
            return $user->can('sellers.edit');
        }

        return false;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Seller $seller): bool
    {
        // Only admin can approve sellers
        return $user->hasRole('admin') && $user->can('sellers.approve');
    }

    /**
     * Determine whether the user can suspend the model.
     */
    public function suspend(User $user, Seller $seller): bool
    {
        // Only admin can suspend sellers
        return $user->hasRole('admin') && $user->can('sellers.suspend');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Seller $seller): bool
    {
        // Only admin can delete sellers
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Seller $seller): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Seller $seller): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, Seller $seller): bool
    {
        return $user->hasRole('admin') && $user->can('sellers.approve');
    }

    /**
     * Determine whether the user can view seller orders.
     */
    public function viewOrders(User $user, Seller $seller): bool
    {
        // Admin can view all orders
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can view their own orders
        return $seller->user_id === $user->id;
    }

    /**
     * Determine whether the user can view sales report.
     */
    public function viewSalesReport(User $user, Seller $seller): bool
    {
        // Admin can view all reports
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can view their own report
        return $seller->user_id === $user->id;
    }

    /**
     * Determine whether the user can access dashboard.
     */
    public function accessDashboard(User $user, Seller $seller): bool
    {
        // Admin can access any dashboard
        if ($user->hasRole('admin')) {
            return true;
        }

        // Only approved sellers can access their dashboard
        return $seller->user_id === $user->id && $seller->isApproved();
    }
}

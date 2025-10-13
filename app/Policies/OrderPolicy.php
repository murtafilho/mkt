<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('orders.view') || $user->can('orders.view-all');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Admin can view all orders
        if ($user->hasRole('admin') && $user->can('orders.view-all')) {
            return true;
        }

        // Seller can view orders from their store
        /** @var \App\Models\Seller|null $seller */
        $seller = $order->seller;
        if ($user->hasRole('seller') && $seller && $seller->user_id === $user->id) {
            return $user->can('orders.view');
        }

        // Customer can view their own orders (no permission check needed)
        if ($order->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create orders
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        // Only admin can directly update orders
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update order status.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        // Admin can update any order status
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can update status of their orders
        /** @var \App\Models\Seller|null $seller */
        $seller = $order->seller;
        if ($user->hasRole('seller') && $seller && $seller->user_id === $user->id) {
            return $user->can('orders.update-status');
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, Order $order): bool
    {
        // Admin can cancel any order
        if ($user->hasRole('admin')) {
            return true;
        }

        // Customer can cancel their own orders if awaiting_payment or paid (not shipped yet)
        if ($order->user_id === $user->id) {
            return in_array($order->status, ['awaiting_payment', 'paid']);
        }

        // Seller can cancel orders from their store if not shipped
        /** @var \App\Models\Seller|null $seller */
        $seller = $order->seller;
        if ($user->hasRole('seller') && $seller && $seller->user_id === $user->id) {
            return in_array($order->status, ['paid', 'preparing']) && $user->can('orders.cancel');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        // Only admin can delete orders
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->hasRole('admin');
    }
}

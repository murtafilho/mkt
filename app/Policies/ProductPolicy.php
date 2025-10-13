<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view published products
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Product $product): bool
    {
        // Allow guests to view published products
        if (! $user) {
            return $product->status === 'published';
        }

        // Admin can view all products
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can view their own products
        /** @var \App\Models\Seller|null $seller */
        $seller = $product->seller;
        if ($user->hasRole('seller') && $seller && $seller->user_id === $user->id) {
            return true;
        }

        // Customers can only view published products
        return $product->status === 'published';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin can always create products
        if ($user->hasRole('admin')) {
            return true;
        }

        // Only users with seller accounts can create products
        /** @var \App\Models\Seller|null $seller */
        $seller = $user->seller;

        if (! $seller) {
            return false;
        }

        // Seller can create products even if pending (as drafts)
        // Only suspended sellers cannot create products
        return $seller->status !== 'suspended';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        // Admin can update any product
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can only update their own products
        /** @var \App\Models\Seller|null $seller */
        $seller = $product->seller;

        if (! $seller || $seller->user_id !== $user->id) {
            return false;
        }

        // Suspended sellers cannot update products
        return $seller->status !== 'suspended';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        // Admin can delete any product
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can only delete their own products
        /** @var \App\Models\Seller|null $seller */
        $seller = $product->seller;
        if ($user->hasRole('seller') && $seller && $seller->user_id === $user->id) {
            return $user->can('products.delete');
        }

        return false;
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, Product $product): bool
    {
        // Admin can publish any product
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can publish their own products
        /** @var \App\Models\Seller|null $seller */
        $seller = $product->seller;
        if ($user->hasRole('seller') && $seller && $seller->user_id === $user->id) {
            return $user->can('products.publish');
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Only admin can force delete
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can unpublish the model.
     */
    public function unpublish(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }

    /**
     * Determine whether the user can manage stock.
     */
    public function manageStock(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}

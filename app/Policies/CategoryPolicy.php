<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Everyone can view categories (including guests)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Category $category): bool
    {
        // Everyone can view active categories
        if (! $user) {
            return $category->is_active;
        }

        // Admin can view all categories
        if ($user->hasRole('admin')) {
            return true;
        }

        // Others can only view active categories
        return $category->is_active;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin can create categories
        return $user->hasRole('admin') && $user->can('categories.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $category): bool
    {
        // Only admin can update categories
        return $user->hasRole('admin') && $user->can('categories.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $category): bool
    {
        // Only admin can delete categories
        // Cannot delete if it has subcategories or products
        if (! $user->hasRole('admin') || ! $user->can('categories.delete')) {
            return false;
        }

        // Check if category has subcategories
        if ($category->children()->count() > 0) {
            return false;
        }

        // Check if category has products
        if ($category->products()->count() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Category $category): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return $user->hasRole('admin');
    }
}

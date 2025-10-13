<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Product permissions
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.publish',

            // Order permissions
            'orders.view',
            'orders.view-all',
            'orders.update-status',
            'orders.cancel',

            // Seller permissions
            'sellers.view',
            'sellers.view-all',
            'sellers.create',
            'sellers.edit',
            'sellers.approve',
            'sellers.suspend',

            // Category permissions
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            // User permissions
            'users.view-all',
            'users.edit',
            'users.delete',

            // Payment permissions
            'payments.view',
            'payments.view-all',

            // Settings permissions
            'settings.view',
            'settings.edit',

            // Reports permissions
            'reports.sales',
            'reports.sellers',
            'reports.products',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin role - all permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Seller role - limited permissions
        $sellerRole = Role::create(['name' => 'seller']);
        $sellerRole->givePermissionTo([
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.publish',
            'orders.view',
            'orders.update-status',
            'sellers.view',
            'sellers.edit',
            'payments.view',
        ]);

        // Customer role - very limited permissions
        $customerRole = Role::create(['name' => 'customer']);
        $customerRole->givePermissionTo([
            'products.view',
            'orders.view',
        ]);
    }
}

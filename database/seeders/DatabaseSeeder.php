<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolePermissionSeeder::class,
            CategoriesSeeder::class,
            SettingsSeeder::class,
        ]);

        // Create fixed test users
        $admin = User::factory()->create([
            'name' => 'Administrador Vale do Sol',
            'email' => 'admin@valedosol.org',
            'password' => bcrypt('password'),
            'cpf_cnpj' => '12345678901',
            'phone' => '11987654321',
            'birth_date' => '1990-01-01',
        ]);
        $admin->assignRole('admin');

        $seller = User::factory()->create([
            'name' => 'João Silva Vendedor',
            'email' => 'seller@valedosol.org',
            'password' => bcrypt('password'),
            'cpf_cnpj' => '98765432100',
            'phone' => '11912345678',
            'birth_date' => '1985-05-15',
        ]);
        $seller->assignRole('seller');

        $customer = User::factory()->create([
            'name' => 'Maria Santos Cliente',
            'email' => 'customer@valedosol.org',
            'password' => bcrypt('password'),
            'cpf_cnpj' => '45678912300',
            'phone' => '11998765432',
            'birth_date' => '1995-10-20',
        ]);
        $customer->assignRole('customer');

        // Seed marketplace data with faker
        $this->call([
            SellersSeeder::class,    // 20 sellers with addresses and payment info
            ProductsSeeder::class,   // ~100 products distributed among sellers
            OrdersSeeder::class,     // 50 orders with items and addresses
        ]);
    }
}

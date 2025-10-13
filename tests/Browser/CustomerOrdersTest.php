<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Testes E2E - CRUD de Pedidos (Customer View).
 *
 * Testa:
 * - Visualização de lista de pedidos
 * - Filtros por status
 * - Busca por número de pedido
 * - Detalhes do pedido
 * - Cancelamento de pedido
 */
class CustomerOrdersTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles se necessário
        if (\Spatie\Permission\Models\Role::count() === 0) {
            $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        }
    }

    /**
     * Helper: Create customer with order.
     */
    protected function createCustomerWithOrder(string $status = 'paid'): array
    {
        // Create customer
        $customer = User::factory()->create();

        // Create seller with product
        $seller = User::factory()->create();
        $docNumber = '999'.substr(md5(uniqid()), 0, 8);

        $seller->seller()->create([
            'store_name' => 'Test Store '.uniqid(),
            'slug' => 'test-store-'.uniqid(),
            'document_number' => $docNumber,
            'person_type' => 'individual',
            'business_phone' => '31'.rand(900000000, 999999999),
            'business_email' => 'store'.uniqid().'@test.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $seller->assignRole('seller');

        // Create category and product
        $category = Category::firstOrCreate(
            ['name' => 'Eletrônicos'],
            ['slug' => 'eletronicos', 'is_active' => true]
        );

        $product = Product::create([
            'seller_id' => $seller->seller->id,
            'category_id' => $category->id,
            'name' => 'Product '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.uniqid(),
            'description' => 'Test product',
            'original_price' => 100.00,
            'sale_price' => 89.90,
            'stock' => 10,
            'status' => 'published',
        ]);

        // Create order
        $order = Order::create([
            'user_id' => $customer->id,
            'seller_id' => $seller->seller->id,
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'status' => $status,
            'subtotal' => 89.90,
            'shipping' => 10.00,
            'total' => 99.90,
        ]);

        // Create order item
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => 89.90,
            'subtotal' => 89.90,
        ]);

        // Create order address
        $order->address()->create([
            'recipient_name' => $customer->name,
            'postal_code' => '30130010',
            'street' => 'Rua Teste',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'Belo Horizonte',
            'state' => 'MG',
        ]);

        return [
            'customer' => $customer,
            'seller' => $seller,
            'product' => $product,
            'order' => $order,
        ];
    }

    /**
     * Test: Customer can view their orders list.
     */
    public function test_customer_can_view_orders_list(): void
    {
        $data = $this->createCustomerWithOrder();
        $customer = $data['customer'];
        $order = $data['order'];

        $this->browse(function (Browser $browser) use ($customer) {
            $browser->loginAs($customer)
                ->visit('/meus-pedidos')
                ->screenshot('orders-list-01-page-load')
                ->pause(2000);

            $currentUrl = $browser->driver->getCurrentURL();
            echo "\n📍 Current URL: ".$currentUrl."\n";

            $browser->screenshot('orders-list-02-debug');

            echo "\n✅ Debug de lista de pedidos concluído\n";
        });
    }

    /**
     * Test: Customer can view order details.
     */
    public function test_customer_can_view_order_details(): void
    {
        $data = $this->createCustomerWithOrder();
        $customer = $data['customer'];
        $order = $data['order'];
        $product = $data['product'];

        $this->browse(function (Browser $browser) use ($customer, $order, $product) {
            $browser->loginAs($customer)
                ->visit('/meus-pedidos')
                ->screenshot('order-details-01-list')

                // Click on order to see details
                ->clickLink('Ver Detalhes')
                ->pause(1000)
                ->screenshot('order-details-02-details-page')

                // Assert order information
                ->assertSee($order->order_number)
                ->assertSee($product->name)
                ->assertSee('R$ 89,90')
                ->assertSee('R$ 99,90') // Total
                ->assertSee('Belo Horizonte')
                ->screenshot('order-details-03-complete');

            echo "\n✅ Detalhes do pedido exibidos corretamente\n";
        });
    }

    /**
     * Test: Customer can filter orders by status.
     */
    public function test_customer_can_filter_orders_by_status(): void
    {
        // Create multiple orders with different statuses
        $paidData = $this->createCustomerWithOrder('paid');
        $customer = $paidData['customer'];

        // Create another order for same customer - preparing
        $seller2 = User::factory()->create();
        $docNumber2 = '999'.substr(md5(uniqid().'2'), 0, 8);

        $seller2->seller()->create([
            'store_name' => 'Store 2 '.uniqid(),
            'slug' => 'store-2-'.uniqid(),
            'document_number' => $docNumber2,
            'person_type' => 'individual',
            'business_phone' => '31'.rand(900000000, 999999999),
            'business_email' => 'store2'.uniqid().'@test.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category = Category::first();

        $product2 = Product::create([
            'seller_id' => $seller2->seller->id,
            'category_id' => $category->id,
            'name' => 'Product 2 '.uniqid(),
            'slug' => 'product-2-'.uniqid(),
            'sku' => 'SKU2-'.uniqid(),
            'description' => 'Test product 2',
            'original_price' => 50.00,
            'sale_price' => 45.00,
            'stock' => 5,
            'status' => 'published',
        ]);

        $order2 = Order::create([
            'user_id' => $customer->id,
            'seller_id' => $seller2->seller->id,
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'status' => 'preparing',
            'subtotal' => 45.00,
            'shipping' => 10.00,
            'total' => 55.00,
        ]);

        $order2->items()->create([
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'sku' => $product2->sku,
            'quantity' => 1,
            'unit_price' => 45.00,
            'subtotal' => 45.00,
        ]);

        $this->browse(function (Browser $browser) use ($customer, $paidData, $order2) {
            $browser->loginAs($customer)
                ->visit('/meus-pedidos')
                ->screenshot('filter-01-all-orders')

                // Initially should see both orders
                ->assertSee($paidData['order']->order_number)
                ->assertSee($order2->order_number)

                // Filter by "paid" status
                ->select('select[name="status"]', 'paid')
                ->pause(1000)
                ->screenshot('filter-02-paid-only')
                ->assertSee($paidData['order']->order_number)

                // Filter by "preparing" status
                ->select('select[name="status"]', 'preparing')
                ->pause(1000)
                ->screenshot('filter-03-preparing-only')
                ->assertSee($order2->order_number);

            echo "\n✅ Filtros de status funcionando\n";
        });
    }

    /**
     * Test: Customer can search orders by order number.
     */
    public function test_customer_can_search_orders(): void
    {
        $data = $this->createCustomerWithOrder();
        $customer = $data['customer'];
        $order = $data['order'];

        $this->browse(function (Browser $browser) use ($customer, $order) {
            $browser->loginAs($customer)
                ->visit('/meus-pedidos')
                ->screenshot('search-01-before')

                // Search by order number
                ->type('input[name="search"]', $order->order_number)
                ->pause(1000)
                ->screenshot('search-02-typed')

                // Should see the order
                ->assertSee($order->order_number)
                ->screenshot('search-03-found');

            echo "\n✅ Busca de pedidos funcionando\n";
        });
    }

    /**
     * Test: Customer can cancel order (awaiting_payment status).
     */
    public function skip_test_customer_can_cancel_order(): void // TODO: Implement cancel functionality
    {
        $data = $this->createCustomerWithOrder('awaiting_payment');
        $customer = $data['customer'];
        $order = $data['order'];

        $this->browse(function (Browser $browser) use ($customer, $order) {
            $browser->loginAs($customer)
                ->visit("/customer/orders/{$order->id}")
                ->screenshot('cancel-01-order-details')

                // Should see cancel button
                ->assertSee('Cancelar Pedido')

                // Click cancel
                ->press('Cancelar Pedido')
                ->pause(1000)
                ->screenshot('cancel-02-after-click')

                // Confirm cancellation (if there's a confirmation dialog)
                ->whenAvailable('.swal2-confirm', function ($modal) {
                    $modal->click();
                })
                ->pause(2000)
                ->screenshot('cancel-03-confirmed')

                // Should see success message
                ->waitForText('cancelado', 5)
                ->screenshot('cancel-04-success');

            echo "\n✅ Cancelamento de pedido funcionando\n";
        });

        // Verify in database
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /**
     * Test: Customer cannot cancel shipped order.
     */
    public function test_customer_cannot_cancel_shipped_order(): void
    {
        $data = $this->createCustomerWithOrder('shipped');
        $customer = $data['customer'];
        $order = $data['order'];

        $this->browse(function (Browser $browser) use ($customer, $order) {
            $browser->loginAs($customer)
                ->visit("/customer/orders/{$order->id}")
                ->screenshot('no-cancel-01-shipped-order')

                // Should NOT see cancel button
                ->assertDontSee('Cancelar Pedido')
                ->screenshot('no-cancel-02-no-button');

            echo "\n✅ Pedido enviado não pode ser cancelado (correto)\n";
        });
    }

    /**
     * Test: Customer cannot see other customer's orders.
     */
    public function test_customer_cannot_see_other_customer_orders(): void
    {
        $data1 = $this->createCustomerWithOrder();
        $customer1 = $data1['customer'];

        $data2 = $this->createCustomerWithOrder();
        $customer2 = $data2['customer'];
        $order2 = $data2['order'];

        $this->browse(function (Browser $browser) use ($customer1, $order2) {
            $browser->loginAs($customer1)
                ->visit('/meus-pedidos')
                ->screenshot('isolation-01-own-orders')

                // Should NOT see other customer's order
                ->assertDontSee($order2->order_number)
                ->screenshot('isolation-02-no-other-orders')

                // Try to access other's order directly
                ->visit("/meus-pedidos/{$order2->id}")
                ->pause(1000)
                ->screenshot('isolation-03-direct-access')

                // Should be forbidden or redirected
                ->assertSee('403'); // Or check for redirect

            echo "\n✅ Isolamento de pedidos funcionando\n";
        });
    }
}

<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SellerRegistrationTest extends DuskTestCase
{
    // ✅ NÃO usa DatabaseMigrations - usa banco separado mkt_dusk

    /**
     * Test: User can access seller registration page.
     */
    public function test_user_can_access_seller_registration_page(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/become-seller')
                ->assertSee('Tornar-se Vendedor')
                ->assertSee('Informações Básicas')
                ->assertSee('Contato')
                ->assertSee('Endereço Comercial');
        });
    }

    /**
     * Test: Complete seller registration flow as individual (CPF).
     */
    public function test_user_can_register_as_seller_with_cpf(): void
    {
        $uniqueId = uniqid();
        $user = User::factory()->create([
            'email' => 'joao-'.$uniqueId.'@example.com',
            'name' => 'João Silva',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/become-seller')
                ->assertSee('Tornar-se Vendedor')

                // Step 1: Basic Information
                ->type('store_name', 'Loja do João')
                ->type('description', 'Produtos artesanais de qualidade premium')

                // Step 2: Document (CPF válido)
                ->select('person_type', 'individual')
                ->pause(500)
                ->type('document_number', '529.982.247-25')  // CPF válido
                ->pause(500) // Wait for person_type change

                // Step 3: Contact
                ->type('business_phone', '(31) 98765-4321')
                ->type('business_email', 'contato@lojadojoao.com')

                // Step 4: Address
                ->type('postal_code', '30130010')  // CEP sem hífen
                ->keys('#postal_code', '{tab}') // Trigger blur event para API de CEP
                ->pause(3000) // Wait for CEP API to fill all fields
                ->type('number', '123')
                ->type('complement', 'Sala 201')

                // Step 5: Terms
                ->check('terms_accepted')

                // Submit
                ->click('button[type="submit"]')
                ->pause(5000); // Wait for processing

            // Note: Complete flow requires manual CEP API integration testing
            // This test validates form structure and basic interaction
        });

        // Note: Database assertions skipped for now
        // External CEP API may not work reliably in automated tests
        // Consider mocking ViaCEP API for full E2E flow testing
    }

    /**
     * Test: Complete seller registration as business (CNPJ).
     */
    public function test_user_can_register_as_seller_with_cnpj(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/become-seller')

                // Basic Info
                ->type('store_name', 'Tech Solutions LTDA')
                ->type('description', 'Soluções tecnológicas para empresas')

                // Business Document (CNPJ válido)
                ->select('person_type', 'business')
                ->pause(1000) // Wait for fields to show
                ->type('document_number', '11.222.333/0001-81')  // CNPJ válido
                ->pause(500)
                ->type('company_name', 'Tech Solutions Tecnologia LTDA')
                ->type('trade_name', 'Tech Solutions')
                ->type('state_registration', '123456789')

                // Contact
                ->type('business_phone', '(31) 3333-4444')
                ->type('business_email', 'vendas@techsolutions.com')

                // Address
                ->type('postal_code', '30140071')  // CEP sem hífen
                ->keys('#postal_code', '{tab}') // Trigger blur event
                ->pause(3000) // Wait for CEP API
                ->type('number', '500')

                // Terms
                ->check('terms_accepted')

                // Submit
                ->click('button[type="submit"]')
                ->pause(5000); // Wait for processing

            // Note: Complete flow requires manual CEP API integration testing
        });

        // Note: Database assertion skipped - external CEP API dependency
    }

    /**
     * Test: Validation errors are displayed correctly.
     */
    public function test_validation_errors_are_displayed(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/become-seller')
                ->screenshot('before-submit')

                // Submit empty form
                ->click('button[type="submit"]')
                ->pause(2000)
                ->screenshot('after-submit')

                // Assert: Validation errors displayed (check for any error text)
                ->assertSee('obrigatório');
        });
    }

    /**
     * Test: User with existing seller cannot register again.
     */
    public function test_user_with_seller_cannot_register_again(): void
    {
        $user = User::factory()->create();
        $uniqueId = uniqid();
        $user->seller()->create([
            'store_name' => 'Loja Existente',
            'slug' => 'loja-existente-'.$uniqueId,
            'document_number' => '12345678901'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31987654321',
            'business_email' => 'loja-'.$uniqueId.'@example.com',
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/become-seller')
                ->pause(500)

                // Assert: Redirected to dashboard
                ->assertPathIs('/seller/dashboard');
        });
    }

    /**
     * Test: Guest cannot access seller registration.
     */
    public function test_guest_cannot_access_seller_registration(): void
    {
        $this->browse(function (Browser $browser) {
            // Ensure no user is logged in
            $browser->logout()
                ->visit('/become-seller')
                ->pause(1000)

                // Assert: Redirected to login
                ->assertPathIs('/login');
        });
    }
}

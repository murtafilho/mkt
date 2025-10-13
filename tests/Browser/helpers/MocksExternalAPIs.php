<?php

namespace Tests\Browser\Helpers;

use Laravel\Dusk\Browser;

/**
 * Trait for mocking external APIs in Dusk tests
 *
 * Usage in test class:
 * ```php
 * use Tests\Browser\Helpers\MocksExternalAPIs;
 *
 * class MyTest extends DuskTestCase
 * {
 *     use MocksExternalAPIs;
 *
 *     public function test_something()
 *     {
 *         $this->browse(function (Browser $browser) {
 *             $this->mockMercadoPago($browser);
 *             $this->mockViaCEP($browser);
 *
 *             // Now APIs are mocked
 *             $browser->visit('/checkout')...
 *         });
 *     }
 * }
 * ```
 */
trait MocksExternalAPIs
{
    /**
     * Mock Mercado Pago SDK
     */
    protected function mockMercadoPago(Browser $browser): void
    {
        $scriptPath = __DIR__.'/MockMercadoPago.js';

        if (! file_exists($scriptPath)) {
            throw new \RuntimeException("MockMercadoPago.js not found at: {$scriptPath}");
        }

        $script = file_get_contents($scriptPath);
        $browser->script($script);

        echo "\n🎭 Mercado Pago SDK mocked\n";
    }

    /**
     * Mock ViaCEP API
     */
    protected function mockViaCEP(Browser $browser): void
    {
        $scriptPath = __DIR__.'/MockViaCEP.js';

        if (! file_exists($scriptPath)) {
            throw new \RuntimeException("MockViaCEP.js not found at: {$scriptPath}");
        }

        $script = file_get_contents($scriptPath);
        $browser->script($script);

        echo "\n🎭 ViaCEP API mocked\n";
    }

    /**
     * Mock both Mercado Pago and ViaCEP
     */
    protected function mockExternalAPIs(Browser $browser): void
    {
        $this->mockMercadoPago($browser);
        $this->mockViaCEP($browser);
    }
}

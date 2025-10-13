/**
 * Mock Mercado Pago SDK for Dusk Tests
 *
 * Usage in Dusk tests:
 * ```php
 * $browser->script(file_get_contents(__DIR__ . '/helpers/MockMercadoPago.js'));
 * ```
 */

// Mock loadMercadoPago function
window.loadMercadoPago = function() {
    return new Promise((resolve) => {
        console.log('🎭 MOCK: loadMercadoPago called');

        // Create mock MercadoPago class
        window.MercadoPago = class {
            constructor(publicKey, options) {
                console.log('🎭 MOCK: MercadoPago instance created', { publicKey, options });
                this.publicKey = publicKey;
                this.options = options;
            }

            bricks() {
                console.log('🎭 MOCK: bricks() called');
                return {
                    create: async (type, containerId, config) => {
                        console.log('🎭 MOCK: Payment Brick create() called', {
                            type,
                            containerId,
                            amount: config.initialization?.amount
                        });

                        // Render mock Payment Brick
                        const container = document.getElementById(containerId);
                        if (container) {
                            container.innerHTML = `
                                <div id="mock-payment-brick" style="border: 2px dashed #00a650; padding: 20px; background: #f0f0f0;">
                                    <h3>🎭 MOCK Payment Brick</h3>
                                    <p>Amount: R$ ${config.initialization.amount.toFixed(2)}</p>
                                    <p>Payer: ${config.initialization.payer.email}</p>

                                    <div style="margin-top: 15px;">
                                        <label>
                                            <input type="radio" name="payment_method" value="pix" checked>
                                            PIX
                                        </label>
                                        <label style="margin-left: 15px;">
                                            <input type="radio" name="payment_method" value="credit_card">
                                            Cartão de Crédito
                                        </label>
                                    </div>

                                    <button id="mock-submit-payment" style="margin-top: 15px; padding: 10px 20px; background: #00a650; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                        Pagar
                                    </button>
                                </div>
                                <iframe name="payment-mock" style="display: none;"></iframe>
                            `;

                            // Call onReady callback
                            if (config.callbacks?.onReady) {
                                setTimeout(() => {
                                    config.callbacks.onReady();
                                }, 100);
                            }

                            // Setup submit button
                            const submitBtn = document.getElementById('mock-submit-payment');
                            if (submitBtn && config.callbacks?.onSubmit) {
                                submitBtn.addEventListener('click', () => {
                                    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

                                    console.log('🎭 MOCK: Payment submitted', { paymentMethod });

                                    const mockFormData = {
                                        token: 'mock_token_' + Date.now(),
                                        payment_method_id: paymentMethod,
                                        payer: {
                                            email: config.initialization.payer.email
                                        },
                                        transaction_amount: config.initialization.amount
                                    };

                                    config.callbacks.onSubmit({
                                        selectedPaymentMethod: paymentMethod,
                                        formData: mockFormData
                                    });
                                });
                            }
                        }

                        // Return mock controller
                        return {
                            unmount: () => {
                                console.log('🎭 MOCK: Payment Brick unmounted');
                                if (container) {
                                    container.innerHTML = '';
                                }
                            }
                        };
                    }
                };
            }
        };

        console.log('✅ MOCK: MercadoPago SDK loaded successfully');
        resolve();
    });
};

console.log('✅ MockMercadoPago.js loaded');

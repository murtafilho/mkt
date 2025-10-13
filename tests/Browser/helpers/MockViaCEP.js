/**
 * Mock ViaCEP API for Dusk Tests
 *
 * Usage in Dusk tests:
 * ```php
 * $browser->script(file_get_contents(__DIR__ . '/helpers/MockViaCEP.js'));
 * ```
 */

// Mock CEP database
const MOCK_CEPS = {
    '13500110': {
        cep: '13500-110',
        logradouro: 'Rua 1',
        complemento: '',
        bairro: 'Centro',
        localidade: 'Rio Claro',
        uf: 'SP',
        ibge: '3543907',
        gia: '6137',
        ddd: '19',
        siafi: '6867'
    },
    '01310100': {
        cep: '01310-100',
        logradouro: 'Avenida Paulista',
        complemento: '',
        bairro: 'Bela Vista',
        localidade: 'São Paulo',
        uf: 'SP',
        ibge: '3550308',
        gia: '1004',
        ddd: '11',
        siafi: '7107'
    },
    '22250040': {
        cep: '22250-040',
        logradouro: 'Rua Jardim Botânico',
        complemento: '',
        bairro: 'Jardim Botânico',
        localidade: 'Rio de Janeiro',
        uf: 'RJ',
        ibge: '3304557',
        gia: '',
        ddd: '21',
        siafi: '6001'
    },
    '30130100': {
        cep: '30130-100',
        logradouro: 'Avenida Afonso Pena',
        complemento: '',
        bairro: 'Centro',
        localidade: 'Belo Horizonte',
        uf: 'MG',
        ibge: '3106200',
        gia: '',
        ddd: '31',
        siafi: '4123'
    }
};

// Intercept fetch requests to ViaCEP
const originalFetch = window.fetch;
window.fetch = function(url, options) {
    // Check if it's a ViaCEP request
    if (typeof url === 'string' && url.includes('viacep.com.br')) {
        console.log('🎭 MOCK: ViaCEP request intercepted', { url });

        // Extract CEP from URL
        const cepMatch = url.match(/\/ws\/(\d{8})\/json/);
        if (cepMatch) {
            const cep = cepMatch[1];
            const mockData = MOCK_CEPS[cep];

            console.log('🎭 MOCK: CEP lookup', { cep, found: !!mockData });

            return new Promise((resolve) => {
                setTimeout(() => {
                    if (mockData) {
                        resolve({
                            ok: true,
                            status: 200,
                            json: async () => mockData
                        });
                    } else {
                        // CEP not found
                        resolve({
                            ok: true,
                            status: 200,
                            json: async () => ({ erro: true })
                        });
                    }
                }, 200); // Simulate network delay
            });
        }
    }

    // Pass through other requests
    return originalFetch.apply(this, arguments);
};

console.log('✅ MockViaCEP.js loaded - Intercepting viacep.com.br requests');

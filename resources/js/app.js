import './bootstrap';

// Import Bootstrap JavaScript via npm
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import Alpine from 'alpinejs';

// Import Chart.js (for admin dashboard and reports)
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Import Cropper.js (for image upload/crop functionality)
import Cropper from 'cropperjs';
window.Cropper = Cropper;

// Import Mercado Pago SDK
import { loadMercadoPago } from '@mercadopago/sdk-js';
window.loadMercadoPago = loadMercadoPago;

// Import Vanilla Masks (replaces @alpinejs/mask)
import './masks';

// Import Cart (Vanilla JavaScript)
import './cart';

window.Alpine = Alpine;

// Search Autocomplete Component
document.addEventListener('alpine:init', () => {
    Alpine.data('searchAutocomplete', () => ({
        query: '',
        products: [],
        sellers: [],
        showSuggestions: false,
        loading: false,

        async performSearch() {
            const query = this.query.trim();

            // Minimum 2 characters
            if (query.length < 2) {
                this.products = [];
                this.sellers = [];
                this.showSuggestions = false;
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`);
                const data = await response.json();

                this.products = data.products || [];
                this.sellers = data.sellers || [];
                this.showSuggestions = true;
            } catch (error) {
                console.error('Erro na busca:', error);
                this.products = [];
                this.sellers = [];
            } finally {
                this.loading = false;
            }
        }
    }));
});

console.log('🎯 Iniciando Alpine.js...');
Alpine.start();
console.log('✅ Alpine.js iniciado');

/**
 * Header Scroll Effect - Minimalista 2025
 */
window.addEventListener('scroll', () => {
    const header = document.querySelector('header.sticky-top');
    if (header) {
        if (window.scrollY > 10) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
}, { passive: true });
<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Marketplace Configuration
            [
                'key' => 'marketplace_name',
                'value' => 'Marketplace MVP',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Nome do marketplace',
            ],
            [
                'key' => 'marketplace_email',
                'value' => 'contato@marketplace.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Email de contato do marketplace',
            ],
            [
                'key' => 'marketplace_phone',
                'value' => '(11) 99999-9999',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Telefone de contato',
            ],

            // Commission Configuration
            [
                'key' => 'commission_percentage',
                'value' => '10.00',
                'type' => 'decimal',
                'group' => 'financial',
                'description' => 'Percentual de comissão do marketplace (%)',
            ],
            [
                'key' => 'minimum_order_value',
                'value' => '20.00',
                'type' => 'decimal',
                'group' => 'financial',
                'description' => 'Valor mínimo do pedido (R$)',
            ],

            // Shipping Configuration
            [
                'key' => 'free_shipping_threshold',
                'value' => '200.00',
                'type' => 'decimal',
                'group' => 'shipping',
                'description' => 'Valor para frete grátis (R$)',
            ],
            [
                'key' => 'default_shipping_cost',
                'value' => '15.00',
                'type' => 'decimal',
                'group' => 'shipping',
                'description' => 'Custo padrão de frete (R$)',
            ],

            // Mercado Pago Configuration
            [
                'key' => 'mercadopago_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Habilitar Mercado Pago',
            ],
            [
                'key' => 'mercadopago_public_key',
                'value' => config('services.mercadopago.public_key', ''),
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Chave pública do Mercado Pago',
            ],
            [
                'key' => 'mercadopago_access_token',
                'value' => config('services.mercadopago.access_token', ''),
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Access token do Mercado Pago',
            ],
            [
                'key' => 'mercadopago_webhook_secret',
                'value' => config('services.mercadopago.webhook_secret', ''),
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Secret para webhook do Mercado Pago',
            ],

            // Seller Configuration
            [
                'key' => 'seller_auto_approval',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'seller',
                'description' => 'Aprovar vendedores automaticamente',
            ],
            [
                'key' => 'seller_minimum_products',
                'value' => '1',
                'type' => 'integer',
                'group' => 'seller',
                'description' => 'Número mínimo de produtos para publicar',
            ],

            // Product Configuration
            [
                'key' => 'products_per_page',
                'value' => '24',
                'type' => 'integer',
                'group' => 'product',
                'description' => 'Produtos por página',
            ],
            [
                'key' => 'featured_products_limit',
                'value' => '8',
                'type' => 'integer',
                'group' => 'product',
                'description' => 'Limite de produtos em destaque',
            ],
            [
                'key' => 'related_products_limit',
                'value' => '4',
                'type' => 'integer',
                'group' => 'product',
                'description' => 'Limite de produtos relacionados',
            ],

            // Email Configuration
            [
                'key' => 'send_order_confirmation',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Enviar email de confirmação de pedido',
            ],
            [
                'key' => 'send_shipping_notification',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Enviar email de envio de pedido',
            ],
            [
                'key' => 'send_seller_approval',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Enviar email de aprovação de vendedor',
            ],

            // SEO Configuration
            [
                'key' => 'site_title',
                'value' => 'Marketplace MVP - Compre e Venda Online',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Título do site',
            ],
            [
                'key' => 'site_description',
                'value' => 'Marketplace online com diversos produtos de vendedores confiáveis',
                'type' => 'text',
                'group' => 'seo',
                'description' => 'Descrição do site',
            ],
            [
                'key' => 'site_keywords',
                'value' => 'marketplace, ecommerce, comprar, vender',
                'type' => 'text',
                'group' => 'seo',
                'description' => 'Palavras-chave do site',
            ],

            // Maintenance
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Modo de manutenção',
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'Estamos em manutenção. Voltaremos em breve!',
                'type' => 'text',
                'group' => 'system',
                'description' => 'Mensagem de manutenção',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}

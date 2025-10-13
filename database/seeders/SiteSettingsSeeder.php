<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Site Info
            ['key' => 'site_name', 'value' => config('app.name'), 'type' => 'string', 'group' => 'site', 'description' => 'Nome do site'],
            ['key' => 'site_tagline', 'value' => 'Seu marketplace multivendor', 'type' => 'string', 'group' => 'site', 'description' => 'Slogan do site'],
            ['key' => 'site_description', 'value' => 'Encontre milhares de produtos de vendedores confiáveis', 'type' => 'string', 'group' => 'site', 'description' => 'Descrição para SEO'],

            // Branding - Logo
            ['key' => 'logo_svg', 'value' => '', 'type' => 'string', 'group' => 'branding', 'description' => 'Código SVG da logo'],
            ['key' => 'logo_width', 'value' => '180', 'type' => 'integer', 'group' => 'branding', 'description' => 'Largura da logo em pixels'],
            ['key' => 'logo_height', 'value' => '60', 'type' => 'integer', 'group' => 'branding', 'description' => 'Altura da logo em pixels'],

            // Hero
            ['key' => 'hero_type', 'value' => 'gradient', 'type' => 'string', 'group' => 'hero', 'description' => 'Tipo de hero: gradient ou image'],
            ['key' => 'hero_image', 'value' => '', 'type' => 'string', 'group' => 'hero', 'description' => 'Caminho da imagem do hero'],
            ['key' => 'hero_title', 'value' => 'Bem-vindo ao Marketplace', 'type' => 'string', 'group' => 'hero', 'description' => 'Título do hero'],
            ['key' => 'hero_subtitle', 'value' => 'Milhares de produtos incríveis esperando por você', 'type' => 'string', 'group' => 'hero', 'description' => 'Subtítulo do hero'],

            // Colors (Tailwind CSS 4.0 - OKLCH)
            ['key' => 'color_primary', 'value' => 'oklch(0.58 0.18 145)', 'type' => 'string', 'group' => 'colors', 'description' => 'Cor primária (OKLCH)'],
            ['key' => 'color_secondary', 'value' => 'oklch(0.68 0.15 250)', 'type' => 'string', 'group' => 'colors', 'description' => 'Cor secundária (OKLCH)'],
            ['key' => 'color_accent', 'value' => 'oklch(0.75 0.20 85)', 'type' => 'string', 'group' => 'colors', 'description' => 'Cor de destaque (OKLCH)'],

            // Home Sections Order
            ['key' => 'home_sections_order', 'value' => '["featured","categories","latest","deals"]', 'type' => 'json', 'group' => 'sections', 'description' => 'Ordem das seções na homepage'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✅ Site settings seeded successfully!');
    }
}

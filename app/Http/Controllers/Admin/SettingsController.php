<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {}

    /**
     * Show site settings form.
     */
    public function index()
    {
        $this->authorize('viewAny', Setting::class);

        // Get all site settings organized by group
        $settings = [
            'site' => [
                'site_name' => $this->settingsService->get('site_name', config('app.name')),
                'site_tagline' => $this->settingsService->get('site_tagline', 'Seu marketplace multivendor'),
                'site_description' => $this->settingsService->get('site_description', ''),
            ],
            'branding' => [
                'logo_svg' => $this->settingsService->get('logo_svg', ''),
                'logo_width' => $this->settingsService->get('logo_width', '180'),
                'logo_height' => $this->settingsService->get('logo_height', '60'),
            ],
            'hero' => [
                'hero_type' => $this->settingsService->get('hero_type', 'gradient'), // gradient, image
                'hero_image' => $this->settingsService->get('hero_image', ''),
                'hero_title' => $this->settingsService->get('hero_title', 'Bem-vindo ao Marketplace'),
                'hero_subtitle' => $this->settingsService->get('hero_subtitle', 'Milhares de produtos incríveis'),
            ],
            'colors' => [
                'color_primary' => $this->settingsService->get('color_primary', 'oklch(0.58 0.18 145)'),
                'color_secondary' => $this->settingsService->get('color_secondary', 'oklch(0.68 0.15 250)'),
                'color_accent' => $this->settingsService->get('color_accent', 'oklch(0.75 0.20 85)'),
            ],
            'sections' => [
                'home_sections_order' => $this->settingsService->get(
                    'home_sections_order',
                    ['featured', 'categories', 'latest', 'deals']
                ),
            ],
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update site settings.
     */
    public function update(Request $request)
    {
        $this->authorize('update', Setting::class);

        $validated = $request->validate([
            // Site info
            'site_name' => 'nullable|string|max:100',
            'site_tagline' => 'nullable|string|max:200',
            'site_description' => 'nullable|string|max:500',

            // Logo SVG
            'logo_svg' => 'nullable|string',
            'logo_width' => 'nullable|integer|min:50|max:500',
            'logo_height' => 'nullable|integer|min:20|max:200',

            // Hero
            'hero_type' => 'required|in:gradient,image',
            'hero_image' => 'nullable|image|max:2048', // 2MB
            'hero_title' => 'nullable|string|max:200',
            'hero_subtitle' => 'nullable|string|max:300',

            // Colors (OKLCH format for Tailwind CSS 4.0)
            'color_primary' => 'nullable|string|max:50',
            'color_secondary' => 'nullable|string|max:50',
            'color_accent' => 'nullable|string|max:50',

            // Sections order (JSON array)
            'home_sections_order' => 'nullable|json',
        ]);

        try {
            // Save basic settings
            foreach (['site_name', 'site_tagline', 'site_description'] as $key) {
                if (isset($validated[$key])) {
                    $this->settingsService->set($key, $validated[$key], 'string', 'site');
                }
            }

            // Save logo SVG
            if (isset($validated['logo_svg'])) {
                $this->settingsService->set('logo_svg', $validated['logo_svg'], 'string', 'branding');
            }

            foreach (['logo_width', 'logo_height'] as $key) {
                if (isset($validated[$key])) {
                    $this->settingsService->set($key, $validated[$key], 'integer', 'branding');
                }
            }

            // Handle hero image upload
            if ($request->hasFile('hero_image')) {
                $path = $request->file('hero_image')->store('hero', 'public');
                $this->settingsService->set('hero_image', $path, 'string', 'hero');
            }

            // Save hero settings
            foreach (['hero_type', 'hero_title', 'hero_subtitle'] as $key) {
                if (isset($validated[$key])) {
                    $this->settingsService->set($key, $validated[$key], 'string', 'hero');
                }
            }

            // Save colors
            foreach (['color_primary', 'color_secondary', 'color_accent'] as $key) {
                if (isset($validated[$key])) {
                    $this->settingsService->set($key, $validated[$key], 'string', 'colors');
                }
            }

            // Save sections order
            if (isset($validated['home_sections_order'])) {
                $this->settingsService->set('home_sections_order', $validated['home_sections_order'], 'json', 'sections');
            }

            return back()->with('success', 'Configurações atualizadas com sucesso!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao salvar configurações: '.$e->getMessage());
        }
    }

    /**
     * Delete hero image.
     */
    public function deleteHeroImage()
    {
        $this->authorize('update', Setting::class);

        try {
            $heroImage = $this->settingsService->get('hero_image');

            if ($heroImage && Storage::disk('public')->exists($heroImage)) {
                Storage::disk('public')->delete($heroImage);
            }

            $this->settingsService->set('hero_image', '', 'string', 'hero');

            return response()->json([
                'success' => true,
                'message' => 'Imagem do hero removida com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover imagem.',
            ], 500);
        }
    }
}

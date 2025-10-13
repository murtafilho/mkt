<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConvertLayoutsCommand extends Command
{
    protected $signature = 'layouts:convert';
    protected $description = 'Convert x-layouts to @extends syntax';

    public function handle()
    {
        $this->info('🔄 Converting x-layouts to @extends...');

        $files = [
            // Admin pages
            'resources/views/admin/categories/index.blade.php' => 'admin',
            'resources/views/admin/categories/create.blade.php' => 'admin',
            'resources/views/admin/categories/edit.blade.php' => 'admin',
            'resources/views/admin/sellers/index.blade.php' => 'admin',
            'resources/views/admin/sellers/show.blade.php' => 'admin',
            'resources/views/admin/orders/index.blade.php' => 'admin',
            'resources/views/admin/orders/show.blade.php' => 'admin',
            'resources/views/admin/settings/index.blade.php' => 'admin',
            'resources/views/admin/reports/index.blade.php' => 'admin',
            'resources/views/admin/reports/sales.blade.php' => 'admin',
            'resources/views/admin/reports/sellers.blade.php' => 'admin',
            'resources/views/admin/reports/products.blade.php' => 'admin',
            
            // Seller pages
            'resources/views/seller/dashboard.blade.php' => 'seller',
            'resources/views/seller/products/index.blade.php' => 'seller',
            'resources/views/seller/products/create.blade.php' => 'seller',
            'resources/views/seller/products/edit.blade.php' => 'seller',
            'resources/views/seller/orders/index.blade.php' => 'seller',
            'resources/views/seller/orders/show.blade.php' => 'seller',
            'resources/views/seller/profile/edit.blade.php' => 'seller',
            
            // App pages
            'resources/views/seller/register.blade.php' => 'app',
            'resources/views/profile/edit.blade.php' => 'app',
            
            // Public pages
            'resources/views/checkout/index.blade.php' => 'public',
            'resources/views/checkout/success.blade.php' => 'public',
            'resources/views/checkout/pending.blade.php' => 'public',
            'resources/views/checkout/failure.blade.php' => 'public',
            'resources/views/sellers/show.blade.php' => 'public',
            'resources/views/customer/my-orders/index.blade.php' => 'public',
            'resources/views/customer/my-orders/show.blade.php' => 'public',
        ];

        $converted = 0;
        $skipped = 0;

        foreach ($files as $file => $layout) {
            if (!File::exists($file)) {
                $this->warn("  ✗ Not found: $file");
                $skipped++;
                continue;
            }

            $content = File::get($file);
            $original = $content;

            // Pattern 1: <x-layouts.XXX> with simple title
            $content = preg_replace(
                '/<x-layouts\.' . $layout . '>\s*<x-slot:title>([^<]+)<\/x-slot>/s',
                "@extends('layouts.$layout')\n\n@section('title', '$1')\n\n@section('page-content')",
                $content
            );

            // Pattern 2: <x-layouts.XXX> with title attribute
            $content = preg_replace(
                '/<x-layouts\.' . $layout . '\s+title="([^"]+)"\s*>/s',
                "@extends('layouts.$layout')\n\n@section('title', '$1')\n\n@section('page-content')",
                $content
            );

            // Pattern 3: <x-layouts.XXX> with :title="..."
            $content = preg_replace(
                '/<x-layouts\.' . $layout . '\s+:title="([^"]+)"\s*>/s',
                "@extends('layouts.$layout')\n\n@section('title', $1)\n\n@section('page-content')",
                $content
            );

            // Pattern 4: <x-layouts.XXX> with both header and title slots
            $content = preg_replace(
                '/<x-layouts\.' . $layout . '>\s*<x-slot:header>([^<]+)<\/x-slot>\s*<x-slot:title>([^<]+)<\/x-slot>/s',
                "@extends('layouts.$layout')\n\n@section('title', '$2')\n\n@section('header', '$1')\n\n@section('page-content')",
                $content
            );

            // Pattern 5: Just header slot
            $content = preg_replace(
                '/<x-slot:header>([^<]+)<\/x-slot>/s',
                "@section('header', '$1')",
                $content
            );

            // Pattern 6: Complex title section
            $content = preg_replace(
                '/<x-slot:title>\s*@if/s',
                "@section('title')\n    @if",
                $content
            );

            $content = preg_replace(
                '/@endif\s*<\/x-slot>\s*(?=<x-slot|{{--|@|<section)/s',
                "@endif\n@endsection\n",
                $content
            );

            // Convert closing tag
            $content = preg_replace(
                '/<\/x-layouts\.' . $layout . '>/s',
                '@endsection',
                $content
            );

            // Only save if changed
            if ($content !== $original) {
                File::put($file, $content);
                $this->info("  ✓ Converted: $file");
                $converted++;
            } else {
                $this->comment("  - Already converted: $file");
            }
        }

        $this->newLine();
        $this->info("✅ Conversion complete!");
        $this->info("   Converted: $converted files");
        $this->info("   Skipped: $skipped files");

        return 0;
    }
}

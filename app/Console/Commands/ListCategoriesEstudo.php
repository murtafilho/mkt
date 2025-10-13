<?php

namespace App\Console\Commands;

use App\Models\CategoryEstudo;
use Illuminate\Console\Command;

class ListCategoriesEstudo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:list-estudo 
                            {--format=table : Output format (table, tree, json)}
                            {--parent= : Show only categories with specific parent ID}
                            {--active : Show only active categories}
                            {--source= : Filter by source (default: valedosol_db)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List imported categories from categories_estudo table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $format = $this->option('format');
        $parentId = $this->option('parent');
        $activeOnly = $this->option('active');
        $source = $this->option('source') ?: 'valedosol_db';

        $query = CategoryEstudo::query();

        // Apply filters
        $query->where('source', $source);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        if ($parentId) {
            $query->where('parent_id', $parentId);
        }

        $categories = $query->with('parent')->orderBy('parent_id')->orderBy('order')->get();

        $this->info('📋 Categorias encontradas: '.$categories->count());

        switch ($format) {
            case 'tree':
                $this->displayTree($categories);
                break;
            case 'json':
                $this->displayJson($categories);
                break;
            case 'table':
            default:
                $this->displayTable($categories);
                break;
        }

        return 0;
    }

    /**
     * Display categories in table format
     */
    private function displayTable($categories): void
    {
        $headers = ['ID', 'Nome', 'Slug', 'Parent', 'Ordem', 'Ativo', 'Origem', 'Caminho'];

        $rows = $categories->map(function ($category) {
            return [
                $category->id,
                $category->name,
                $category->slug,
                $category->parent ? $category->parent->name : '-',
                $category->order,
                $category->is_active ? 'Sim' : 'Não',
                $category->source,
                $category->full_path,
            ];
        })->toArray();

        $this->table($headers, $rows);
    }

    /**
     * Display categories in tree format
     */
    private function displayTree($categories): void
    {
        $rootCategories = $categories->whereNull('parent_id')->sortBy('order');

        foreach ($rootCategories as $root) {
            $this->displayCategoryNode($root, $categories, 0);
        }
    }

    /**
     * Display a category node and its children recursively
     */
    private function displayCategoryNode($category, $allCategories, int $depth): void
    {
        $prefix = str_repeat('  ', $depth);
        $status = $category->is_active ? '✅' : '❌';
        $this->line("{$prefix}{$status} {$category->name} (ID: {$category->id})");

        if ($category->description) {
            $this->line("{$prefix}   📝 ".substr($category->description, 0, 60).'...');
        }

        // Find and display children
        $children = $allCategories->where('parent_id', $category->id)->sortBy('order');
        foreach ($children as $child) {
            $this->displayCategoryNode($child, $allCategories, $depth + 1);
        }
    }

    /**
     * Display categories in JSON format
     */
    private function displayJson($categories): void
    {
        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'original_id' => $category->original_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'order' => $category->order,
                'is_active' => $category->is_active,
                'source' => $category->source,
                'parent_id' => $category->parent_id,
                'parent_name' => $category->parent?->name,
                'depth' => $category->depth,
                'full_path' => $category->full_path,
                'children_count' => $category->children()->count(),
            ];
        });

        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

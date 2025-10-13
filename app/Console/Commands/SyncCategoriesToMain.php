<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\CategoryEstudo;
use Illuminate\Console\Command;

class SyncCategoriesToMain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:sync-to-main 
                            {--source=valedosol_db : Source to sync from categories_estudo}
                            {--clear : Clear existing categories before sync}
                            {--dry-run : Show what would be synced without actually syncing}
                            {--update : Update existing categories instead of skipping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync categories from categories_estudo to main categories table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $source = $this->option('source');
        $clear = $this->option('clear');
        $dryRun = $this->option('dry-run');
        $update = $this->option('update');

        $this->info('🔄 Iniciando sincronização de categorias...');
        $this->info("📂 Fonte: {$source}");

        try {
            // Buscar categorias da tabela auxiliar
            $categoriesToSync = CategoryEstudo::fromSource($source)->orderBy('parent_id')->orderBy('order')->get();

            $this->info('📦 Encontradas '.$categoriesToSync->count().' categorias para sincronizar');

            if ($categoriesToSync->isEmpty()) {
                $this->warn('⚠️ Nenhuma categoria encontrada para sincronizar');

                return 0;
            }

            if ($dryRun) {
                $this->info('🔍 MODO DRY-RUN - Nenhum dado será sincronizado');
                $this->displaySyncPreview($categoriesToSync);

                return 0;
            }

            // Limpar categorias existentes se solicitado
            if ($clear) {
                if ($this->confirm('⚠️ Tem certeza que deseja limpar todas as categorias existentes?')) {
                    Category::truncate();
                    $this->info('🗑️ Categorias existentes removidas');
                } else {
                    $this->info('✅ Sincronização cancelada');

                    return 0;
                }
            }

            // Sincronizar categorias
            $result = $this->syncCategories($categoriesToSync, $update);

            $this->info('✅ Sincronização concluída!');
            $this->displayResults($result);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erro durante a sincronização: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Exibir preview da sincronização (modo dry-run)
     */
    private function displaySyncPreview($categories): void
    {
        $this->info("\n📋 Preview da sincronização:");

        $rootCategories = $categories->whereNull('parent_id');
        $this->info('🏠 Categorias principais: '.$rootCategories->count());

        $subCategories = $categories->whereNotNull('parent_id');
        $this->info('📁 Subcategorias: '.$subCategories->count());

        $this->table(
            ['ID Orig.', 'Nome', 'Slug', 'Parent', 'Status'],
            $categories->take(10)->map(function ($cat) {
                $existing = Category::where('name', $cat->name)->first();

                return [
                    $cat->original_id,
                    $cat->name,
                    $cat->slug,
                    $cat->parent ? $cat->parent->name : '-',
                    $existing ? '🔄 Existente' : '➕ Nova',
                ];
            })
        );

        if ($categories->count() > 10) {
            $this->info('... e mais '.($categories->count() - 10).' categorias');
        }
    }

    /**
     * Sincronizar categorias
     */
    private function syncCategories($categories, bool $update): array
    {
        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        // Primeiro, sincronizar categorias pai
        $parentCategories = $categories->whereNull('parent_id');

        foreach ($parentCategories as $categoryEstudo) {
            try {
                $this->syncSingleCategory($categoryEstudo, $update, $result);
            } catch (\Exception $e) {
                $this->error("\n❌ Erro ao sincronizar categoria {$categoryEstudo->id}: ".$e->getMessage());
                $result['errors']++;
            }
            $bar->advance();
        }

        // Depois, sincronizar subcategorias
        $subCategories = $categories->whereNotNull('parent_id');

        foreach ($subCategories as $categoryEstudo) {
            try {
                // Verificar se a categoria pai foi sincronizada
                $parentCategory = Category::where('name', $categoryEstudo->parent->name)->first();

                if (! $parentCategory) {
                    $this->warn("\n⚠️ Categoria pai '{$categoryEstudo->parent->name}' não encontrada para '{$categoryEstudo->name}'");
                    $result['skipped']++;
                    $bar->advance();

                    continue;
                }

                $this->syncSingleCategory($categoryEstudo, $update, $result, $parentCategory->id);
            } catch (\Exception $e) {
                $this->error("\n❌ Erro ao sincronizar categoria {$categoryEstudo->id}: ".$e->getMessage());
                $result['errors']++;
            }
            $bar->advance();
        }

        $bar->finish();

        return $result;
    }

    /**
     * Sincronizar uma única categoria
     */
    private function syncSingleCategory($categoryEstudo, bool $update, array &$result, ?int $parentId = null): void
    {
        // Buscar categoria existente
        $existingCategory = Category::where('name', $categoryEstudo->name)->first();

        if ($existingCategory && ! $update) {
            $result['skipped']++;

            return;
        }

        // Gerar slug único
        $slug = $this->generateUniqueSlug($categoryEstudo->slug, $existingCategory);

        $categoryData = [
            'parent_id' => $parentId,
            'name' => $categoryEstudo->name,
            'slug' => $slug,
            'description' => $categoryEstudo->description,
            'order' => $categoryEstudo->order,
            'is_active' => $categoryEstudo->is_active,
        ];

        if ($existingCategory) {
            $existingCategory->update($categoryData);
            $result['updated']++;
        } else {
            Category::create($categoryData);
            $result['created']++;
        }
    }

    /**
     * Gerar slug único
     */
    private function generateUniqueSlug(string $originalSlug, ?Category $existingCategory = null): string
    {
        $slug = $originalSlug;

        // Se está atualizando a mesma categoria, manter o slug
        if ($existingCategory && $existingCategory->slug === $slug) {
            return $slug;
        }

        // Verificar se slug já existe
        $counter = 1;
        $originalSlug = $slug;

        while (Category::where('slug', $slug)->where('id', '!=', $existingCategory?->id)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Exibir resultados da sincronização
     */
    private function displayResults(array $result): void
    {
        $this->newLine();
        $this->info('📊 Resumo da sincronização:');
        $this->line("  • Criadas: {$result['created']}");
        $this->line("  • Atualizadas: {$result['updated']}");
        $this->line("  • Ignoradas: {$result['skipped']}");
        $this->line("  • Erros: {$result['errors']}");

        $totalCategories = Category::count();
        $this->info("\n📈 Total de categorias no sistema: {$totalCategories}");
    }
}

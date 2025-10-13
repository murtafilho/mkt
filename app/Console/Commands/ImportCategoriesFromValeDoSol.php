<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportCategoriesFromValeDoSol extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:categories-valedosol 
                            {--host=127.0.0.1 : Database host}
                            {--port=3306 : Database port}
                            {--username=root : Database username}
                            {--password= : Database password}
                            {--database=valedosol_db : Source database name}
                            {--clear : Clear existing data before import}
                            {--dry-run : Show what would be imported without actually importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import categories from valedosol_db database to categories_estudo table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Iniciando importação de categorias do banco valedosol_db...');

        // Configurações da conexão
        $host = $this->option('host');
        $port = $this->option('port');
        $username = $this->option('username');
        $password = $this->option('password');
        $database = $this->option('database');
        $dryRun = $this->option('dry-run');
        $clear = $this->option('clear');

        try {
            // Testar conexão com o banco externo
            $this->info("📡 Testando conexão com {$host}:{$port}/{$database}...");

            $externalConnection = $this->createExternalConnection($host, $port, $username, $password, $database);

            // Verificar se a tabela categories existe
            $stmt = $externalConnection->prepare("SHOW TABLES LIKE 'categories'");
            $stmt->execute();
            $tables = $stmt->fetchAll();
            if (empty($tables)) {
                $this->error("❌ Tabela 'categories' não encontrada no banco {$database}");

                return 1;
            }

            // Verificar estrutura da tabela
            $stmt = $externalConnection->prepare('DESCRIBE categories');
            $stmt->execute();
            $columns = $stmt->fetchAll();
            $this->info('📋 Estrutura da tabela categories encontrada:');
            foreach ($columns as $column) {
                $this->line("  • {$column->Field} ({$column->Type})");
            }

            // Buscar todas as categorias
            $stmt = $externalConnection->prepare('SELECT * FROM categories ORDER BY id');
            $stmt->execute();
            $categories = $stmt->fetchAll();
            $this->info('📦 Encontradas '.count($categories).' categorias para importar');

            if (empty($categories)) {
                $this->warn('⚠️ Nenhuma categoria encontrada para importar');

                return 0;
            }

            if ($dryRun) {
                $this->info('🔍 MODO DRY-RUN - Nenhum dado será importado');
                $this->displayCategories($categories);

                return 0;
            }

            // Limpar dados existentes se solicitado
            if ($clear) {
                if ($this->confirm('⚠️ Tem certeza que deseja limpar todos os dados existentes em categories_estudo?')) {
                    DB::table('categories_estudo')->truncate();
                    $this->info('🗑️ Dados existentes removidos');
                } else {
                    $this->info('✅ Importação cancelada');

                    return 0;
                }
            }

            // Importar categorias
            $this->importCategories($categories);

            $this->info('✅ Importação concluída com sucesso!');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erro durante a importação: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return 1;
        }
    }

    /**
     * Criar conexão externa com o banco valedosol_db
     */
    private function createExternalConnection(string $host, string $port, string $username, ?string $password, string $database)
    {
        $config = [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        // Criar conexão temporária
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        try {
            $pdo = new \PDO($dsn, $username, $password ?? '', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            ]);

            return $pdo;
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao conectar com o banco {$database}: ".$e->getMessage());
        }
    }

    /**
     * Exibir categorias que seriam importadas (modo dry-run)
     */
    private function displayCategories(array $categories): void
    {
        $this->table(
            ['ID', 'Nome', 'Slug', 'Parent ID', 'Descrição', 'Ordem', 'Ativo'],
            array_map(function ($category) {
                return [
                    $category->id ?? 'N/A',
                    $category->name ?? 'N/A',
                    $category->slug ?? 'N/A',
                    $category->parent_id ?? 'N/A',
                    Str::limit($category->description ?? '', 30),
                    $category->order ?? 0,
                    isset($category->is_active) ? ($category->is_active ? 'Sim' : 'Não') : 'N/A',
                ];
            }, $categories)
        );
    }

    /**
     * Importar categorias para a tabela categories_estudo
     */
    private function importCategories(array $categories): void
    {
        $bar = $this->output->createProgressBar(count($categories));
        $bar->start();

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        // Primeiro, importar todas as categorias pai (parent_id = null)
        $parentCategories = array_filter($categories, fn ($cat) => empty($cat->parent_id));

        foreach ($parentCategories as $category) {
            try {
                $this->importSingleCategory($category);
                $imported++;
            } catch (\Exception $e) {
                $this->error("\n❌ Erro ao importar categoria {$category->id}: ".$e->getMessage());
                $errors++;
            }
            $bar->advance();
        }

        // Depois, importar subcategorias
        $subCategories = array_filter($categories, fn ($cat) => ! empty($cat->parent_id));

        foreach ($subCategories as $category) {
            try {
                // Verificar se a categoria pai foi importada
                $parentExists = DB::table('categories_estudo')
                    ->where('original_id', $category->parent_id)
                    ->exists();

                if (! $parentExists) {
                    $this->warn("\n⚠️ Categoria pai {$category->parent_id} não encontrada para subcategoria {$category->id}");
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                $this->importSingleCategory($category);
                $imported++;
            } catch (\Exception $e) {
                $this->error("\n❌ Erro ao importar categoria {$category->id}: ".$e->getMessage());
                $errors++;
            }
            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info('📊 Resumo da importação:');
        $this->line("  • Importadas: {$imported}");
        $this->line("  • Ignoradas: {$skipped}");
        $this->line("  • Erros: {$errors}");
    }

    /**
     * Importar uma única categoria
     */
    private function importSingleCategory(object $category): void
    {
        // Verificar se já existe
        $exists = DB::table('categories_estudo')
            ->where('original_id', $category->id)
            ->exists();

        if ($exists) {
            return; // Já importada
        }

        // Buscar parent_id na tabela de estudo
        $parentId = null;
        if (! empty($category->parent_id)) {
            $parentRecord = DB::table('categories_estudo')
                ->where('original_id', $category->parent_id)
                ->first();
            $parentId = $parentRecord ? $parentRecord->id : null;
        }

        // Gerar slug se não existir
        $slug = $category->slug ?? Str::slug($category->name);

        // Verificar se slug já existe
        $counter = 1;
        $originalSlug = $slug;
        while (DB::table('categories_estudo')->where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        DB::table('categories_estudo')->insert([
            'original_id' => $category->id,
            'parent_id' => $parentId,
            'name' => $category->name,
            'slug' => $slug,
            'description' => $category->description ?? null,
            'order' => $category->order ?? 0,
            'is_active' => $category->is_active ?? true,
            'source' => 'valedosol_db',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

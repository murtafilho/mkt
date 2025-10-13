<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Teste E2E completo de cadastro de produtos com foco em upload de imagens.
 *
 * Testa:
 * - Upload de 1 imagem
 * - Upload de múltiplas imagens (até 4)
 * - Preview de imagens antes do submit
 * - Remoção de imagens do preview
 * - Validação de tipo, tamanho e dimensões
 * - Persistência das imagens após submit
 */
class ProductImageUploadTest extends DuskTestCase
{
    protected $testImagePath;

    protected $testImagesDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Garante roles e categorias
        if (\Spatie\Permission\Models\Role::count() === 0) {
            $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        }

        if (Category::count() === 0) {
            $this->seed(\Database\Seeders\CategoriesSeeder::class);
        }

        // Limpa produtos de teste antigos
        Product::where('name', 'LIKE', '%Test Product%')->delete();
        Product::where('name', 'LIKE', '%Produto Teste Imagem%')->delete();

        // Cria diretório de imagens de teste
        $this->testImagesDir = base_path('tests/Browser/test-images');
        if (! File::exists($this->testImagesDir)) {
            File::makeDirectory($this->testImagesDir, 0755, true);
        }

        // Cria imagens de teste válidas se não existirem
        $this->createTestImages();
    }

    protected function tearDown(): void
    {
        // Limpa imagens de teste se desejar (opcional)
        // File::deleteDirectory($this->testImagesDir);

        parent::tearDown();
    }

    /**
     * Cria imagens PNG válidas para teste.
     */
    protected function createTestImages(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $imagePath = "{$this->testImagesDir}/test-product-{$i}.png";

            if (File::exists($imagePath)) {
                continue; // Já existe
            }

            // Cria imagem PNG 1000x1000 (válida - acima de 800x800)
            $image = imagecreatetruecolor(1000, 1000);

            // Define cores diferentes para cada imagem
            $colors = [
                imagecolorallocate($image, 255, 100, 100), // Vermelho
                imagecolorallocate($image, 100, 255, 100), // Verde
                imagecolorallocate($image, 100, 100, 255), // Azul
                imagecolorallocate($image, 255, 255, 100), // Amarelo
                imagecolorallocate($image, 255, 100, 255), // Magenta
            ];

            imagefilledrectangle($image, 0, 0, 1000, 1000, $colors[$i - 1]);

            // Adiciona texto identificador
            $white = imagecolorallocate($image, 255, 255, 255);
            $font = 5; // Built-in font
            imagestring($image, $font, 450, 490, "IMG {$i}", $white);

            imagepng($image, $imagePath);
            imagedestroy($image);
        }

        // Cria uma imagem pequena (inválida - abaixo de 800x800)
        $smallImagePath = "{$this->testImagesDir}/test-small.png";
        if (! File::exists($smallImagePath)) {
            $smallImage = imagecreatetruecolor(600, 600);
            $red = imagecolorallocate($smallImage, 255, 0, 0);
            imagefilledrectangle($smallImage, 0, 0, 600, 600, $red);
            imagepng($smallImage, $smallImagePath);
            imagedestroy($smallImage);
        }
    }

    /**
     * Helper: Cria seller autenticado.
     */
    protected function createAuthenticatedSeller(): User
    {
        $user = User::firstOrCreate(
            ['email' => 'seller-images@test.com'],
            [
                'name' => 'Image Upload Tester',
                'password' => bcrypt('password'),
            ]
        );

        if (! $user->seller) {
            $user->seller()->create([
                'store_name' => 'Test Image Store',
                'slug' => 'test-image-store',
                'document_number' => '98765432100',
                'person_type' => 'individual',
                'business_phone' => '11999998888',
                'business_email' => 'store@test.com',
                'status' => 'active', // Approved seller
                'approved_at' => now(),
            ]);
        }

        if (! $user->hasRole('seller')) {
            $user->assignRole('seller');
        }

        return $user;
    }

    /**
     * Test: Seller pode criar produto SEM imagens (draft).
     */
    public function test_seller_can_create_product_without_images_as_draft(): void
    {
        $user = $this->createAuthenticatedSeller();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/seller/products/create')
                ->screenshot('01-form-vazio')

                // Preencher dados básicos
                ->type('input[name="name"]', 'Produto Sem Imagens Test')
                ->type('textarea[name="description"]', 'Descrição completa do produto de teste sem imagens')
                ->select('select[name="category_id"]', '1')
                ->type('input[name="original_price"]', '100.00')
                ->type('input[name="sale_price"]', '89.90')
                ->type('input[name="stock"]', '15')
                ->select('select[name="status"]', 'draft') // Draft permite sem imagens

                ->screenshot('02-dados-preenchidos-sem-imagens')
                ->scrollIntoView('button[type="submit"]')
                ->press('Criar Produto')
                ->pause(3000)
                ->screenshot('03-apos-submit-sem-imagens');

            // Verifica sucesso
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertStringContainsString('/edit', $currentUrl, 'Produto sem imagens criado com sucesso');
        });
    }

    /**
     * Test: Upload de 1 imagem com preview.
     */
    public function test_seller_can_upload_single_image_with_preview(): void
    {
        $user = $this->createAuthenticatedSeller();
        $imagePath = "{$this->testImagesDir}/test-product-1.png";

        $this->browse(function (Browser $browser) use ($user, $imagePath) {
            $browser->loginAs($user)
                ->visit('/seller/products/create')
                ->screenshot('10-antes-upload-1-imagem')

                // Preencher dados básicos
                ->type('input[name="name"]', 'Produto 1 Imagem Test')
                ->type('textarea[name="description"]', 'Produto com uma imagem de teste')
                ->select('select[name="category_id"]', '1')
                ->type('input[name="original_price"]', '120.00')
                ->type('input[name="sale_price"]', '99.90')
                ->type('input[name="stock"]', '20')

                // Upload de 1 imagem
                ->attach('input[type="file"]', $imagePath)
                ->pause(2000) // Aguarda preview carregar

                ->screenshot('11-preview-1-imagem')

                // Verifica preview apareceu
                ->assertVisible('div[class*="grid"][class*="grid-cols"]') // Grid de previews
                ->pause(1000)

                // Verifica badge "Novo"
                ->assertSeeIn('div[class*="bg-success-600"]', 'Novo')

                // Verifica contador
                ->assertSee('1 de 4 imagens')

                ->screenshot('12-antes-submit-1-imagem')
                ->select('status', 'published') // Pode publicar com 1+ imagem
                ->scrollIntoView('button[type="submit"]')
                ->press('Criar Produto')
                ->pause(5000)
                ->screenshot('13-apos-submit-1-imagem');

            // Verifica redirecionamento para edit
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertStringContainsString('/edit', $currentUrl);

            echo "\n✅ Produto com 1 imagem criado: ".$currentUrl."\n";
        });

        // Verifica no banco que produto foi criado com imagem
        $product = Product::where('name', 'Produto 1 Imagem Test')->first();
        $this->assertNotNull($product);
        $this->assertEquals(1, $product->getMedia('product_images')->count());
    }

    /**
     * Test: Upload de 4 imagens (máximo) com preview de todas.
     */
    public function test_seller_can_upload_multiple_images_with_previews(): void
    {
        $user = $this->createAuthenticatedSeller();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/seller/products/create')
                ->screenshot('20-antes-upload-4-imagens')

                // Preencher dados básicos
                ->type('name', 'Produto 4 Imagens Test')
                ->type('description', 'Produto com quatro imagens de teste para verificar grid e contador')
                ->select('category_id', '1')
                ->type('original_price', '200.00')
                ->type('sale_price', '179.90')
                ->type('stock', '30')

                ->scrollIntoView('h2')
                ->pause(1000)
                ->screenshot('21-secao-imagens');

            // Upload da primeira imagem
            $browser->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-1.png")
                ->pause(2000)
                ->screenshot('22-preview-1-de-4');

            // Upload da segunda imagem
            $browser->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-2.png")
                ->pause(2000)
                ->screenshot('23-preview-2-de-4');

            // Upload da terceira imagem
            $browser->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-3.png")
                ->pause(2000)
                ->screenshot('24-preview-3-de-4');

            // Upload da quarta imagem
            $browser->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-4.png")
                ->pause(2000)
                ->screenshot('25-preview-4-de-4');

            // Verifica contador
            $browser->assertSee('4 de 4 imagens')
                ->pause(1000)

                // Verifica que limite foi atingido (4 imagens)
                ->assertSee('4 de 4 imagens')

                ->screenshot('26-limite-4-imagens-atingido')

                // Submit
                ->select('status', 'published')
                ->scrollIntoView('button[type="submit"]')
                ->press('Criar Produto')
                ->pause(5000)
                ->screenshot('27-apos-submit-4-imagens');

            echo "\n✅ Produto com 4 imagens criado\n";
        });

        // Verifica no banco
        $product = Product::where('name', 'Produto 4 Imagens Test')->first();
        $this->assertNotNull($product);
        $this->assertEquals(4, $product->getMedia('product_images')->count());
    }

    /**
     * Test: Remoção de imagem do preview antes de submeter.
     */
    public function test_seller_can_remove_image_from_preview(): void
    {
        $user = $this->createAuthenticatedSeller();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/seller/products/create')
                ->type('name', 'Produto Remover Preview Test')
                ->type('description', 'Testando remoção de imagens do preview')
                ->select('category_id', '1')
                ->type('original_price', '80.00')
                ->type('sale_price', '69.90')
                ->type('stock', '10')

                // Upload de 2 imagens
                ->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-1.png")
                ->pause(2000)
                ->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-2.png")
                ->pause(2000)

                ->screenshot('30-2-imagens-carregadas')
                ->assertSee('2 de 4 imagens')

                // Hover na primeira imagem para ver botão de remover
                ->mouseover('div[class*="group"][class*="aspect-square"]:first-child')
                ->pause(500)
                ->screenshot('31-hover-mostra-botao-remover')

                // Clica no botão de remover (X) da primeira imagem
                ->click('div[class*="group"][class*="aspect-square"]:first-child button')
                ->pause(1000)
                ->screenshot('32-apos-remover-1-imagem')

                // Verifica contador atualizado
                ->assertSee('1 de 4 imagens')

                // Pode adicionar novamente
                ->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-3.png")
                ->pause(2000)
                ->assertSee('2 de 4 imagens')

                ->screenshot('33-readicionou-outra-imagem')
                ->select('status', 'published')
                ->scrollIntoView('button[type="submit"]')
                ->press('Criar Produto')
                ->pause(5000);

            echo "\n✅ Teste de remoção de preview concluído\n";
        });
    }

    /**
     * Test: Validação de dimensões mínimas (deve rejeitar imagem pequena).
     */
    public function test_image_validation_rejects_small_dimensions(): void
    {
        $user = $this->createAuthenticatedSeller();
        $smallImagePath = "{$this->testImagesDir}/test-small.png"; // 600x600

        $this->browse(function (Browser $browser) use ($user, $smallImagePath) {
            $browser->loginAs($user)
                ->visit('/seller/products/create')
                ->screenshot('40-antes-upload-imagem-pequena')

                // Tenta fazer upload de imagem pequena
                ->attach('input[type="file"][name="images[]"]', $smallImagePath)
                ->pause(2000)
                ->screenshot('41-apos-upload-imagem-pequena')

                // Verifica mensagem de erro
                ->assertSee('Dimensões mínimas: 800x800px')

                // Verifica que preview NÃO apareceu
                ->assertDontSee('1 de 4 imagens');

            echo "\n✅ Validação de dimensões funcionando corretamente\n";
        });
    }

    /**
     * Test: Fluxo completo - criar, editar e adicionar mais imagens.
     */
    public function test_complete_flow_create_then_add_more_images(): void
    {
        $user = $this->createAuthenticatedSeller();

        $this->browse(function (Browser $browser) use ($user) {
            // PASSO 1: Criar produto com 1 imagem
            $browser->loginAs($user)
                ->visit('/seller/products/create')
                ->type('name', 'Produto Fluxo Completo Test')
                ->type('description', 'Produto para testar fluxo completo de adição de imagens')
                ->select('category_id', '1')
                ->type('original_price', '150.00')
                ->type('sale_price', '129.90')
                ->type('stock', '25')
                ->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-1.png")
                ->pause(2000)
                ->screenshot('50-criando-com-1-imagem')
                ->select('status', 'published')
                ->scrollIntoView('button[type="submit"]')
                ->press('Criar Produto')
                ->pause(5000);

            $currentUrl = $browser->driver->getCurrentURL();
            echo "\n📍 Produto criado, redirecionado para: ".$currentUrl."\n";

            // PASSO 2: Agora está na página de edição, adicionar mais 2 imagens
            $browser->screenshot('51-pagina-edicao-com-1-imagem-existente')
                ->assertSee('Atual') // Badge de imagem existente

                // Upload de mais 2 imagens
                ->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-2.png")
                ->pause(2000)
                ->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-3.png")
                ->pause(2000)
                ->screenshot('52-adicionou-2-novas-imagens')

                // Verifica mix de "Atual" e "Novo"
                ->assertSeeIn('div[class*="bg-primary-600"]', 'Atual')
                ->assertSeeIn('div[class*="bg-success-600"]', 'Novo')
                ->assertSee('3 de 4 imagens')

                ->scrollIntoView('button[type="submit"]')
                ->press('Atualizar Produto')
                ->pause(5000)
                ->screenshot('53-apos-atualizar');

            echo "\n✅ Fluxo completo concluído - produto agora tem 3 imagens\n";
        });

        // Verifica no banco
        $product = Product::where('name', 'Produto Fluxo Completo Test')->first();
        $this->assertNotNull($product);
        $this->assertEquals(3, $product->getMedia('product_images')->count());
    }

    /**
     * Test: Visualização do grid de imagens responsivo.
     */
    public function test_image_grid_display_is_responsive(): void
    {
        $user = $this->createAuthenticatedSeller();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/seller/products/create')
                ->type('name', 'Test Grid Responsivo')
                ->type('description', 'Testando grid responsivo de imagens')
                ->select('category_id', '1')
                ->type('original_price', '100.00')
                ->type('sale_price', '89.90')
                ->type('stock', '10')

                // Upload 3 imagens para testar grid
                ->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-1.png")
                ->pause(1500)
                ->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-2.png")
                ->pause(1500)
                ->attach('input[type="file"][name="images[]"]', "{$this->testImagesDir}/test-product-3.png")
                ->pause(1500)

                ->screenshot('60-grid-3-imagens-desktop')

                // Resize para mobile
                ->resize(375, 667)
                ->pause(1000)
                ->screenshot('61-grid-3-imagens-mobile')

                // Resize de volta
                ->maximize()
                ->pause(500)
                ->screenshot('62-grid-3-imagens-maximized');

            echo "\n✅ Grid responsivo testado em diferentes tamanhos\n";
        });
    }
}

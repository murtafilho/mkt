<?php

use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

    $this->user = User::factory()->create();
    Storage::fake('public');
});

test('seller registration page can be rendered', function () {
    $response = $this->actingAs($this->user)->get(route('seller.register'));

    $response->assertStatus(200);
    $response->assertViewIs('seller.register');
});

test('user can register as seller with complete data', function () {
    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'description' => 'Descrição da loja de teste',
        'document_number' => '11.222.333/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Empresa Teste LTDA',
        'trade_name' => 'Loja Teste',
        'state_registration' => '123456789',
        'business_phone' => '11987654321',
        'business_email' => 'contato@lojateste.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'complement' => 'Sala 100',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertRedirect(route('seller.dashboard'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('sellers', [
        'user_id' => $this->user->id,
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'status' => 'pending',
    ]);
});

test('user can register as seller with minimal data (individual)', function () {
    $sellerData = [
        'store_name' => 'Loja Individual',
        'slug' => 'loja-individual',
        'document_number' => '529.982.247-25', // Valid CPF
        'person_type' => 'individual',
        'business_phone' => '11987654321',
        'business_email' => 'contato@individual.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertRedirect(route('seller.dashboard'));

    $this->assertDatabaseHas('sellers', [
        'user_id' => $this->user->id,
        'person_type' => 'individual',
        'status' => 'pending',
    ]);
});

test('seller registration requires authentication', function () {
    $response = $this->get(route('seller.register'));

    $response->assertRedirect(route('login'));
});

test('user cannot register as seller twice', function () {
    // Create existing seller for this user and assign role
    Seller::factory()->create(['user_id' => $this->user->id]);
    $this->user->assignRole('seller');

    // Try to access registration page - should redirect to dashboard
    $response = $this->actingAs($this->user)->get(route('seller.register'));

    $response->assertRedirect(route('seller.dashboard'));
    $response->assertSessionHas('info');
});

test('document number must be unique', function () {
    // Create seller with existing document
    $existingDocument = '11.222.333/0001-81';
    Seller::factory()->create(['document_number' => $existingDocument]);

    $sellerData = [
        'store_name' => 'Loja Nova',
        'document_number' => $existingDocument, // Duplicate document
        'person_type' => 'business',
        'company_name' => 'Loja Nova LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@lojanova.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('document_number');
});

test('store name is required', function () {
    $sellerData = [
        'slug' => 'loja-teste',
        'document_number' => '11.222.333/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@lojateste.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('store_name');
});

test('document number must be valid cpf or cnpj', function () {
    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'document_number' => '123', // Invalid
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@lojateste.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('document_number');
});

test('accepts valid cpf with formatting', function () {
    $sellerData = [
        'store_name' => 'Loja Teste CPF',
        'slug' => 'loja-teste-cpf',
        'document_number' => '529.982.247-25', // Valid CPF with formatting
        'person_type' => 'individual',
        'business_phone' => '11987654321',
        'business_email' => 'cpf@lojateste.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
});

test('accepts valid cnpj with formatting', function () {
    $sellerData = [
        'store_name' => 'Loja Teste CNPJ',
        'slug' => 'loja-teste-cnpj',
        'document_number' => '11.222.333/0001-81', // Valid CNPJ with formatting
        'person_type' => 'business',
        'company_name' => 'Teste CNPJ LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'cnpj@lojateste.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
});

test('rejects cpf with all same digits', function () {
    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'document_number' => '111.111.111-11', // Invalid CPF (all same digits)
        'person_type' => 'individual',
        'business_phone' => '11987654321',
        'business_email' => 'invalid@lojateste.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('document_number');
});

test('rejects cnpj with all same digits', function () {
    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'document_number' => '00.000.000/0000-00', // Invalid CNPJ (all same digits)
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'invalid@lojateste.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('document_number');
});

test('business email must be valid', function () {
    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'document_number' => '11.222.333/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'email-invalido', // Invalid email
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('business_email');
});

test('terms must be accepted', function () {
    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'document_number' => '11.222.333/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@lojateste.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => false, // Not accepted
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('terms_accepted');
});

test('can upload logo during registration', function () {
    $logo = UploadedFile::fake()->image('logo.jpg', 300, 300);

    $sellerData = [
        'store_name' => 'Loja com Logo',
        'slug' => 'loja-com-logo',
        'document_number' => '22.333.444/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@loja.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
        'logo' => $logo,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertRedirect(route('seller.dashboard'));

    $seller = Seller::where('user_id', $this->user->id)->first();
    expect($seller->getFirstMedia('seller_logo'))->not->toBeNull();
});

test('can upload banner during registration', function () {
    $banner = UploadedFile::fake()->image('banner.jpg', 1920, 400);

    $sellerData = [
        'store_name' => 'Loja com Banner',
        'slug' => 'loja-com-banner',
        'document_number' => '33.444.555/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@loja.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
        'banner' => $banner,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertRedirect(route('seller.dashboard'));

    $seller = Seller::where('user_id', $this->user->id)->first();
    expect($seller->getFirstMedia('seller_banner'))->not->toBeNull();
});

test('can upload both logo and banner during registration', function () {
    $logo = UploadedFile::fake()->image('logo.jpg', 300, 300);
    $banner = UploadedFile::fake()->image('banner.jpg', 1920, 400);

    $sellerData = [
        'store_name' => 'Loja Completa',
        'slug' => 'loja-completa',
        'document_number' => '44.555.666/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@loja.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
        'logo' => $logo,
        'banner' => $banner,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertRedirect(route('seller.dashboard'));

    $seller = Seller::where('user_id', $this->user->id)->first();
    expect($seller->getFirstMedia('seller_logo'))->not->toBeNull();
    expect($seller->getFirstMedia('seller_banner'))->not->toBeNull();
});

test('logo must be valid image type', function () {
    $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'document_number' => '11.222.333/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@loja.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
        'logo' => $invalidFile,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('logo');
});

test('logo must meet minimum dimensions', function () {
    $smallLogo = UploadedFile::fake()->image('logo.jpg', 100, 100); // Too small

    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'document_number' => '11.222.333/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@loja.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
        'logo' => $smallLogo,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('logo');
});

test('banner must meet minimum dimensions', function () {
    $smallBanner = UploadedFile::fake()->image('banner.jpg', 500, 100); // Too small

    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'document_number' => '11.222.333/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@loja.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
        'banner' => $smallBanner,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('banner');
});

test('logo file size cannot exceed 2mb', function () {
    $largeLogo = UploadedFile::fake()->image('logo.jpg', 300, 300)->size(3000); // 3MB

    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'document_number' => '11.222.333/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@loja.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
        'logo' => $largeLogo,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('logo');
});

test('banner file size cannot exceed 4mb', function () {
    $largeBanner = UploadedFile::fake()->image('banner.jpg', 1920, 400)->size(5000); // 5MB

    $sellerData = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'document_number' => '11.222.333/0001-81', // Valid CNPJ
        'person_type' => 'business',
        'company_name' => 'Teste LTDA',
        'business_phone' => '11987654321',
        'business_email' => 'contato@loja.com',
        'postal_code' => '01310-100',
        'street' => 'Avenida Paulista',
        'number' => '1578',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'terms_accepted' => true,
        'banner' => $largeBanner,
    ];

    $response = $this->actingAs($this->user)->post(route('seller.store'), $sellerData);

    $response->assertSessionHasErrors('banner');
});

<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Eletrônicos',
                'slug' => 'eletronicos',
                'description' => 'Produtos eletrônicos e tecnologia',
                'is_active' => true,
                'children' => [
                    ['name' => 'Smartphones', 'slug' => 'smartphones', 'description' => 'Celulares e acessórios'],
                    ['name' => 'Computadores', 'slug' => 'computadores', 'description' => 'Notebooks, desktops e acessórios'],
                    ['name' => 'Áudio', 'slug' => 'audio', 'description' => 'Fones de ouvido, caixas de som'],
                ],
            ],
            [
                'name' => 'Moda',
                'slug' => 'moda',
                'description' => 'Roupas, calçados e acessórios',
                'is_active' => true,
                'children' => [
                    ['name' => 'Roupas Masculinas', 'slug' => 'roupas-masculinas', 'description' => 'Moda masculina'],
                    ['name' => 'Roupas Femininas', 'slug' => 'roupas-femininas', 'description' => 'Moda feminina'],
                    ['name' => 'Calçados', 'slug' => 'calcados', 'description' => 'Sapatos, tênis e sandálias'],
                    ['name' => 'Acessórios', 'slug' => 'acessorios-moda', 'description' => 'Bolsas, relógios e bijuterias'],
                ],
            ],
            [
                'name' => 'Casa e Decoração',
                'slug' => 'casa-decoracao',
                'description' => 'Produtos para casa e decoração',
                'is_active' => true,
                'children' => [
                    ['name' => 'Móveis', 'slug' => 'moveis', 'description' => 'Móveis para todos os ambientes'],
                    ['name' => 'Decoração', 'slug' => 'decoracao', 'description' => 'Itens decorativos'],
                    ['name' => 'Cama, Mesa e Banho', 'slug' => 'cama-mesa-banho', 'description' => 'Produtos para cama, mesa e banho'],
                ],
            ],
            [
                'name' => 'Esportes e Lazer',
                'slug' => 'esportes-lazer',
                'description' => 'Produtos esportivos e recreativos',
                'is_active' => true,
                'children' => [
                    ['name' => 'Equipamentos Esportivos', 'slug' => 'equipamentos-esportivos', 'description' => 'Equipamentos para prática esportiva'],
                    ['name' => 'Roupas Esportivas', 'slug' => 'roupas-esportivas', 'description' => 'Vestuário esportivo'],
                    ['name' => 'Camping e Aventura', 'slug' => 'camping-aventura', 'description' => 'Equipamentos para camping'],
                ],
            ],
            [
                'name' => 'Beleza e Cuidados Pessoais',
                'slug' => 'beleza-cuidados',
                'description' => 'Produtos de beleza e higiene',
                'is_active' => true,
                'children' => [
                    ['name' => 'Maquiagem', 'slug' => 'maquiagem', 'description' => 'Produtos de maquiagem'],
                    ['name' => 'Cuidados com a Pele', 'slug' => 'cuidados-pele', 'description' => 'Produtos para cuidados com a pele'],
                    ['name' => 'Cabelos', 'slug' => 'cabelos', 'description' => 'Produtos para cabelo'],
                    ['name' => 'Perfumes', 'slug' => 'perfumes', 'description' => 'Perfumes e fragrâncias'],
                ],
            ],
            [
                'name' => 'Livros e Papelaria',
                'slug' => 'livros-papelaria',
                'description' => 'Livros, materiais escolares e escritório',
                'is_active' => true,
                'children' => [
                    ['name' => 'Livros', 'slug' => 'livros', 'description' => 'Livros diversos'],
                    ['name' => 'Material Escolar', 'slug' => 'material-escolar', 'description' => 'Materiais para escola'],
                    ['name' => 'Material de Escritório', 'slug' => 'material-escritorio', 'description' => 'Produtos para escritório'],
                ],
            ],
            [
                'name' => 'Alimentos e Bebidas',
                'slug' => 'alimentos-bebidas',
                'description' => 'Produtos alimentícios e bebidas',
                'is_active' => true,
                'children' => [
                    ['name' => 'Alimentos', 'slug' => 'alimentos', 'description' => 'Alimentos diversos'],
                    ['name' => 'Bebidas', 'slug' => 'bebidas', 'description' => 'Bebidas diversas'],
                    ['name' => 'Produtos Naturais', 'slug' => 'produtos-naturais', 'description' => 'Produtos orgânicos e naturais'],
                ],
            ],
            [
                'name' => 'Brinquedos e Jogos',
                'slug' => 'brinquedos-jogos',
                'description' => 'Brinquedos e jogos infantis',
                'is_active' => true,
                'children' => [
                    ['name' => 'Brinquedos Infantis', 'slug' => 'brinquedos-infantis', 'description' => 'Brinquedos para crianças'],
                    ['name' => 'Jogos de Tabuleiro', 'slug' => 'jogos-tabuleiro', 'description' => 'Jogos de mesa'],
                    ['name' => 'Videogames', 'slug' => 'videogames', 'description' => 'Consoles e jogos eletrônicos'],
                ],
            ],
            [
                'name' => 'Automotivo',
                'slug' => 'automotivo',
                'description' => 'Produtos para veículos',
                'is_active' => true,
                'children' => [
                    ['name' => 'Acessórios Automotivos', 'slug' => 'acessorios-automotivos', 'description' => 'Acessórios para carros'],
                    ['name' => 'Peças', 'slug' => 'pecas', 'description' => 'Peças automotivas'],
                    ['name' => 'Ferramentas', 'slug' => 'ferramentas', 'description' => 'Ferramentas automotivas'],
                ],
            ],
            [
                'name' => 'Pet Shop',
                'slug' => 'pet-shop',
                'description' => 'Produtos para animais de estimação',
                'is_active' => true,
                'children' => [
                    ['name' => 'Alimentação Pet', 'slug' => 'alimentacao-pet', 'description' => 'Rações e petiscos'],
                    ['name' => 'Acessórios Pet', 'slug' => 'acessorios-pet', 'description' => 'Acessórios para pets'],
                    ['name' => 'Higiene Pet', 'slug' => 'higiene-pet', 'description' => 'Produtos de higiene'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'];
            unset($categoryData['children']);

            // Create parent category
            $parent = Category::create($categoryData);

            // Create child categories
            foreach ($children as $childData) {
                $childData['parent_id'] = $parent->id;
                $childData['is_active'] = true;
                Category::create($childData);
            }
        }
    }
}

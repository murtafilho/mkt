<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use App\Models\Seller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        /** @var Product|null $product */
        $product = $this->route('product');

        /** @var Seller|null $seller */
        $seller = $user->seller;

        return $product instanceof Product && $seller !== null && $product->seller_id === $seller->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                // SKU must be unique per seller (not globally), ignoring current product
                Rule::unique('products', 'sku')
                    ->where('seller_id', $product->seller_id)
                    ->ignore($product->id),
            ],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'original_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0', 'lte:original_price'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'depth' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,published,inactive,out_of_stock'],
            'is_featured' => ['boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120', 'dimensions:min_width=800,min_height=800'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            /** @var Product|null $product */
            $product = $this->route('product');

            if ($this->hasFile('images') && $product) {
                $currentCount = $product->getMedia('product_images')->count();
                $newCount = count($this->file('images'));
                $totalCount = $currentCount + $newCount;

                if ($totalCount > 4) {
                    $maxAllowed = 4 - $currentCount;
                    $validator->errors()->add(
                        'images',
                        "O produto já possui {$currentCount} imagem(ns). Você pode adicionar no máximo {$maxAllowed} imagem(ns) adicional(is)."
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório.',
            'description.required' => 'A descrição do produto é obrigatória.',
            'category_id.required' => 'Selecione uma categoria.',
            'category_id.exists' => 'Categoria inválida.',
            'sku.unique' => 'Este SKU já está em uso.',
            'original_price.required' => 'O preço original é obrigatório.',
            'original_price.min' => 'O preço original deve ser maior ou igual a zero.',
            'sale_price.required' => 'O preço de venda é obrigatório.',
            'sale_price.min' => 'O preço de venda deve ser maior ou igual a zero.',
            'sale_price.lte' => 'O preço de venda não pode ser maior que o preço original.',
            'stock.required' => 'A quantidade em estoque é obrigatória.',
            'stock.min' => 'O estoque não pode ser negativo.',
            'status.required' => 'Selecione o status do produto.',
            'images.*.required' => 'O arquivo de imagem é obrigatório.',
            'images.*.image' => 'O arquivo deve ser uma imagem.',
            'images.*.mimes' => 'As imagens devem estar nos formatos: JPEG, JPG, PNG ou WEBP.',
            'images.*.max' => 'Cada imagem não pode ser maior que 5MB.',
            'images.*.dimensions' => 'As imagens devem ter no mínimo 800x800 pixels e proporção 1:1 (quadrado).',
        ];
    }
}

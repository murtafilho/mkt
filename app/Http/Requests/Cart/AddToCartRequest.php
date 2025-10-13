<?php

namespace App\Http\Requests\Cart;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Everyone can add to cart (guests + authenticated)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
                function ($attribute, $value, $fail) {
                    $product = Product::find($value);

                    if (! $product) {
                        $fail('O produto selecionado não existe.');

                        return;
                    }

                    if ($product->status !== 'published') {
                        $fail('Este produto não está disponível para compra.');

                        return;
                    }

                    if ($product->stock < 1) {
                        $fail('Este produto está fora de estoque.');

                        return;
                    }

                    /** @var \App\Models\Seller $seller */
                    $seller = $product->seller;

                    if (! $seller->isApproved()) {
                        $fail('O vendedor deste produto não está ativo.');

                        return;
                    }
                },
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $productId = $this->input('product_id');
                    $product = Product::find($productId);

                    if ($product && $value > $product->stock) {
                        $fail("A quantidade máxima disponível para este produto é {$product->stock}.");
                    }
                },
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'produto',
            'quantity' => 'quantidade',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Selecione um produto para adicionar ao carrinho.',
            'product_id.integer' => 'O produto selecionado é inválido.',
            'product_id.exists' => 'O produto selecionado não foi encontrado.',
            'quantity.required' => 'Informe a quantidade desejada.',
            'quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'quantity.min' => 'A quantidade mínima é 1.',
        ];
    }
}

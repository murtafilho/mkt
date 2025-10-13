<?php

namespace App\Http\Requests\Cart;

use App\Models\CartItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $cartItemId = $this->route('cartItemId');
        $cartItem = CartItem::find($cartItemId);

        if (! $cartItem) {
            return false;
        }

        // Check if cart item belongs to current user or session
        if (auth()->check()) {
            return $cartItem->user_id === auth()->id();
        }

        return $cartItem->session_id === session()->getId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $cartItemId = $this->route('cartItemId');
                    $cartItem = CartItem::with('product')->find($cartItemId);

                    if (! $cartItem) {
                        $fail('Item do carrinho não encontrado.');

                        return;
                    }

                    if ($value > $cartItem->product->stock) {
                        $fail("A quantidade máxima disponível para este produto é {$cartItem->product->stock}.");
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
            'quantity.required' => 'Informe a quantidade desejada.',
            'quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'quantity.min' => 'A quantidade mínima é 1.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Você não tem permissão para atualizar este item do carrinho.'
        );
    }
}

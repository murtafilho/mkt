<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class ProcessCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can checkout
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Shipping address fields
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:20'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:10'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'postal_code' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],

            // Payment data from Payment Brick
            'payment_data' => ['required', 'array'],
            'payment_data.payment_method_id' => ['required', 'string'],
            'payment_data.payer' => ['required', 'array'],
            'payment_data.payer.email' => ['required', 'email'],

            // Payment method type (bank_transfer, credit_card, debit_card, ticket)
            'payment_method' => ['required', 'string', 'in:bank_transfer,credit_card,debit_card,ticket'],

            // Optional: coupon code
            'coupon_code' => ['nullable', 'string', 'max:50'],

            // Optional: notes
            'notes' => ['nullable', 'string', 'max:500'],
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
            'recipient_name.required' => 'O nome do destinatário é obrigatório.',
            'recipient_name.max' => 'O nome do destinatário não pode ter mais de 255 caracteres.',

            'recipient_phone.required' => 'O telefone do destinatário é obrigatório.',
            'recipient_phone.max' => 'O telefone não pode ter mais de 20 caracteres.',

            'street.required' => 'O endereço é obrigatório.',
            'street.max' => 'O endereço não pode ter mais de 255 caracteres.',

            'number.required' => 'O número é obrigatório.',
            'number.max' => 'O número não pode ter mais de 10 caracteres.',

            'complement.max' => 'O complemento não pode ter mais de 255 caracteres.',

            'neighborhood.required' => 'O bairro é obrigatório.',
            'neighborhood.max' => 'O bairro não pode ter mais de 100 caracteres.',

            'city.required' => 'A cidade é obrigatória.',
            'city.max' => 'A cidade não pode ter mais de 100 caracteres.',

            'state.required' => 'O estado é obrigatório.',
            'state.size' => 'O estado deve ter 2 caracteres (ex: SP, RJ).',

            'postal_code.required' => 'O CEP é obrigatório.',
            'postal_code.regex' => 'O CEP deve estar no formato 00000-000.',

            'payment_data.required' => 'Os dados de pagamento são obrigatórios.',
            'payment_data.array' => 'Os dados de pagamento devem ser um array.',
            'payment_data.payment_method_id.required' => 'O método de pagamento é obrigatório.',
            'payment_data.payer.required' => 'Os dados do pagador são obrigatórios.',
            'payment_data.payer.email.required' => 'O e-mail do pagador é obrigatório.',
            'payment_data.payer.email.email' => 'O e-mail do pagador deve ser válido.',

            'payment_method.required' => 'O tipo de pagamento é obrigatório.',
            'payment_method.in' => 'O tipo de pagamento deve ser: PIX, cartão de crédito, cartão de débito ou boleto.',

            'coupon_code.max' => 'O código do cupom não pode ter mais de 50 caracteres.',

            'notes.max' => 'As observações não podem ter mais de 500 caracteres.',
        ];
    }
}

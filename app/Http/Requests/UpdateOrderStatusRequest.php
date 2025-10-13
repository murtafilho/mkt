<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['paid', 'preparing', 'shipped', 'delivered', 'cancelled']),
            ],
            'tracking_code' => [
                'nullable',
                'string',
                'max:100',
                'required_if:status,shipped',
            ],
            'note' => [
                'nullable',
                'string',
                'max:500',
            ],
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
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido.',
            'tracking_code.required_if' => 'O código de rastreamento é obrigatório quando o status é "Enviado".',
            'tracking_code.max' => 'O código de rastreamento não pode ter mais de 100 caracteres.',
            'note.max' => 'A observação não pode ter mais de 500 caracteres.',
        ];
    }
}

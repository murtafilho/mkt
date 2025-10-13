<?php

namespace App\Http\Requests\Seller;

use App\Rules\CpfCnpj;
use Illuminate\Foundation\Http\FormRequest;

class StoreSellerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated and not have a seller profile yet
        return auth()->check() && ! auth()->user()->seller;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'document_number' => ['required', 'string', 'max:18', 'unique:sellers,document_number', new CpfCnpj],
            'person_type' => ['required', 'in:individual,business'],
            'company_name' => ['required_if:person_type,business', 'nullable', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'state_registration' => ['nullable', 'string', 'max:20'],
            'business_phone' => ['required', 'string', 'max:20'],
            'business_email' => ['required', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],

            // Images (using unified image-uploader component)
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048', 'dimensions:min_width=200,min_height=200'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096', 'dimensions:min_width=960,min_height=200'],

            // Address fields
            'type' => ['nullable', 'string', 'in:business,return'],
            'postal_code' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:10'],
            'complement' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],

            'terms_accepted' => ['required', 'accepted'],
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
            'store_name.required' => 'O nome da loja é obrigatório.',
            'document_number.required' => 'O CPF/CNPJ é obrigatório.',
            'document_number.unique' => 'Este CPF/CNPJ já está cadastrado.',
            'person_type.required' => 'Selecione o tipo de pessoa.',
            'company_name.required_if' => 'A razão social é obrigatória para pessoa jurídica.',
            'business_phone.required' => 'O telefone comercial é obrigatório.',
            'business_email.required' => 'O e-mail comercial é obrigatório.',
            'business_email.email' => 'O e-mail comercial deve ser válido.',
            'logo.image' => 'O logo deve ser uma imagem.',
            'logo.mimes' => 'O logo deve ser JPG, PNG ou WEBP.',
            'logo.max' => 'O logo não pode ser maior que 2MB.',
            'logo.dimensions' => 'O logo deve ter no mínimo 200x200 pixels.',
            'banner.image' => 'O banner deve ser uma imagem.',
            'banner.mimes' => 'O banner deve ser JPG, PNG ou WEBP.',
            'banner.max' => 'O banner não pode ser maior que 4MB.',
            'banner.dimensions' => 'O banner deve ter no mínimo 960x200 pixels.',

            // Address validation messages
            'postal_code.required' => 'O CEP é obrigatório.',
            'postal_code.regex' => 'O CEP deve estar no formato 12345-678.',
            'street.required' => 'O endereço é obrigatório.',
            'number.required' => 'O número é obrigatório.',
            'neighborhood.required' => 'O bairro é obrigatório.',
            'city.required' => 'A cidade é obrigatória.',
            'state.required' => 'O estado é obrigatório.',
            'state.size' => 'O estado deve ter 2 caracteres (ex: MG).',

            'terms_accepted.required' => 'Você deve aceitar os termos de uso.',
            'terms_accepted.accepted' => 'Você deve aceitar os termos de uso.',
        ];
    }
}

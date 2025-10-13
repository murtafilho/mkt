<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfCnpj implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('O :attribute deve ser uma string.');

            return;
        }

        // Remove non-numeric characters
        $document = preg_replace('/[^0-9]/', '', $value);

        // Check if it's CPF (11 digits) or CNPJ (14 digits)
        if (strlen($document) === 11) {
            if (! $this->validateCpf($document)) {
                $fail('O :attribute informado não é um CPF válido.');
            }
        } elseif (strlen($document) === 14) {
            if (! $this->validateCnpj($document)) {
                $fail('O :attribute informado não é um CNPJ válido.');
            }
        } else {
            $fail('O :attribute deve conter 11 dígitos (CPF) ou 14 dígitos (CNPJ).');
        }
    }

    /**
     * Validate CPF (Cadastro de Pessoas Físicas).
     */
    private function validateCpf(string $cpf): bool
    {
        // Check for known invalid CPFs (all digits the same)
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validate first check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if (intval($cpf[9]) !== $digit1) {
            return false;
        }

        // Validate second check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        return intval($cpf[10]) === $digit2;
    }

    /**
     * Validate CNPJ (Cadastro Nacional da Pessoa Jurídica).
     */
    private function validateCnpj(string $cnpj): bool
    {
        // Check for known invalid CNPJs (all digits the same)
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Validate first check digit
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if (intval($cnpj[12]) !== $digit1) {
            return false;
        }

        // Validate second check digit
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        return intval($cnpj[13]) === $digit2;
    }
}

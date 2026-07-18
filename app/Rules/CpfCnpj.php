<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CpfCnpj implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if (strlen($digits) === 11 && $this->isValidCpf($digits)) {
            return;
        }

        if (strlen($digits) === 14 && $this->isValidCnpj($digits)) {
            return;
        }

        $fail('O :attribute deve ser um CPF ou CNPJ válido.');
    }

    /**
     * Validate the check digits of an 11-digit CPF number.
     */
    private function isValidCpf(string $cpf): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        if ((int) $cpf[9] !== $this->checkDigit($cpf, 9)) {
            return false;
        }

        return (int) $cpf[10] === $this->checkDigit($cpf, 10);
    }

    /**
     * Calculate a CPF check digit for the given number of weighted positions.
     */
    private function checkDigit(string $cpf, int $length): int
    {
        $sum = 0;

        for ($i = 0; $i < $length; $i++) {
            $sum += (int) $cpf[$i] * ($length + 1 - $i);
        }

        $remainder = ($sum * 10) % 11;

        return $remainder === 10 ? 0 : $remainder;
    }

    /**
     * Validate the check digits of a 14-digit CNPJ number.
     */
    private function isValidCnpj(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $firstWeights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        if ((int) $cnpj[12] !== $this->weightedCheckDigit(substr($cnpj, 0, 12), $firstWeights)) {
            return false;
        }

        $secondWeights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        return (int) $cnpj[13] === $this->weightedCheckDigit(substr($cnpj, 0, 13), $secondWeights);
    }

    /**
     * Calculate a CNPJ check digit using the given positional weights.
     *
     * @param  list<int>  $weights
     */
    private function weightedCheckDigit(string $base, array $weights): int
    {
        $sum = 0;

        foreach ($weights as $i => $weight) {
            $sum += (int) $base[$i] * $weight;
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}

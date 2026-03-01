<?php

declare(strict_types=1);

namespace Modules\Common\Core\DTOs\Concerns\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cpf implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value) || $value === '') {
            return;
        }

        $c = preg_replace('/\D/', '', $value);

        if (strlen($c) !== 11 || preg_match("/^{$c[0]}{11}$/", $c)) {
            $fail('O campo :attribute é inválido.');
        }

        for ($s = 10, $n = 0, $i = 0; $s >= 2; $n += $c[$i++] * $s--);

        if ((int) $c[9] !== ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            $fail('O campo :attribute é inválido.');
        }

        for ($s = 11, $n = 0, $i = 0; $s >= 2; $n += $c[$i++] * $s--);

        if ((int) $c[10] !== ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            $fail('O campo :attribute é inválido.');
        }
    }
}

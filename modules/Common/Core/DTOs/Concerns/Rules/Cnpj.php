<?php

declare(strict_types=1);

namespace Modules\Common\Core\DTOs\Concerns\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnpj implements ValidationRule
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

        $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        if (strlen($c) !== 14) {
            $fail('O campo :attribute é inválido.');

        } elseif (preg_match("/^{$c[0]}{14}$/", $c) > 0) {
            $fail('O campo :attribute é inválido.');
        }

        for ($i = 0, $n = 0; $i < 12; $n += $c[$i] * $b[++$i]);

        if ((int) $c[12] !== ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            $fail('O campo :attribute é inválido.');
        }

        for ($i = 0, $n = 0; $i <= 12; $n += $c[$i] * $b[$i++]);

        if ((int) $c[13] !== ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            $fail('O campo :attribute é inválido.');
        }
    }
}

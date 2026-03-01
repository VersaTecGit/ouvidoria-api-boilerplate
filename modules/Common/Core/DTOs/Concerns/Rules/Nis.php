<?php

declare(strict_types=1);

namespace Modules\Common\Core\DTOs\Concerns\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Nis implements ValidationRule
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

        $nis = sprintf('%011s', empty($value) ? '' : preg_replace('/[^\d]/', '', $value));

        if (mb_strlen($nis) !== 11 || preg_match("/^{$nis[0]}{11}$/", $nis)) {
            $fail('O campo :attribute é inválido.');
        }

        for ($d = 0, $p = 2, $c = 9; $c >= 0; $c--, ($p < 9) ? $p++ : $p = 2) {
            $d += (int) $nis[$c] * $p;
        }

        if (! ((int) $nis[10] === (((10 * $d) % 11) % 10))) {
            $fail('O campo :attribute é inválido.');
        }
    }
}

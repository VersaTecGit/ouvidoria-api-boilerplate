<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\Common\Core\DTOs\Concerns\Rules\Cnpj;
use Modules\Common\Core\DTOs\Concerns\Rules\Cpf;

/**
 * The manifestant declares a single document field, so the rule picks the
 * validator by digit count. Chaining `Cpf` and `Cnpj` directly would reject
 * every value: each fails anything that is not its own length.
 */
class CpfOrCnpj implements ValidationRule
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

        $digits = preg_replace('/\D/', '', (string) $value);

        $rule = match (strlen((string) $digits)) {
            11 => new Cpf(),
            14 => new Cnpj(),
            default => null,
        };

        if ($rule === null) {
            $fail('O campo :attribute deve ser um CPF ou CNPJ válido.');

            return;
        }

        $rule->validate($attribute, $value, $fail);
    }
}

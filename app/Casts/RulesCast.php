<?php

namespace App\Casts;

use App\DTOs\TransformationMapRule;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class RulesCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        $rulesArray = json_decode($value, true);

        if (!is_array($rulesArray)) {
            throw new InvalidArgumentException('O campo rules deve ser um array JSON válido.');
        }

        return array_map(fn($rule) => TransformationMapRule::fromArray($rule), $rulesArray);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('O campo rules deve ser um array de regras.');
        }

        return json_encode(array_map(fn($rule) => $rule->toArray(), $value));
    }
}

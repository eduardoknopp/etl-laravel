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

        if (!is_array($rulesArray) || !isset($rulesArray['header'], $rulesArray['row'], $rulesArray['footer'])) {
            throw new InvalidArgumentException('O campo rules deve ser um array JSON válido contendo as chaves: header, row e footer.');
        }

        return [
            'header' => array_map(fn($rule) => TransformationMapRule::fromArray($rule), $rulesArray['header']),
            'row' => array_map(fn($rule) => TransformationMapRule::fromArray($rule), $rulesArray['row']),
            'footer' => array_map(fn($rule) => TransformationMapRule::fromArray($rule), $rulesArray['footer']),
        ];
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        if (!is_array($value) || !isset($value['header'], $value['row'], $value['footer'])) {
            throw new InvalidArgumentException('O campo rules deve ser um array contendo as chaves: header, row e footer.');
        }

        return json_encode([
            'header' => array_map(fn($rule) => $rule->toArray(), $value['header']),
            'row' => array_map(fn($rule) => $rule->toArray(), $value['row']),
            'footer' => array_map(fn($rule) => $rule->toArray(), $value['footer']),
        ]);
    }
}

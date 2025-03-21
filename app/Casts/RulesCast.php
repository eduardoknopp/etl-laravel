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
        if (empty($value)) {
            return [
                'mappings' => [],
                'sections' => [
                    'header' => [],
                    'row' => [],
                    'footer' => []
                ]
            ];
        }

        $rulesArray = json_decode($value, true);

        if (!is_array($rulesArray)) {
            throw new InvalidArgumentException('O campo rules deve ser um array JSON válido.');
        }

        $result = [
            'mappings' => [],
            'sections' => [
                'header' => [],
                'row' => [],
                'footer' => []
            ]
        ];

        // Processar mapeamentos principais
        if (isset($rulesArray['mappings']) && is_array($rulesArray['mappings'])) {
            $result['mappings'] = array_map(
                fn($rule) => TransformationMapRule::fromArray($rule), 
                $rulesArray['mappings']
            );
        }

        // Processar seções (header, row, footer) se existirem
        if (isset($rulesArray['sections']) && is_array($rulesArray['sections'])) {
            foreach (['header', 'row', 'footer'] as $section) {
                if (isset($rulesArray['sections'][$section]) && is_array($rulesArray['sections'][$section])) {
                    $result['sections'][$section] = array_map(
                        fn($rule) => TransformationMapRule::fromArray($rule),
                        $rulesArray['sections'][$section]
                    );
                }
            }
        }

        return $result;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('O campo rules deve ser um array.');
        }

        $result = [
            'mappings' => [],
            'sections' => [
                'header' => [],
                'row' => [],
                'footer' => []
            ]
        ];

        // Processar mapeamentos principais
        if (isset($value['mappings']) && is_array($value['mappings'])) {
            $result['mappings'] = array_map(
                fn($rule) => $rule instanceof TransformationMapRule ? $rule->toArray() : $rule,
                $value['mappings']
            );
        }

        // Processar seções (header, row, footer) se existirem
        if (isset($value['sections']) && is_array($value['sections'])) {
            foreach (['header', 'row', 'footer'] as $section) {
                if (isset($value['sections'][$section]) && is_array($value['sections'][$section])) {
                    $result['sections'][$section] = array_map(
                        fn($rule) => $rule instanceof TransformationMapRule ? $rule->toArray() : $rule,
                        $value['sections'][$section]
                    );
                }
            }
        }

        return json_encode($result);
    }
}

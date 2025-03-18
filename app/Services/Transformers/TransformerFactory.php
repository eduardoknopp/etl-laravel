<?php

namespace App\Services\Transformers;

use InvalidArgumentException;

class TransformerFactory
{
    public static function createTransformer(string $toType): TransformerInterface
    {
        return match ($toType) {
            'xml' => new XmlTransformer(),
            default => throw new InvalidArgumentException("Tipo de transformação '$toType' não suportado."),
        };
    }
}

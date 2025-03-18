<?php

namespace App\Services\Transformers;

interface TransformerInterface
{
    public function transform(array $data, array $rules, string $templateName): string;
}

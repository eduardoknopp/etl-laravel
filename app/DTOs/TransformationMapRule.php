<?php

namespace App\DTOs;

class TransformationMapRule
{
    public string $sourceField;
    public ?int $sourceIndex;
    public string $destinationField;
    public ?int $destinationIndex;

    public function __construct(
        string $sourceField,
        ?int $sourceIndex,
        string $destinationField,
        ?int $destinationIndex
    ) {
        $this->sourceField = $sourceField;
        $this->sourceIndex = $sourceIndex;
        $this->destinationField = $destinationField;
        $this->destinationIndex = $destinationIndex;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['source_field'] ?? throw new \InvalidArgumentException('source_field é obrigatório'),
            $data['source_index'] ?? null,
            $data['destination_field'] ?? throw new \InvalidArgumentException('destination_field é obrigatório'),
            $data['destination_index'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'source_field' => $this->sourceField,
            'source_index' => $this->sourceIndex,
            'destination_field' => $this->destinationField,
            'destination_index' => $this->destinationIndex,
        ];
    }
}

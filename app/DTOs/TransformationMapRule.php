<?php

namespace App\DTOs;

class TransformationMapRule
{
    // Constantes para os tipos de regras
    public const TYPE_FIELD_MAPPING = 'field_mapping'; // Mapeamento de campo para campo
    public const TYPE_FIXED_VALUE = 'fixed_value';     // Valor fixo definido pelo usuário
    public const TYPE_FORMULA = 'formula';             // Fórmula ou expressão
    public const TYPE_DATE_TRANSFORM = 'date_transform'; // Transformação de data
    public const TYPE_CONCAT = 'concat';               // Concatenação de campos
    public const TYPE_CONDITIONAL = 'conditional';     // Valor condicional

    public string $type;              // Tipo da regra (field_mapping, fixed_value, etc)
    public ?string $sourceField;      // Campo de origem (pode ser null para valores fixos)
    public ?string $sourcePath;       // Caminho para o campo em estruturas aninhadas (user.address.street)
    public ?int $sourceIndex;         // Índice do campo para formatos tabulares (como CSV)
    public string $destinationField;  // Campo de destino no template
    public ?string $destinationPath;  // Caminho para o campo em estruturas aninhadas de destino
    public ?int $destinationIndex;    // Índice do campo para formatos tabulares de destino
    public ?string $format;           // Formato para aplicar (uppercase, date, etc)
    public ?string $valueFormat;      // Formato de dados (string, number, date, etc)
    public ?string $fixedValue;       // Valor fixo (usado quando type é fixed_value)
    public ?array $options;           // Opções adicionais (condicionais, parâmetros de fórmula, etc)

    public function __construct(
        string $type,
        ?string $sourceField = null,
        ?string $sourcePath = null,
        ?int $sourceIndex = null,
        string $destinationField,
        ?string $destinationPath = null,
        ?int $destinationIndex = null,
        ?string $format = null,
        ?string $valueFormat = null,
        ?string $fixedValue = null,
        ?array $options = null
    ) {
        $this->type = $type;
        $this->sourceField = $sourceField;
        $this->sourcePath = $sourcePath;
        $this->sourceIndex = $sourceIndex;
        $this->destinationField = $destinationField;
        $this->destinationPath = $destinationPath;
        $this->destinationIndex = $destinationIndex;
        $this->format = $format;
        $this->valueFormat = $valueFormat;
        $this->fixedValue = $fixedValue;
        $this->options = $options;
    }

    public static function fromArray(array $data): self
    {
        // Validações básicas
        if (!isset($data['type'])) {
            throw new \InvalidArgumentException('O tipo da regra é obrigatório');
        }

        if (!isset($data['destination_field'])) {
            throw new \InvalidArgumentException('O campo de destino é obrigatório');
        }

        // Para regras do tipo field_mapping, o campo de origem é obrigatório
        if ($data['type'] === self::TYPE_FIELD_MAPPING && !isset($data['source_field']) && !isset($data['source_path'])) {
            throw new \InvalidArgumentException('Para mapeamento de campo, source_field ou source_path é obrigatório');
        }

        // Para valores fixos, o fixedValue é obrigatório
        if ($data['type'] === self::TYPE_FIXED_VALUE && !isset($data['fixed_value'])) {
            throw new \InvalidArgumentException('Para valores fixos, fixed_value é obrigatório');
        }

        return new self(
            $data['type'],
            $data['source_field'] ?? null,
            $data['source_path'] ?? null,
            $data['source_index'] ?? null,
            $data['destination_field'],
            $data['destination_path'] ?? null,
            $data['destination_index'] ?? null,
            $data['format'] ?? null,
            $data['value_format'] ?? null,
            $data['fixed_value'] ?? null,
            $data['options'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'source_field' => $this->sourceField,
            'source_path' => $this->sourcePath,
            'source_index' => $this->sourceIndex,
            'destination_field' => $this->destinationField,
            'destination_path' => $this->destinationPath,
            'destination_index' => $this->destinationIndex,
            'format' => $this->format,
            'value_format' => $this->valueFormat,
            'fixed_value' => $this->fixedValue,
            'options' => $this->options,
        ];
    }
}

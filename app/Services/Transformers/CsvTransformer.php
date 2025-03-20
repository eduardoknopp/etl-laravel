<?php

namespace App\Services\Transformers;

use League\Csv\Reader;
use League\Csv\Statement;

class CsvTransformer implements TransformerInterface
{
    /**
     * Transforma os dados do CSV de acordo com as regras de mapeamento fornecidas
     *
     * @param array $data Os dados do arquivo CSV (geralmente o caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato desejado
     */
    public function transform(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'] ?? null;
        
        if (!$filePath || !file_exists($filePath)) {
            throw new \InvalidArgumentException('Arquivo CSV inválido ou não encontrado');
        }

        // Ler o arquivo CSV
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0); // A primeira linha contém os cabeçalhos
        
        // Ler os registros
        $records = Statement::create()->process($csv);
        
        // Array para armazenar os dados transformados
        $transformedData = [];
        
        // Processar cada linha do CSV
        foreach ($records as $record) {
            $transformedRow = [];
            
            // Aplicar as regras de transformação para cada campo
            foreach ($rules as $targetField => $sourceField) {
                // Se a regra é um array, ela pode conter mais lógica como formatação
                if (is_array($sourceField)) {
                    $fieldName = $sourceField['field'] ?? null;
                    $format = $sourceField['format'] ?? null;
                    
                    $value = $record[$fieldName] ?? null;
                    
                    // Aplicar formatação se especificada
                    if ($format && $value !== null) {
                        $value = $this->applyFormat($value, $format);
                    }
                    
                    $transformedRow[$targetField] = $value;
                } else {
                    // Regra simples: apenas mapear campo de origem para destino
                    $transformedRow[$targetField] = $record[$sourceField] ?? null;
                }
            }
            
            $transformedData[] = $transformedRow;
        }
        
        // Retornar os dados transformados como JSON
        return json_encode($transformedData, JSON_PRETTY_PRINT);
    }
    
    /**
     * Aplica formatação ao valor de acordo com o formato especificado
     *
     * @param mixed $value O valor a ser formatado
     * @param string $format O formato a ser aplicado
     * @return mixed O valor formatado
     */
    private function applyFormat($value, string $format)
    {
        switch ($format) {
            case 'uppercase':
                return strtoupper($value);
            case 'lowercase':
                return strtolower($value);
            case 'date':
                $date = \DateTime::createFromFormat('Y-m-d', $value);
                return $date ? $date->format('d/m/Y') : $value;
            case 'number':
                return is_numeric($value) ? number_format((float)$value, 2, ',', '.') : $value;
            default:
                return $value;
        }
    }
} 
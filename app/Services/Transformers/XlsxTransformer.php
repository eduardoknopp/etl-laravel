<?php

namespace App\Services\Transformers;

use Spatie\SimpleExcel\SimpleExcelReader;

class XlsxTransformer implements TransformerInterface
{
    /**
     * Transforma os dados do XLSX de acordo com as regras de mapeamento fornecidas
     *
     * @param array $data Os dados do arquivo XLSX (geralmente o caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato desejado
     */
    public function transform(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'] ?? null;
        
        if (!$filePath || !file_exists($filePath)) {
            throw new \InvalidArgumentException('Arquivo XLSX inválido ou não encontrado');
        }

        // Ler o arquivo Excel
        $reader = SimpleExcelReader::create($filePath);
        
        // Array para armazenar os dados transformados
        $transformedData = [];
        
        // Processar cada linha do Excel
        foreach ($reader->getRows() as $row) {
            $transformedRow = [];
            
            // Aplicar as regras de transformação para cada campo
            foreach ($rules as $targetField => $sourceField) {
                // Se a regra é um array, ela pode conter mais lógica como formatação
                if (is_array($sourceField)) {
                    $fieldName = $sourceField['field'] ?? null;
                    $format = $sourceField['format'] ?? null;
                    
                    $value = $row[$fieldName] ?? null;
                    
                    // Aplicar formatação se especificada
                    if ($format && $value !== null) {
                        $value = $this->applyFormat($value, $format);
                    }
                    
                    $transformedRow[$targetField] = $value;
                } else {
                    // Regra simples: apenas mapear campo de origem para destino
                    $transformedRow[$targetField] = $row[$sourceField] ?? null;
                }
            }
            
            $transformedData[] = $transformedRow;
        }
        
        // Fechar o leitor para liberar recursos
        $reader->close();
        
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
                // Excel normalmente armazena datas como números seriais
                if (is_numeric($value)) {
                    // Converte timestamp Excel para data PHP (Excel começa em 01/01/1900)
                    $unixTimestamp = ($value - 25569) * 86400;
                    return date('d/m/Y', $unixTimestamp);
                }
                $date = \DateTime::createFromFormat('Y-m-d', $value);
                return $date ? $date->format('d/m/Y') : $value;
            case 'number':
                return is_numeric($value) ? number_format((float)$value, 2, ',', '.') : $value;
            default:
                return $value;
        }
    }
} 
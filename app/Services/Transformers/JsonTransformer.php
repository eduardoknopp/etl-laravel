<?php

namespace App\Services\Transformers;

class JsonTransformer implements TransformerInterface
{
    /**
     * Transforma os dados JSON de acordo com as regras de mapeamento fornecidas
     *
     * @param array $data Os dados do arquivo JSON (geralmente o caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato desejado
     */
    public function transform(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'] ?? null;
        
        if (!$filePath || !file_exists($filePath)) {
            throw new \InvalidArgumentException('Arquivo JSON inválido ou não encontrado');
        }

        // Ler o conteúdo do arquivo JSON
        $jsonContent = file_get_contents($filePath);
        $sourceData = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Erro ao decodificar JSON: ' . json_last_error_msg());
        }
        
        // Determinar se estamos lidando com um array de objetos ou um objeto único
        if (!is_array($sourceData) || !isset($sourceData[0])) {
            // É um objeto único, converter para array para processamento uniforme
            $sourceData = [$sourceData];
        }
        
        // Array para armazenar os dados transformados
        $transformedData = [];
        
        // Processar cada item do JSON
        foreach ($sourceData as $item) {
            $transformedItem = [];
            
            // Aplicar as regras de transformação para cada campo
            foreach ($rules as $targetField => $sourceField) {
                // Verificar se é uma regra complexa
                if (is_array($sourceField)) {
                    // Suporta caminhos aninhados com notação de ponto
                    if (isset($sourceField['path'])) {
                        $path = $sourceField['path'];
                        $value = $this->getNestedValue($item, $path);
                        
                        // Aplicar formatação se necessário
                        if (isset($sourceField['format']) && $value !== null) {
                            $value = $this->applyFormat($value, $sourceField['format']);
                        }
                        
                        $transformedItem[$targetField] = $value;
                    }
                } else {
                    // Regra simples: pode ser um caminho com notação de ponto
                    $transformedItem[$targetField] = $this->getNestedValue($item, $sourceField);
                }
            }
            
            $transformedData[] = $transformedItem;
        }
        
        // Retornar os dados transformados como JSON
        return json_encode($transformedData, JSON_PRETTY_PRINT);
    }
    
    /**
     * Obtém um valor aninhado de um array usando notação de ponto
     * Ex: "user.address.street" retornará o valor de $array['user']['address']['street']
     *
     * @param array $array O array de origem
     * @param string $path O caminho para o valor usando notação de ponto
     * @return mixed O valor encontrado ou null se não existir
     */
    private function getNestedValue(array $array, string $path)
    {
        $keys = explode('.', $path);
        $value = $array;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }
        
        return $value;
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
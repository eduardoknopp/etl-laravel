<?php

namespace App\Services\Transformers;

use App\DTOs\TransformationMapRule;

class JsonTransformer implements TransformerInterface
{
    /**
     * Transforma os dados JSON de acordo com as regras de mapeamento fornecidas
     *
     * @param array $data Os dados do arquivo JSON (geralmente o caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato JSON
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
        
        // Obter o template
        $template = JsonTemplates::getTemplate($templateName);
        
        // Determinar se estamos lidando com um array de objetos ou um objeto único
        if (!is_array($sourceData) || (!$this->isAssocArray($sourceData) && !isset($sourceData[0]))) {
            // É um objeto único, converter para array para processamento uniforme
            $sourceData = [$sourceData];
        } else if ($this->isAssocArray($sourceData)) {
            // É um objeto associativo, não um array de objetos
            $sourceData = [$sourceData];
        }
        
        // Estrutura de saída
        $outputStructure = [];
        
        // Verificar se o template tem uma propriedade de empacotamento (wrapper)
        if (isset($template['root_property'])) {
            // Criar a propriedade raiz
            $outputStructure[$template['root_property']] = [];
            
            // Processar cada item dos dados de origem
            $transformedItems = $this->processItems($sourceData, $rules, $template);
            
            // Adicionar itens à estrutura raiz
            $outputStructure[$template['root_property']] = $transformedItems;
            
            // Adicionar metadados se definidos no template
            if (isset($template['metadata']) && is_array($template['metadata'])) {
                foreach ($template['metadata'] as $metaKey => $metaValue) {
                    if (is_array($metaValue) && isset($metaValue['field'])) {
                        // Se o metadado referencia um campo nos dados, obter o valor dinamicamente
                        $outputStructure[$metaKey] = $this->getValueFromRules(
                            $rules, 
                            $metaValue['field'], 
                            $sourceData[0] ?? []
                        );
                    } else {
                        // Valor fixo
                        $outputStructure[$metaKey] = $metaValue;
                    }
                }
            }
        } else {
            // Sem wrapper, retornar diretamente o array de itens transformados
            $outputStructure = $this->processItems($sourceData, $rules, $template);
        }
        
        // Retornar os dados transformados como JSON
        return json_encode($outputStructure, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Processa os itens de dados de acordo com as regras e o template
     *
     * @param array $items Os itens de dados a serem processados
     * @param array $rules As regras de transformação
     * @param array $template O template a ser usado
     * @return array Os itens transformados
     */
    private function processItems(array $items, array $rules, array $template): array
    {
        $transformedItems = [];
        
        // Estrutura para cada item
        $itemStructure = $template['item_structure'] ?? ['fields' => []];
        
        foreach ($items as $item) {
            $transformedItem = [];
            
            // Processar campos explícitos definidos no template
            if (isset($itemStructure['fields']) && is_array($itemStructure['fields'])) {
                foreach ($itemStructure['fields'] as $field) {
                    if (is_string($field)) {
                        // Campo simples
                        $transformedItem[$field] = $this->getValueFromRules($rules, $field, $item);
                    } else if (is_array($field) && isset($field['source']) && isset($field['target'])) {
                        // Mapeamento de campo com fonte e destino específicos
                        $value = $this->getValueFromRules($rules, $field['source'], $item);
                        $transformedItem[$field['target']] = $value;
                    }
                }
            } else {
                // Se não houver campos explícitos, tentar aplicar todas as regras disponíveis
                if (isset($rules['mappings']) && is_array($rules['mappings'])) {
                    foreach ($rules['mappings'] as $rule) {
                        if ($rule instanceof TransformationMapRule) {
                            $destinationField = $rule->destinationField;
                            $transformedItem[$destinationField] = $this->applyRule($rule, $item);
                        }
                    }
                }
            }
            
            // Processar subestruturas (objetos aninhados)
            if (isset($itemStructure['nested_objects']) && is_array($itemStructure['nested_objects'])) {
                foreach ($itemStructure['nested_objects'] as $nestedKey => $nestedConfig) {
                    // Obter os dados de origem para o objeto aninhado
                    $nestedSourceKey = $nestedConfig['source'] ?? $nestedKey;
                    $nestedData = isset($item[$nestedSourceKey]) && is_array($item[$nestedSourceKey]) 
                        ? $item[$nestedSourceKey] 
                        : [];
                    
                    // Criar estrutura para o objeto aninhado
                    $transformedItem[$nestedKey] = [];
                    
                    // Processar cada campo do objeto aninhado
                    if (isset($nestedConfig['fields']) && is_array($nestedConfig['fields'])) {
                        foreach ($nestedConfig['fields'] as $field) {
                            if (is_string($field)) {
                                // Tentar obter o valor diretamente das regras
                                $fullFieldPath = $nestedKey . '.' . $field;
                                $transformedItem[$nestedKey][$field] = $this->getValueFromRules($rules, $fullFieldPath, $item);
                            }
                        }
                    }
                    
                    // Se configurado para representar um array de objetos
                    if (isset($nestedConfig['is_array']) && $nestedConfig['is_array'] && is_array($nestedData)) {
                        $transformedItem[$nestedKey] = [];
                        
                        // Processar cada item no array
                        foreach ($nestedData as $nestedItem) {
                            $processedNestedItem = [];
                            
                            if (isset($nestedConfig['fields']) && is_array($nestedConfig['fields'])) {
                                foreach ($nestedConfig['fields'] as $field) {
                                    if (is_string($field)) {
                                        $processedNestedItem[$field] = isset($nestedItem[$field]) 
                                            ? $nestedItem[$field] 
                                            : null;
                                    }
                                }
                            }
                            
                            $transformedItem[$nestedKey][] = $processedNestedItem;
                        }
                    }
                }
            }
            
            $transformedItems[] = $transformedItem;
        }
        
        return $transformedItems;
    }
    
    /**
     * Obtém um valor para um campo de destino com base nas regras de transformação
     *
     * @param array $rules As regras de transformação
     * @param string $targetField O campo de destino
     * @param array $sourceData Os dados da origem
     * @return mixed O valor transformado ou null se não encontrado
     */
    private function getValueFromRules(array $rules, string $targetField, array $sourceData)
    {
        // Procurar nas regras de mapeamento
        if (isset($rules['mappings']) && is_array($rules['mappings'])) {
            foreach ($rules['mappings'] as $rule) {
                if ($rule instanceof TransformationMapRule && $rule->destinationField === $targetField) {
                    return $this->applyRule($rule, $sourceData);
                }
                // Para compatibilidade com regras em formato array (legado)
                else if (is_array($rule) && 
                        (isset($rule['destination_field']) && $rule['destination_field'] === $targetField)) {
                    return $sourceData[$rule['source_field']] ?? null;
                }
            }
        }
        
        // Se não encontrou regra específica, verificar se há um campo com o mesmo nome
        // Para campos aninhados, tentar buscar usando a notação de ponto
        if (strpos($targetField, '.') !== false) {
            return $this->getNestedValue($sourceData, $targetField);
        }
        
        return $sourceData[$targetField] ?? null;
    }
    
    /**
     * Aplica uma regra de transformação a uma linha de dados
     *
     * @param TransformationMapRule $rule A regra a ser aplicada
     * @param array $sourceData Os dados da origem
     * @return mixed O valor transformado
     */
    private function applyRule(TransformationMapRule $rule, array $sourceData)
    {
        switch ($rule->type) {
            case TransformationMapRule::TYPE_FIELD_MAPPING:
                $value = $this->getSourceValue($rule, $sourceData);
                return $this->formatValue($value, $rule->format, $rule->valueFormat);
                
            case TransformationMapRule::TYPE_FIXED_VALUE:
                return $rule->fixedValue;
                
            case TransformationMapRule::TYPE_FORMULA:
                // Implementar processamento de fórmula
                return $this->processFormula($rule, $sourceData);
                
            case TransformationMapRule::TYPE_CONCAT:
                // Implementar concatenação de campos
                return $this->processConcat($rule, $sourceData);
                
            case TransformationMapRule::TYPE_DATE_TRANSFORM:
                $value = $this->getSourceValue($rule, $sourceData);
                return $this->formatDate($value, $rule->format, $rule->sourceFormat);
                
            case TransformationMapRule::TYPE_CONDITIONAL:
                // Implementar condicionais
                return $this->processConditional($rule, $sourceData);
                
            default:
                return null;
        }
    }
    
    /**
     * Obtém o valor do campo de origem usando o sourceField ou sourcePath
     *
     * @param TransformationMapRule $rule A regra contendo o campo de origem
     * @param array $sourceData Os dados da origem
     * @return mixed O valor do campo de origem
     */
    private function getSourceValue(TransformationMapRule $rule, array $sourceData)
    {
        // Se tiver índice de origem específico
        if ($rule->sourceIndex !== null && isset($sourceData[$rule->sourceIndex])) {
            return $sourceData[$rule->sourceIndex];
        }
        
        // Se tiver caminho aninhado
        if ($rule->sourcePath !== null) {
            return $this->getNestedValue($sourceData, $rule->sourcePath);
        }
        
        // Campo simples
        if ($rule->sourceField !== null && isset($sourceData[$rule->sourceField])) {
            return $sourceData[$rule->sourceField];
        }
        
        return null;
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
     * Aplica formatação ao valor
     *
     * @param mixed $value O valor a ser formatado
     * @param string|null $format O formato a ser aplicado
     * @param string|null $valueFormat O formato de dados
     * @return mixed O valor formatado
     */
    private function formatValue($value, ?string $format, ?string $valueFormat)
    {
        if ($value === null) {
            return '';
        }
        
        if ($format === null) {
            return $value;
        }
        
        switch ($format) {
            case 'uppercase':
                return strtoupper($value);
            case 'lowercase':
                return strtolower($value);
            case 'capitalize':
                return ucwords(strtolower($value));
            case 'trim':
                return trim($value);
            default:
                return $value;
        }
    }
    
    /**
     * Formata um valor de data
     *
     * @param mixed $value O valor a ser formatado
     * @param string|null $format O formato de data desejado
     * @param string|null $sourceFormat O formato da data de origem
     * @return string A data formatada
     */
    private function formatDate($value, ?string $format, ?string $sourceFormat)
    {
        if ($value === null) {
            return '';
        }
        
        // Se o formato de origem for especificado
        if ($sourceFormat) {
            $date = \DateTime::createFromFormat($sourceFormat, $value);
            if ($date) {
                return $date->format($format ?? 'Y-m-d');
            }
        }
        
        // Tentar assumir formatos comuns
        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d'] as $possibleFormat) {
            $date = \DateTime::createFromFormat($possibleFormat, $value);
            if ($date) {
                return $date->format($format ?? 'Y-m-d');
            }
        }
        
        return $value;
    }
    
    /**
     * Processa uma fórmula ou expressão
     *
     * @param TransformationMapRule $rule A regra com a fórmula
     * @param array $sourceData Os dados da origem
     * @return mixed O resultado da fórmula
     */
    private function processFormula(TransformationMapRule $rule, array $sourceData)
    {
        // Implementação básica - pode ser expandida para suportar fórmulas mais complexas
        if (!isset($rule->options['formula'])) {
            return null;
        }
        
        $formula = $rule->options['formula'];
        
        // Substituir variáveis na fórmula
        preg_match_all('/\{\{(.*?)\}\}/', $formula, $matches);
        
        if (isset($matches[1])) {
            foreach ($matches[1] as $match) {
                $value = $sourceData[$match] ?? '';
                $formula = str_replace('{{'.$match.'}}', $value, $formula);
            }
        }
        
        // Avaliar a fórmula - ATENÇÃO: eval pode ser perigoso em ambiente de produção!
        // Esta é uma implementação simplificada para demonstração
        // Em produção, use uma biblioteca segura de avaliação de expressões matemáticas
        try {
            $result = eval('return ' . $formula . ';');
            return $result;
        } catch (\Throwable $e) {
            return 'Erro na fórmula: ' . $e->getMessage();
        }
    }
    
    /**
     * Processa concatenação de campos
     *
     * @param TransformationMapRule $rule A regra com configuração de concatenação
     * @param array $sourceData Os dados da origem
     * @return string O resultado da concatenação
     */
    private function processConcat(TransformationMapRule $rule, array $sourceData)
    {
        if (!isset($rule->options['fields']) || !is_array($rule->options['fields'])) {
            return '';
        }
        
        $separator = $rule->options['separator'] ?? ' ';
        $result = [];
        
        foreach ($rule->options['fields'] as $field) {
            $value = $sourceData[$field] ?? '';
            if ($value !== '') {
                $result[] = $value;
            }
        }
        
        return implode($separator, $result);
    }
    
    /**
     * Processa regras condicionais
     *
     * @param TransformationMapRule $rule A regra com condições
     * @param array $sourceData Os dados da origem
     * @return mixed O valor resultante da condição
     */
    private function processConditional(TransformationMapRule $rule, array $sourceData)
    {
        if (!isset($rule->options['conditions']) || !is_array($rule->options['conditions'])) {
            return $rule->options['default'] ?? '';
        }
        
        $sourceValue = $this->getSourceValue($rule, $sourceData);
        
        foreach ($rule->options['conditions'] as $condition) {
            if (!isset($condition['condition']) || !isset($condition['value'])) {
                continue;
            }
            
            $conditionStr = str_replace('?', $sourceValue, $condition['condition']);
            
            // Avaliar a condição - ATENÇÃO: eval pode ser perigoso em ambiente de produção!
            // Esta é uma implementação simplificada para demonstração
            try {
                $result = eval('return ' . $conditionStr . ';');
                if ($result) {
                    return $condition['value'];
                }
            } catch (\Throwable $e) {
                // Ignorar erro e continuar para a próxima condição
            }
        }
        
        return $rule->options['default'] ?? '';
    }
    
    /**
     * Verifica se um array é associativo
     *
     * @param array $array O array a ser verificado
     * @return bool true se for associativo, false caso contrário
     */
    private function isAssocArray(array $array): bool
    {
        if (empty($array)) return false;
        return array_keys($array) !== range(0, count($array) - 1);
    }
} 
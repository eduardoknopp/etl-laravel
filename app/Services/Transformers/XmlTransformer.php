<?php

namespace App\Services\Transformers;

use App\DTOs\TransformationMapRule;

class XmlTransformer implements TransformerInterface
{
    /**
     * Transforma os dados de origem de acordo com as regras de mapeamento
     * e os templates fornecidos
     *
     * @param array $data Os dados a serem transformados (pode ser caminho de arquivo ou dados estruturados)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato XML
     */
    public function transform(array $data, array $rules, string $templateName): string
    {
        // Obtém o template XML
        $template = XmlTemplates::getTemplate($templateName);
        $rootElement = $template['root_element'];
        $xmlDeclaration = $template['declaration'] ?? '<?xml version="1.0" encoding="UTF-8"?>';
        
        // Preparar documento XML
        $output = $xmlDeclaration . "\n";
        
        // Adicionar namespace se especificado
        $namespaceAttr = '';
        if (!empty($template['namespaces'])) {
            foreach ($template['namespaces'] as $prefix => $uri) {
                $prefix = ($prefix === 'default') ? '' : "xmlns:{$prefix}";
                $prefix = ($prefix === '') ? 'xmlns' : $prefix;
                $namespaceAttr .= " {$prefix}=\"{$uri}\"";
            }
        }
        
        // Adicionar atributos se especificados
        $attributesStr = '';
        if (!empty($template['attributes'])) {
            foreach ($template['attributes'] as $name => $value) {
                $attributesStr .= " {$name}=\"{$value}\"";
            }
        }
        
        // Abrir tag raiz
        $output .= "<{$rootElement}{$namespaceAttr}{$attributesStr}>\n";
        
        // Processar estrutura principal
        $xmlStructure = $this->processXmlStructure($template['structure'], $data, $rules);
        $output .= $xmlStructure;
        
        // Processar footer se existir
        if (isset($template['footer']) && !empty($template['footer'])) {
            $footerXml = $this->processFooter($template['footer'], $data, $rules);
            $output .= $footerXml;
        }
        
        // Fechar tag raiz
        $output .= "</{$rootElement}>\n";
        
        return $output;
    }
    
    /**
     * Processa a estrutura XML com base no template e regras
     *
     * @param array $structure A estrutura do XML definida no template
     * @param array $data Os dados a serem transformados
     * @param array $rules As regras de transformação
     * @return string O XML processado
     */
    private function processXmlStructure(array $structure, array $data, array $rules): string
    {
        $output = '';
        
        // Se for uma estrutura simples com elementos definidos
        if (isset($structure['elements'])) {
            foreach ($structure['elements'] as $element) {
                $output .= $this->processElement($element, $data, $rules);
            }
        }
        
        // Se tiver uma seção para iterar sobre dados
        if (isset($structure['items']) && isset($structure['items']['source'])) {
            $sourceData = $data[$structure['items']['source']] ?? $data;
            
            if (!is_array($sourceData)) {
                $sourceData = [$sourceData];
            }
            
            foreach ($sourceData as $item) {
                $itemOutput = '';
                
                if (isset($structure['items']['wrapper'])) {
                    $itemOutput .= "<{$structure['items']['wrapper']}>\n";
                }
                
                foreach ($structure['items']['elements'] as $element) {
                    $itemOutput .= $this->processElement($element, $item, $rules);
                }
                
                if (isset($structure['items']['wrapper'])) {
                    $itemOutput .= "</{$structure['items']['wrapper']}>\n";
                }
                
                $output .= $itemOutput;
            }
        }
        
        return $output;
    }
    
    /**
     * Processa um elemento XML individual
     *
     * @param array $element Definição do elemento
     * @param array $data Os dados a serem usados
     * @param array $rules As regras de transformação
     * @return string O elemento XML processado
     */
    private function processElement(array $element, array $data, array $rules): string
    {
        $name = $element['name'] ?? '';
        
        if (empty($name)) {
            return '';
        }
        
        $output = '';
        
        // Processar atributos
        $attributes = '';
        if (isset($element['attributes'])) {
            foreach ($element['attributes'] as $attrName => $attrSource) {
                if (is_array($attrSource) && isset($attrSource['field'])) {
                    $value = $this->getValueFromRules($rules, $attrSource['field'], $data);
                    if ($value !== null) {
                        $attributes .= " {$attrName}=\"" . htmlspecialchars($value) . "\"";
                    }
                } else {
                    $value = $this->getValueFromRules($rules, $attrSource, $data);
                    if ($value !== null) {
                        $attributes .= " {$attrName}=\"" . htmlspecialchars($value) . "\"";
                    }
                }
            }
        }
        
        // Processar conteúdo
        if (isset($element['content'])) {
            if (is_array($element['content'])) {
                // Elemento com subelementos
                $output .= "<{$name}{$attributes}>\n";
                
                foreach ($element['content'] as $subElement) {
                    $output .= $this->processElement($subElement, $data, $rules);
                }
                
                $output .= "</{$name}>\n";
            } else if (is_string($element['content']) && strpos($element['content'], '{{') !== false) {
                // Elemento com placeholder
                $fieldName = str_replace(['{{', '}}'], '', $element['content']);
                $value = $this->getValueFromRules($rules, $fieldName, $data);
                
                if ($value !== null) {
                    $output .= "<{$name}{$attributes}>" . htmlspecialchars($value) . "</{$name}>\n";
                } else {
                    // Se o valor for nulo, verificar se há um valor padrão
                    $defaultValue = $element['default'] ?? '';
                    $output .= "<{$name}{$attributes}>" . htmlspecialchars($defaultValue) . "</{$name}>\n";
                }
            } else {
                // Elemento com conteúdo fixo
                $output .= "<{$name}{$attributes}>" . htmlspecialchars($element['content']) . "</{$name}>\n";
            }
        } else if (isset($element['field'])) {
            // Elemento que corresponde diretamente a um campo
            $value = $this->getValueFromRules($rules, $element['field'], $data);
            
            if ($value !== null) {
                $output .= "<{$name}{$attributes}>" . htmlspecialchars($value) . "</{$name}>\n";
            } else if (isset($element['default'])) {
                $output .= "<{$name}{$attributes}>" . htmlspecialchars($element['default']) . "</{$name}>\n";
            } else {
                // Elemento vazio
                $output .= "<{$name}{$attributes} />\n";
            }
        } else {
            // Elemento vazio
            $output .= "<{$name}{$attributes} />\n";
        }
        
        return $output;
    }
    
    /**
     * Processa o footer do XML
     *
     * @param array $footer A estrutura do footer
     * @param array $data Os dados a serem transformados
     * @param array $rules As regras de transformação
     * @return string O XML do footer
     */
    private function processFooter(array $footer, array $data, array $rules): string
    {
        $output = '';
        
        if (isset($footer['elements'])) {
            foreach ($footer['elements'] as $element) {
                $output .= $this->processElement($element, $data, $rules);
            }
        }
        
        return $output;
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
}

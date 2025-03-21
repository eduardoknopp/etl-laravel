<?php

namespace App\Services\Transformers;

use App\DTOs\TransformationMapRule;

abstract class AbstractTransformer implements TransformerInterface
{
    /**
     * Transforma os dados do arquivo de origem de acordo com as regras de mapeamento
     * e os templates fornecidos
     *
     * @param array $data Os dados do arquivo origem (geralmente o caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    public function transform(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'] ?? null;
        
        if (!$filePath || !file_exists($filePath)) {
            throw new \InvalidArgumentException('Arquivo de origem inválido ou não encontrado');
        }

        $sourceFormat = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $outputFormat = $this->getOutputFormat();
        
        // Direcionar para o método de transformação específico baseado no formato de origem
        return match ($sourceFormat) {
            $outputFormat => $this->transformSameFormat($data, $rules, $templateName),
            'json' => $this->transformJsonToFormat($data, $rules, $templateName),
            'xml' => $this->transformXmlToFormat($data, $rules, $templateName),
            'csv' => $this->transformCsvToFormat($data, $rules, $templateName),
            'xlsx', 'xls' => $this->transformXlsxToFormat($data, $rules, $templateName),
            default => throw new \InvalidArgumentException("Transformação de '$sourceFormat' para '$outputFormat' não suportada"),
        };
    }
    
    /**
     * Retorna o formato de saída do transformador
     *
     * @return string O formato de saída (json, xml, csv, xlsx)
     */
    abstract protected function getOutputFormat(): string;
    
    /**
     * Transforma um arquivo do mesmo formato para o mesmo formato
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    abstract protected function transformSameFormat(array $data, array $rules, string $templateName): string;
    
    /**
     * Transforma um arquivo JSON para o formato de saída
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    abstract protected function transformJsonToFormat(array $data, array $rules, string $templateName): string;
    
    /**
     * Transforma um arquivo XML para o formato de saída
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    abstract protected function transformXmlToFormat(array $data, array $rules, string $templateName): string;
    
    /**
     * Transforma um arquivo CSV para o formato de saída
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    abstract protected function transformCsvToFormat(array $data, array $rules, string $templateName): string;
    
    /**
     * Transforma um arquivo XLSX para o formato de saída
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    abstract protected function transformXlsxToFormat(array $data, array $rules, string $templateName): string;
    
    /**
     * Aplica estilos aos cabeçalhos de uma planilha
     *
     * @param mixed $headerStyle O estilo a ser aplicado
     * @param array $styles Configuração de estilos
     * @return void
     */
    protected function applyHeaderStyles($headerStyle, array $styles): void
    {
        if (isset($styles['font'])) {
            $font = $headerStyle->getFont();
            if (isset($styles['font']['bold'])) {
                $font->setBold($styles['font']['bold']);
            }
            if (isset($styles['font']['size'])) {
                $font->setSize($styles['font']['size']);
            }
        }
        
        if (isset($styles['fill'])) {
            $fill = $headerStyle->getFill();
            $fill->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            if (isset($styles['fill']['color'])) {
                $fill->getStartColor()->setRGB(ltrim($styles['fill']['color'], '#'));
            }
        }
        
        if (isset($styles['font_color'])) {
            $font = $headerStyle->getFont();
            $font->getColor()->setRGB(ltrim($styles['font_color'], '#'));
        }
    }

    /**
     * Converte um objeto SimpleXMLElement para array
     * 
     * @param \SimpleXMLElement $xml O objeto XML a ser convertido
     * @return array O array resultante
     */
    protected function xmlToArray(\SimpleXMLElement $xml): array
    {
        $result = [];
        
        // Determinar a estrutura do XML para extrair os dados
        // Tenta identificar o elemento que contém as linhas de dados
        $rootNode = $xml->getName();
        
        // Se for um documento XML simples com um elemento raiz contendo itens
        if (count($xml->children()) > 0) {
            $children = $xml->children();
            $childName = $children[0]->getName();
            $items = $xml->{$childName};
            
            foreach ($items as $item) {
                $row = [];
                foreach ($item as $key => $value) {
                    $row[(string)$key] = (string)$value;
                }
                $result[] = $row;
            }
        } else {
            // Se for um documento XML sem uma estrutura clara, tenta extrair os dados diretamente
            $row = [];
            foreach ($xml as $key => $value) {
                $row[(string)$key] = (string)$value;
            }
            $result[] = $row;
        }
        
        return $result;
    }

    /**
     * Obtém um valor para um campo de destino com base nas regras de transformação
     *
     * @param array $rules As regras de transformação
     * @param string $targetField O campo de destino
     * @param array $sourceRow Os dados da linha de origem
     * @return mixed O valor transformado
     */
    protected function getValueFromRules(array $rules, string $targetField, array $sourceRow)
    {
        // Procurar nas regras de mapeamento
        if (isset($rules['mappings']) && is_array($rules['mappings'])) {
            foreach ($rules['mappings'] as $rule) {
                if ($rule->destinationField === $targetField) {
                    return $this->applyRule($rule, $sourceRow);
                }
            }
        }
        
        // Se não encontrou regra específica, verificar se há um campo com o mesmo nome
        return $sourceRow[$targetField] ?? '';
    }
    
    /**
     * Aplica uma regra de transformação a uma linha de dados
     *
     * @param TransformationMapRule $rule A regra a ser aplicada
     * @param array $sourceRow Os dados da linha de origem
     * @return mixed O valor transformado
     */
    protected function applyRule(TransformationMapRule $rule, array $sourceRow)
    {
        switch ($rule->type) {
            case TransformationMapRule::TYPE_FIELD_MAPPING:
                $value = $this->getSourceValue($rule, $sourceRow);
                return $this->formatValue($value, $rule->format, $rule->valueFormat);
                
            case TransformationMapRule::TYPE_FIXED_VALUE:
                return $rule->fixedValue;
                
            case TransformationMapRule::TYPE_FORMULA:
                // Implementar processamento de fórmula
                return $this->processFormula($rule, $sourceRow);
                
            case TransformationMapRule::TYPE_CONCAT:
                // Implementar concatenação de campos
                return $this->processConcat($rule, $sourceRow);
                
            case TransformationMapRule::TYPE_DATE_TRANSFORM:
                $value = $this->getSourceValue($rule, $sourceRow);
                return $this->formatDate($value, $rule->format);
                
            case TransformationMapRule::TYPE_CONDITIONAL:
                // Implementar condicionais
                return $this->processConditional($rule, $sourceRow);
                
            default:
                return null;
        }
    }
    
    /**
     * Obtém o valor do campo de origem usando o sourceField ou sourcePath
     *
     * @param TransformationMapRule $rule A regra contendo o campo de origem
     * @param array $sourceRow Os dados da linha de origem
     * @return mixed O valor do campo de origem
     */
    protected function getSourceValue(TransformationMapRule $rule, array $sourceRow)
    {
        // Se tiver índice de origem específico
        if ($rule->sourceIndex !== null && isset($sourceRow[$rule->sourceIndex])) {
            return $sourceRow[$rule->sourceIndex];
        }
        
        // Se tiver caminho aninhado
        if ($rule->sourcePath !== null) {
            return $this->getNestedValue($sourceRow, $rule->sourcePath);
        }
        
        // Campo simples
        if ($rule->sourceField !== null && isset($sourceRow[$rule->sourceField])) {
            return $sourceRow[$rule->sourceField];
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
    protected function getNestedValue(array $array, string $path)
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
    protected function formatValue($value, ?string $format, ?string $valueFormat)
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
     * @return string A data formatada
     */
    protected function formatDate($value, ?string $format)
    {
        if ($value === null) {
            return '';
        }
        
        // Excel normalmente armazena datas como números seriais
        if (is_numeric($value)) {
            // Converte timestamp Excel para data PHP (Excel começa em 01/01/1900)
            $unixTimestamp = ($value - 25569) * 86400;
            return date($format ?? 'd/m/Y', $unixTimestamp);
        }
        
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        if ($date) {
            return $date->format($format ?? 'd/m/Y');
        }
        
        return $value;
    }
    
    /**
     * Processa uma fórmula ou expressão
     *
     * @param TransformationMapRule $rule A regra com a fórmula
     * @param array $sourceRow Os dados da linha de origem
     * @return mixed O resultado da fórmula
     */
    protected function processFormula(TransformationMapRule $rule, array $sourceRow)
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
                $value = $sourceRow[$match] ?? '';
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
     * @param array $sourceRow Os dados da linha de origem
     * @return string O resultado da concatenação
     */
    protected function processConcat(TransformationMapRule $rule, array $sourceRow)
    {
        if (!isset($rule->options['fields']) || !is_array($rule->options['fields'])) {
            return '';
        }
        
        $separator = $rule->options['separator'] ?? ' ';
        $result = [];
        
        foreach ($rule->options['fields'] as $field) {
            $value = $sourceRow[$field] ?? '';
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
     * @param array $sourceRow Os dados da linha de origem
     * @return mixed O valor resultante da condição
     */
    protected function processConditional(TransformationMapRule $rule, array $sourceRow)
    {
        if (!isset($rule->options['conditions']) || !is_array($rule->options['conditions'])) {
            return $rule->options['default'] ?? '';
        }
        
        $sourceValue = $this->getSourceValue($rule, $sourceRow);
        
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
     * Converte um número de coluna para uma letra (A, B, C, ..., AA, AB, ...)
     *
     * @param int $columnNumber O número da coluna (começando em 1)
     * @return string A letra da coluna
     */
    protected function getColumnLetter(int $columnNumber): string
    {
        $columnString = '';
        
        while ($columnNumber > 0) {
            $modulo = ($columnNumber - 1) % 26;
            $columnString = chr(65 + $modulo) . $columnString;
            $columnNumber = (int)(($columnNumber - $modulo) / 26);
        }
        
        return $columnString;
    }
} 
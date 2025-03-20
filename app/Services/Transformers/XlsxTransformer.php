<?php

namespace App\Services\Transformers;

use App\DTOs\TransformationMapRule;
use Spatie\SimpleExcel\SimpleExcelReader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxTransformer implements TransformerInterface
{
    /**
     * Transforma os dados do arquivo de origem de acordo com as regras de mapeamento
     * e os templates fornecidos
     *
     * @param array $data Os dados do arquivo origem (geralmente o caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato XLSX
     */
    public function transform(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'] ?? null;
        
        if (!$filePath || !file_exists($filePath)) {
            throw new \InvalidArgumentException('Arquivo de origem inválido ou não encontrado');
        }

        // Carregar o template XLSX
        $template = XlsxTemplates::getTemplate($templateName);
        
        // Criar um novo spreadsheet
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($template['properties']['creator'] ?? 'Sistema ETL')
            ->setTitle($template['properties']['title'] ?? 'Arquivo Transformado')
            ->setDescription($template['properties']['description'] ?? 'Arquivo gerado pelo sistema ETL');
            
        // Remover a planilha padrão
        $spreadsheet->removeSheetByIndex(0);
        
        // Ler o arquivo de origem
        $reader = SimpleExcelReader::create($filePath);
        $sourceRows = $reader->getRows()->toArray();
        
        // Processar cada planilha definida no template
        $sheetIndex = 0;
        foreach ($template['sheets'] as $sheetName => $sheetConfig) {
            // Criar uma nova planilha
            $sheet = $spreadsheet->createSheet($sheetIndex);
            $sheet->setTitle($sheetName);
            
            // Adicionar cabeçalhos
            $columnIndex = 1;
            foreach ($sheetConfig['headers'] as $header) {
                $sheet->setCellValueByColumnAndRow($columnIndex++, 1, $header);
            }
            
            // Aplicar estilos aos cabeçalhos, se definidos
            if (isset($sheetConfig['styles']['headers'])) {
                $headerStyle = $sheet->getStyle('A1:' . $this->getColumnLetter(count($sheetConfig['headers'])) . '1');
                
                if (isset($sheetConfig['styles']['headers']['font'])) {
                    $font = $headerStyle->getFont();
                    if (isset($sheetConfig['styles']['headers']['font']['bold'])) {
                        $font->setBold($sheetConfig['styles']['headers']['font']['bold']);
                    }
                    if (isset($sheetConfig['styles']['headers']['font']['size'])) {
                        $font->setSize($sheetConfig['styles']['headers']['font']['size']);
                    }
                }
                
                if (isset($sheetConfig['styles']['headers']['fill'])) {
                    $fill = $headerStyle->getFill();
                    $fill->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    if (isset($sheetConfig['styles']['headers']['fill']['color'])) {
                        $fill->getStartColor()->setRGB(ltrim($sheetConfig['styles']['headers']['fill']['color'], '#'));
                    }
                }
                
                if (isset($sheetConfig['styles']['headers']['font_color'])) {
                    $font = $headerStyle->getFont();
                    $font->getColor()->setRGB(ltrim($sheetConfig['styles']['headers']['font_color'], '#'));
                }
            }
            
            // Processar cada linha de dados
            $rowIndex = 2; // Começando abaixo do cabeçalho
            
            foreach ($sourceRows as $sourceRow) {
                $columnIndex = 1;
                
                // Mapear cada coluna de acordo com as regras
                foreach ($sheetConfig['headers'] as $targetField) {
                    $value = $this->getValueFromRules($rules, $targetField, $sourceRow);
                    $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, $value);
                }
                
                $rowIndex++;
            }
            
            // Ajustar largura das colunas
            foreach (range('A', $this->getColumnLetter(count($sheetConfig['headers']))) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            $sheetIndex++;
        }
        
        // Salvar para o buffer de saída
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
    
    /**
     * Obtém um valor para um campo de destino com base nas regras de transformação
     *
     * @param array $rules As regras de transformação
     * @param string $targetField O campo de destino
     * @param array $sourceRow Os dados da linha de origem
     * @return mixed O valor transformado
     */
    private function getValueFromRules(array $rules, string $targetField, array $sourceRow)
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
    private function applyRule(TransformationMapRule $rule, array $sourceRow)
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
    private function getSourceValue(TransformationMapRule $rule, array $sourceRow)
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
     * @return string A data formatada
     */
    private function formatDate($value, ?string $format)
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
    private function processFormula(TransformationMapRule $rule, array $sourceRow)
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
    private function processConcat(TransformationMapRule $rule, array $sourceRow)
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
    private function processConditional(TransformationMapRule $rule, array $sourceRow)
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
    private function getColumnLetter(int $columnNumber): string
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
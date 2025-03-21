<?php

namespace App\Services\Transformers\Json;

use App\Services\Transformers\AbstractTransformer;

class JsonTransformer extends AbstractTransformer
{
    /**
     * Retorna o formato de saída do transformador
     *
     * @return string O formato de saída (json, xml, csv, xlsx)
     */
    protected function getOutputFormat(): string
    {
        return 'json';
    }
    
    /**
     * Transforma um arquivo JSON em outro arquivo JSON aplicando as regras
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    protected function transformSameFormat(array $data, array $rules, string $templateName): string
    {
        return $this->transformJsonToFormat($data, $rules, $templateName);
    }
    
    /**
     * Transforma um arquivo JSON para o formato de saída
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    protected function transformJsonToFormat(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'];
        
        // Carregar o template JSON
        $template = JsonTemplates::getTemplate($templateName);
        
        // Ler o arquivo JSON de origem
        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            throw new \InvalidArgumentException("Erro ao ler o arquivo JSON: $filePath");
        }
        
        $sourceData = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Erro ao decodificar o JSON: " . json_last_error_msg());
        }
        
        // Preparar a estrutura do JSON de saída
        $output = [
            'meta' => [
                'generated' => date('Y-m-d H:i:s'),
                'template' => $templateName,
                'source' => basename($filePath),
            ],
        ];
        
        // Adicionar cabeçalho se existir no template
        if (isset($template['header'])) {
            $output['header'] = $template['header'];
        }
        
        // Processar os dados
        if (isset($template['items']) && isset($sourceData)) {
            $output['items'] = $this->processItems($sourceData, $rules, $template);
        }
        
        // Adicionar rodapé se existir no template
        if (isset($template['footer'])) {
            $output['footer'] = $template['footer'];
        }
        
        // Retornar o JSON formatado
        return json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Transforma um arquivo XML para o formato de saída
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    protected function transformXmlToFormat(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'];
        
        // Carregar o template JSON
        $template = JsonTemplates::getTemplate($templateName);
        
        // Ler o arquivo XML de origem
        $xml = simplexml_load_file($filePath);
        if ($xml === false) {
            throw new \InvalidArgumentException("Erro ao carregar o arquivo XML: $filePath");
        }
        
        // Converter o XML para array para facilitar o processamento
        $sourceData = $this->xmlToArray($xml);
        
        // Preparar a estrutura do JSON de saída
        $output = [
            'meta' => [
                'generated' => date('Y-m-d H:i:s'),
                'template' => $templateName,
                'source' => basename($filePath),
            ],
        ];
        
        // Adicionar cabeçalho se existir no template
        if (isset($template['header'])) {
            $output['header'] = $template['header'];
        }
        
        // Processar os dados
        if (isset($template['items']) && isset($sourceData)) {
            $output['items'] = $this->processItems($sourceData, $rules, $template);
        }
        
        // Adicionar rodapé se existir no template
        if (isset($template['footer'])) {
            $output['footer'] = $template['footer'];
        }
        
        // Retornar o JSON formatado
        return json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Transforma um arquivo CSV para o formato de saída
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    protected function transformCsvToFormat(array $data, array $rules, string $templateName): string
    {
        // Implementação para transformar CSV em JSON
        // Código similar ao transformJsonToFormat, mas lendo de um CSV
        
        // Retorno temporário
        return "Implementação de CSV para JSON pendente";
    }
    
    /**
     * Transforma um arquivo XLSX para o formato de saída
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    protected function transformXlsxToFormat(array $data, array $rules, string $templateName): string
    {
        // Implementação para transformar XLSX em JSON
        // Código similar ao transformJsonToFormat, mas lendo de uma planilha Excel
        
        // Retorno temporário
        return "Implementação de XLSX para JSON pendente";
    }
    
    /**
     * Processa os itens de dados de acordo com o template
     *
     * @param array $items Os dados de origem
     * @param array $rules As regras de transformação
     * @param array $template O template a ser aplicado
     * @return array Os itens processados
     */
    private function processItems(array $items, array $rules, array $template): array
    {
        $result = [];
        
        // Verificar se $items é um array associativo ou sequencial
        if ($this->isAssocArray($items)) {
            // Se for um array associativo, trate como um único item
            $items = [$items];
        } else {
            // Se não temos um array de itens, verificar se existe uma chave principal que contém os dados
            if (!isset($items[0])) {
                foreach ($items as $key => $value) {
                    if (is_array($value) && isset($value[0])) {
                        $items = $value;
                        break;
                    }
                }
            }
        }
        
        // Se ainda não conseguimos identificar os itens, retornar array vazio
        if (!isset($items[0])) {
            return $result;
        }
        
        // Processa cada item
        foreach ($items as $item) {
            $processedItem = [];
            
            // Aplicar o template para cada item
            if (isset($template['items']['fields'])) {
                foreach ($template['items']['fields'] as $field => $config) {
                    if (is_array($config) && isset($config['map'])) {
                        // Campo com mapeamento específico
                        $processedItem[$field] = $this->getValueFromRules($rules, $config['map'], $item);
                    } else {
                        // Campo sem mapeamento específico (usar o mesmo nome)
                        $processedItem[$field] = $this->getValueFromRules($rules, $field, $item);
                    }
                }
            }
            
            $result[] = $processedItem;
        }
        
        return $result;
    }
    
    /**
     * Verifica se um array é associativo
     *
     * @param array $array O array a ser verificado
     * @return bool True se for um array associativo, False caso contrário
     */
    private function isAssocArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        
        return array_keys($array) !== range(0, count($array) - 1);
    }
} 
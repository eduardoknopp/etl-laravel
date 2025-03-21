<?php

namespace App\Services\Transformers\Xml;

use App\Services\Transformers\AbstractTransformer;

class XmlTransformer extends AbstractTransformer
{
    /**
     * Retorna o formato de saída do transformador
     *
     * @return string O formato de saída (json, xml, csv, xlsx)
     */
    protected function getOutputFormat(): string
    {
        return 'xml';
    }
    
    /**
     * Transforma um arquivo do mesmo formato para o mesmo formato
     *
     * @param array $data Os dados do arquivo origem
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados
     */
    protected function transformSameFormat(array $data, array $rules, string $templateName): string
    {
        return $this->transformXmlToFormat($data, $rules, $templateName);
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
        
        // Obtém o template XML
        $template = XmlTemplates::getTemplate($templateName);
        
        // Carregar o arquivo XML de origem
        $xmlData = simplexml_load_file($filePath);
        if ($xmlData === false) {
            throw new \InvalidArgumentException("Erro ao carregar o arquivo XML: $filePath");
        }
        
        // Converter para array para processamento
        $sourceData = $this->xmlToArray($xmlData);
        
        // Gera o XML usando o template e os dados
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= $this->processXmlStructure($template, $sourceData, $rules);
        
        return $xml;
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
        
        // Obtém o template XML
        $template = XmlTemplates::getTemplate($templateName);
        
        // Ler o arquivo JSON de origem
        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            throw new \InvalidArgumentException("Erro ao ler o arquivo JSON: $filePath");
        }
        
        $sourceData = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Erro ao decodificar o JSON: " . json_last_error_msg());
        }
        
        // Gera o XML usando o template e os dados
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= $this->processXmlStructure($template, $sourceData, $rules);
        
        return $xml;
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
        // Implementação para transformar CSV em XML
        // Retorno temporário
        return "Implementação de CSV para XML pendente";
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
        // Implementação para transformar XLSX em XML
        // Retorno temporário
        return "Implementação de XLSX para XML pendente";
    }
    
    /**
     * Processa a estrutura do XML com base no template
     *
     * @param array $structure A estrutura do template
     * @param array $data Os dados a serem inseridos
     * @param array $rules As regras de transformação
     * @return string O XML gerado
     */
    private function processXmlStructure(array $structure, array $data, array $rules): string
    {
        $xml = '';
        
        // Processa o elemento raiz
        if (isset($structure['root'])) {
            $rootElement = $structure['root'];
            $rootName = $rootElement['name'];
            $rootAttributes = $rootElement['attributes'] ?? [];
            
            // Abre o elemento raiz com atributos
            $xml .= "<{$rootName}";
            foreach ($rootAttributes as $attrName => $attrValue) {
                $xml .= " {$attrName}=\"" . htmlspecialchars($attrValue) . "\"";
            }
            $xml .= ">\n";
            
            // Processa o cabeçalho, se existir
            if (isset($structure['header'])) {
                $xml .= $this->processElement($structure['header'], $data, $rules);
            }
            
            // Processa os elementos principais (linhas de dados)
            if (isset($structure['data'])) {
                $dataElement = $structure['data'];
                $itemElement = $dataElement['item'] ?? null;
                
                if ($itemElement && isset($data[0])) {
                    // Processa cada linha de dados
                    foreach ($data as $row) {
                        $xml .= $this->processElement(['element' => $itemElement], $row, $rules);
                    }
                } elseif ($itemElement) {
                    // Se não temos um array de linhas, trate como um único registro
                    $xml .= $this->processElement(['element' => $itemElement], $data, $rules);
                }
            }
            
            // Processa o rodapé, se existir
            if (isset($structure['footer'])) {
                $xml .= $this->processFooter($structure['footer'], $data, $rules);
            }
            
            // Fecha o elemento raiz
            $xml .= "</{$rootName}>\n";
        }
        
        return $xml;
    }
    
    /**
     * Processa um elemento XML
     *
     * @param array $element A definição do elemento
     * @param array $data Os dados para o elemento
     * @param array $rules As regras de transformação
     * @return string O elemento XML
     */
    private function processElement(array $element, array $data, array $rules): string
    {
        $xml = '';
        
        if (isset($element['element'])) {
            $elementDef = $element['element'];
            $elementName = $elementDef['name'];
            $elementAttributes = $elementDef['attributes'] ?? [];
            $elementValue = $elementDef['value'] ?? null;
            $elementChildren = $elementDef['children'] ?? [];
            
            // Abre o elemento com atributos
            $xml .= "<{$elementName}";
            
            // Processa atributos
            foreach ($elementAttributes as $attrName => $attrConfig) {
                if (is_array($attrConfig) && isset($attrConfig['field'])) {
                    // Obtém o valor do atributo das regras
                    $value = $this->getValueFromRules($rules, $attrConfig['field'], $data);
                } else {
                    // Valor fixo
                    $value = $attrConfig;
                }
                
                $xml .= " {$attrName}=\"" . htmlspecialchars((string)$value) . "\"";
            }
            
            // Se tiver conteúdo ou filhos
            if ($elementValue !== null || !empty($elementChildren)) {
                $xml .= ">";
                
                // Processa o valor do elemento, se existir
                if ($elementValue !== null) {
                    if (is_array($elementValue) && isset($elementValue['field'])) {
                        // Obtém o valor do elemento das regras
                        $value = $this->getValueFromRules($rules, $elementValue['field'], $data);
                        $xml .= htmlspecialchars((string)$value);
                    } else {
                        // Valor fixo
                        $xml .= htmlspecialchars((string)$elementValue);
                    }
                }
                
                // Processa elementos filhos
                foreach ($elementChildren as $childElement) {
                    $xml .= $this->processElement(['element' => $childElement], $data, $rules);
                }
                
                // Fecha o elemento
                $xml .= "</{$elementName}>\n";
            } else {
                // Elemento vazio
                $xml .= " />\n";
            }
        }
        
        return $xml;
    }
    
    /**
     * Processa o rodapé do XML
     *
     * @param array $footer A definição do rodapé
     * @param array $data Os dados para o rodapé
     * @param array $rules As regras de transformação
     * @return string O rodapé XML
     */
    private function processFooter(array $footer, array $data, array $rules): string
    {
        $xml = '';
        
        // Processa elementos do rodapé
        if (isset($footer['elements']) && is_array($footer['elements'])) {
            foreach ($footer['elements'] as $element) {
                $xml .= $this->processElement(['element' => $element], $data, $rules);
            }
        }
        
        return $xml;
    }
} 
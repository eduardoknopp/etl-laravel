<?php

namespace App\Services\Transformers\Csv;

use App\Services\Transformers\AbstractTransformer;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;

class CsvTransformer extends AbstractTransformer
{
    /**
     * Retorna o formato de saída do transformador
     *
     * @return string O formato de saída (json, xml, csv, xlsx)
     */
    protected function getOutputFormat(): string
    {
        return 'csv';
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
        return $this->transformCsvToFormat($data, $rules, $templateName);
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
        $filePath = $data['path'];
        
        // Carregar o template CSV
        $template = CsvTemplates::getTemplate($templateName);
        
        // Ler o arquivo CSV de origem
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0); // Assumindo que a primeira linha contém os cabeçalhos
        
        // Ler os registros
        $records = Statement::create()->process($csv);
        $sourceRows = iterator_to_array($records);
        
        // Criar um novo CSV em memória
        $outputCsv = Writer::createFromString('');
        
        // Definir o delimitador e outros parâmetros
        $outputCsv->setDelimiter($template['delimiter'] ?? ',');
        $outputCsv->setEnclosure($template['enclosure'] ?? '"');
        $outputCsv->setEscape($template['escape_char'] ?? '\\');
        
        // Adicionar cabeçalhos
        $outputCsv->insertOne($template['headers']);
        
        // Processar cada linha de dados
        $rows = [];
        foreach ($sourceRows as $sourceRow) {
            $row = [];
            
            // Mapear cada coluna de acordo com as regras
            foreach ($template['headers'] as $targetField) {
                $row[] = $this->getValueFromRules($rules, $targetField, $sourceRow);
            }
            
            $rows[] = $row;
        }
        
        // Inserir todas as linhas de dados
        $outputCsv->insertAll($rows);
        
        // Retornar o conteúdo CSV
        return $outputCsv->getContent();
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
        // Implementação para transformar XLSX em CSV
        // Código similar ao transformCsvToFormat, mas lendo de uma planilha Excel
        
        // Retorno temporário
        return "Implementação de XLSX para CSV pendente";
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
        
        // Carregar o template CSV
        $template = CsvTemplates::getTemplate($templateName);
        
        // Ler o arquivo XML de origem
        $xml = simplexml_load_file($filePath);
        if ($xml === false) {
            throw new \InvalidArgumentException("Erro ao carregar o arquivo XML: $filePath");
        }
        
        // Converter o XML para array para facilitar o processamento
        $sourceRows = $this->xmlToArray($xml);
        
        // Criar um novo CSV em memória
        $outputCsv = Writer::createFromString('');
        
        // Definir o delimitador e outros parâmetros
        $outputCsv->setDelimiter($template['delimiter'] ?? ',');
        $outputCsv->setEnclosure($template['enclosure'] ?? '"');
        $outputCsv->setEscape($template['escape_char'] ?? '\\');
        
        // Adicionar cabeçalhos
        $outputCsv->insertOne($template['headers']);
        
        // Processar cada linha de dados
        $rows = [];
        foreach ($sourceRows as $sourceRow) {
            $row = [];
            
            // Mapear cada coluna de acordo com as regras
            foreach ($template['headers'] as $targetField) {
                $row[] = $this->getValueFromRules($rules, $targetField, $sourceRow);
            }
            
            $rows[] = $row;
        }
        
        // Inserir todas as linhas de dados
        $outputCsv->insertAll($rows);
        
        // Retornar o conteúdo CSV
        return $outputCsv->getContent();
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
        
        // Carregar o template CSV
        $template = CsvTemplates::getTemplate($templateName);
        
        // Ler o arquivo JSON de origem
        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            throw new \InvalidArgumentException("Erro ao ler o arquivo JSON: $filePath");
        }
        
        $sourceRows = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Erro ao decodificar o JSON: " . json_last_error_msg());
        }
        
        // Garantir que temos um array de objetos para processar
        if (!is_array($sourceRows) || !isset($sourceRows[0])) {
            // Se for objeto único ou estrutura aninhada, tentar converter para array de objetos
            if (is_array($sourceRows) && count($sourceRows) > 0) {
                // Verificar se há uma chave principal que contém os dados
                foreach ($sourceRows as $key => $value) {
                    if (is_array($value) && isset($value[0])) {
                        $sourceRows = $value;
                        break;
                    }
                }
            } else {
                // Se não for possível converter, tratar como um único objeto
                $sourceRows = [$sourceRows];
            }
        }
        
        // Criar um novo CSV em memória
        $outputCsv = Writer::createFromString('');
        
        // Definir o delimitador e outros parâmetros
        $outputCsv->setDelimiter($template['delimiter'] ?? ',');
        $outputCsv->setEnclosure($template['enclosure'] ?? '"');
        $outputCsv->setEscape($template['escape_char'] ?? '\\');
        
        // Adicionar cabeçalhos
        $outputCsv->insertOne($template['headers']);
        
        // Processar cada linha de dados
        $rows = [];
        foreach ($sourceRows as $sourceRow) {
            $row = [];
            
            // Mapear cada coluna de acordo com as regras
            foreach ($template['headers'] as $targetField) {
                $row[] = $this->getValueFromRules($rules, $targetField, $sourceRow);
            }
            
            $rows[] = $row;
        }
        
        // Inserir todas as linhas de dados
        $outputCsv->insertAll($rows);
        
        // Retornar o conteúdo CSV
        return $outputCsv->getContent();
    }
} 
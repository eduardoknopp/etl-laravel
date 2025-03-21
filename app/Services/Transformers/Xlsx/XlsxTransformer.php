<?php

namespace App\Services\Transformers\Xlsx;

use App\Services\Transformers\AbstractTransformer;
use Spatie\SimpleExcel\SimpleExcelReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxTransformer extends AbstractTransformer
{
    /**
     * Retorna o formato de saída do transformador
     *
     * @return string O formato de saída (json, xml, csv, xlsx)
     */
    protected function getOutputFormat(): string
    {
        return 'xlsx';
    }
    
    /**
     * Transforma um arquivo XLSX em outro arquivo XLSX aplicando as regras
     *
     * @param array $data Os dados do arquivo origem (caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato XLSX
     */
    protected function transformSameFormat(array $data, array $rules, string $templateName): string
    {
        return $this->transformXlsxToFormat($data, $rules, $templateName);
    }
    
    /**
     * Transforma um arquivo JSON em arquivo XLSX aplicando as regras
     *
     * @param array $data Os dados do arquivo origem (caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato XLSX
     */
    protected function transformJsonToFormat(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'];
        
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
            
            // Aplicar estilos aos cabeçalhos
            if (isset($sheetConfig['styles']['headers'])) {
                $headerStyle = $sheet->getStyle('A1:' . $this->getColumnLetter(count($sheetConfig['headers'])) . '1');
                $this->applyHeaderStyles($headerStyle, $sheetConfig['styles']['headers']);
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
        
        // Salva o arquivo XLSX para um buffer de memória temporário
        return $this->saveSpreadsheetToString($spreadsheet);
    }
    
    /**
     * Transforma um arquivo XML em arquivo XLSX aplicando as regras
     *
     * @param array $data Os dados do arquivo origem (caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato XLSX
     */
    protected function transformXmlToFormat(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'];
        
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
        
        // Ler o arquivo XML de origem
        $xml = simplexml_load_file($filePath);
        if ($xml === false) {
            throw new \InvalidArgumentException("Erro ao carregar o arquivo XML: $filePath");
        }
        
        // Converter o XML para array para facilitar o processamento
        $sourceRows = $this->xmlToArray($xml);
        
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
            
            // Aplicar estilos aos cabeçalhos
            if (isset($sheetConfig['styles']['headers'])) {
                $headerStyle = $sheet->getStyle('A1:' . $this->getColumnLetter(count($sheetConfig['headers'])) . '1');
                $this->applyHeaderStyles($headerStyle, $sheetConfig['styles']['headers']);
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
        
        // Salva o arquivo XLSX para um buffer de memória temporário
        return $this->saveSpreadsheetToString($spreadsheet);
    }
    
    /**
     * Transforma um arquivo CSV em arquivo XLSX aplicando as regras
     *
     * @param array $data Os dados do arquivo origem (caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato XLSX
     */
    protected function transformCsvToFormat(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'];
        
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
        
        // Ler o arquivo CSV de origem com SimpleExcelReader
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
            
            // Aplicar estilos aos cabeçalhos
            if (isset($sheetConfig['styles']['headers'])) {
                $headerStyle = $sheet->getStyle('A1:' . $this->getColumnLetter(count($sheetConfig['headers'])) . '1');
                $this->applyHeaderStyles($headerStyle, $sheetConfig['styles']['headers']);
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
        
        // Salva o arquivo XLSX para um buffer de memória temporário
        return $this->saveSpreadsheetToString($spreadsheet);
    }

    /**
     * Transforma um arquivo XLSX em arquivo XLSX aplicando as regras
     *
     * @param array $data Os dados do arquivo origem (caminho do arquivo)
     * @param array $rules As regras de transformação
     * @param string $templateName O nome do template a ser usado
     * @return string Os dados transformados no formato XLSX
     */
    protected function transformXlsxToFormat(array $data, array $rules, string $templateName): string
    {
        $filePath = $data['path'];
        
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
                $this->applyHeaderStyles($headerStyle, $sheetConfig['styles']['headers']);
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
        
        // Salva o arquivo XLSX para um buffer de memória temporário
        return $this->saveSpreadsheetToString($spreadsheet);
    }

    /**
     * Salva uma planilha para uma string (buffer de memória)
     *
     * @param Spreadsheet $spreadsheet A planilha a ser salva
     * @return string O conteúdo do arquivo XLSX em bytes
     */
    private function saveSpreadsheetToString(Spreadsheet $spreadsheet): string
    {
        // 1. Cria um escritor XLSX usando a planilha gerada
        $writer = new Xlsx($spreadsheet);
        
        // 2. Inicia o buffer de saída para capturar os dados em memória
        ob_start();
        
        // 3. Salva o arquivo XLSX diretamente para o buffer de saída PHP
        $writer->save('php://output');
        
        // 4. Obtém todo o conteúdo do buffer (o arquivo XLSX em bytes)
        $content = ob_get_contents();
        
        // 5. Limpa e finaliza o buffer, liberando a memória
        ob_end_clean();
        
        return $content;
    }
} 
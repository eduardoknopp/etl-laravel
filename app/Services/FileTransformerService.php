<?php

namespace App\Services;

use App\Models\ImportedFile;
use App\Models\TransformationMap;
use App\Models\ETLProcess;
use App\Services\Transformers\TransformerFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Exception;

class FileTransformerService
{
    /**
     * Processa um arquivo aplicando as regras de transformação
     * 
     * @param ImportedFile $importedFile O arquivo a ser processado
     * @param TransformationMap|null $transformationMap Mapa de transformação específico (opcional)
     * @return array Resultado do processamento
     */
    public function processFile(ImportedFile $importedFile, ?TransformationMap $transformationMap = null): array
    {
        Log::info('Processing file transformation: ' . $importedFile->filename);

        $filePath = $importedFile->path;

        // Verifica se o arquivo existe no Storage
        if (!Storage::exists($filePath)) {
            Log::error('File not found in storage: ' . $filePath);
            return ['error' => 'File not found in storage'];
        }

        // Determinar o tipo de arquivo (extensão)
        $fileExtension = pathinfo($importedFile->filename, PATHINFO_EXTENSION);
        $fileType = strtolower($fileExtension);

        // Usar o mapa fornecido ou buscar um adequado
        if (!$transformationMap) {
            $transformationMap = TransformationMap::where('from_type', $fileType)->first();

            if (!$transformationMap) {
                Log::error("No transformation map found for file type: {$fileType}");
                $importedFile->update(['status' => 'failed']);
                return ['error' => "No transformation map found for file type: {$fileType}"];
            }
        }

        try {
            // Registrar o processo ETL
            $etlProcess = ETLProcess::create([
                'file_id' => $importedFile->id,
                'map_id' => $transformationMap->id,
                'status' => 'processing'
            ]);

            // Atualizar o status do arquivo para "processing"
            $importedFile->update(['status' => 'processing']);

            // Preparar dados para transformação
            $data = [
                'path' => Storage::path($filePath),
                'type' => $fileType,
                'filename' => $importedFile->filename
            ];

            // Obter o template apropriado
            $templateName = $transformationMap->template ?? 'default';

            // Criar o transformador adequado e processar
            $transformer = TransformerFactory::createTransformer($transformationMap->to_type);
            $result = $transformer->transform($data, $transformationMap->rules, $templateName);
            
            // Determinar o nome do arquivo de saída com timestamp para evitar colisões
            $timestamp = Carbon::now()->format('YmdHis');
            $outputFilename = "{$timestamp}_{$importedFile->id}." . $transformationMap->to_type;
            $outputPath = "transformed/{$outputFilename}";
            
            // Salvar o resultado transformado
            Storage::put($outputPath, $result);
            
            Log::info('File transformation completed for: ' . $importedFile->filename, [
                'output' => $outputPath
            ]);

            // Atualizar o status do arquivo e do processo ETL
            $importedFile->update(['status' => 'completed']);
            $etlProcess->update([
                'status' => 'completed',
                'processed_at' => now()
            ]);

            return [
                'message' => 'File transformed successfully',
                'file_id' => $importedFile->id,
                'process_id' => $etlProcess->id,
                'status' => 'completed',
                'output_path' => $outputPath,
                'output_url' => Storage::url($outputPath)
            ];
        } catch (Exception $e) {
            Log::error('Error transforming file: ' . $e->getMessage(), [
                'file' => $importedFile->filename,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Atualizar o status do arquivo e do processo ETL para "failed"
            $importedFile->update(['status' => 'failed']);
            
            // Se o processo ETL foi criado, atualizar seu status
            if (isset($etlProcess)) {
                $etlProcess->update([
                    'status' => 'failed',
                    'processed_at' => now()
                ]);
            }
            
            return [
                'error' => 'Error transforming file: ' . $e->getMessage(),
                'file_id' => $importedFile->id,
                'process_id' => $etlProcess->id ?? null,
                'status' => 'failed'
            ];
        }
    }

    /**
     * Analisa um arquivo para extrair metadados e informações que podem ser usadas
     * para criar um mapa de transformação
     * 
     * @param ImportedFile $importedFile O arquivo a ser analisado
     * @return array Informações sobre o arquivo
     */
    public function analyzeFile(ImportedFile $importedFile): array
    {
        $filePath = $importedFile->path;
        
        // Verifica se o arquivo existe
        if (!Storage::exists($filePath)) {
            return ['error' => 'File not found in storage'];
        }
        
        $fileExtension = pathinfo($importedFile->filename, PATHINFO_EXTENSION);
        $fileType = strtolower($fileExtension);
        
        $fullPath = Storage::path($filePath);
        $fileInfo = [
            'type' => $fileType,
            'size' => Storage::size($filePath),
            'last_modified' => date('Y-m-d H:i:s', Storage::lastModified($filePath))
        ];
        
        // Analisar a estrutura do arquivo com base no tipo
        switch ($fileType) {
            case 'csv':
                $fileInfo['structure'] = $this->analyzeCSV($fullPath);
                break;
                
            case 'json':
                $fileInfo['structure'] = $this->analyzeJSON($fullPath);
                break;
                
            case 'xml':
                $fileInfo['structure'] = $this->analyzeXML($fullPath);
                break;
                
            case 'xlsx':
            case 'xls':
                $fileInfo['structure'] = $this->analyzeExcel($fullPath, $fileType);
                break;
                
            default:
                $fileInfo['structure'] = ['error' => 'Unsupported file type for analysis'];
        }
        
        return $fileInfo;
    }
    
    /**
     * Analisa um arquivo CSV para extrair informações de estrutura
     * 
     * @param string $filePath Caminho completo do arquivo
     * @return array Informações de estrutura do CSV
     */
    private function analyzeCSV(string $filePath): array
    {
        // Detectar delimitador
        $possibleDelimiters = [',', ';', "\t", '|'];
        $delimiter = ','; // padrão
        
        $file = fopen($filePath, 'r');
        if ($file) {
            $firstLine = fgets($file);
            fclose($file);
            
            // Analisar qual delimitador tem mais ocorrências
            $countDelimiters = [];
            foreach ($possibleDelimiters as $possibleDelimiter) {
                $countDelimiters[$possibleDelimiter] = substr_count($firstLine, $possibleDelimiter);
            }
            
            // Ordenar por contagem (maior para menor)
            arsort($countDelimiters);
            
            // Pegar o delimitador com mais ocorrências
            $delimiter = key($countDelimiters);
        }
        
        // Usar League CSV para análise detalhada
        $csv = \League\Csv\Reader::createFromPath($filePath, 'r');
        $csv->setDelimiter($delimiter);
        $csv->setHeaderOffset(0);
        
        $headers = $csv->getHeader();
        
        // Pegar uma amostra das primeiras linhas
        $rows = [];
        $stmt = \League\Csv\Statement::create()->limit(5);
        $records = $stmt->process($csv);
        
        foreach ($records as $record) {
            $rows[] = $record;
        }
        
        return [
            'headers' => $headers,
            'delimiter' => $delimiter,
            'sample_rows' => $rows
        ];
    }
    
    /**
     * Analisa um arquivo JSON para extrair informações de estrutura
     * 
     * @param string $filePath Caminho completo do arquivo
     * @return array Informações de estrutura do JSON
     */
    private function analyzeJSON(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON format: ' . json_last_error_msg()];
        }
        
        // Determinar se é um array ou objeto
        $isArray = is_array($data) && isset($data[0]);
        
        // Se for um array, analisar o primeiro item
        if ($isArray) {
            $sample = array_slice($data, 0, 5);
            $structure = $this->analyzeJSONStructure($sample[0]);
            
            return [
                'type' => 'array',
                'count' => count($data),
                'sample' => $sample,
                'structure' => $structure
            ];
        }
        
        // Analisar a estrutura do objeto
        return [
            'type' => 'object',
            'structure' => $this->analyzeJSONStructure($data),
            'sample' => $data
        ];
    }
    
    /**
     * Analisa a estrutura de um objeto JSON
     * 
     * @param array $data Os dados a serem analisados
     * @return array A estrutura dos dados
     */
    private function analyzeJSONStructure(array $data): array
    {
        $structure = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value[0])) {
                    // É um array de objetos/valores
                    $structure[$key] = [
                        'type' => 'array',
                        'sample' => array_slice($value, 0, 3)
                    ];
                    
                    if (is_array($value[0])) {
                        $structure[$key]['item_structure'] = $this->analyzeJSONStructure($value[0]);
                    }
                } else {
                    // É um objeto
                    $structure[$key] = [
                        'type' => 'object',
                        'structure' => $this->analyzeJSONStructure($value)
                    ];
                }
            } else {
                // É um valor simples
                $structure[$key] = [
                    'type' => gettype($value),
                    'sample' => $value
                ];
            }
        }
        
        return $structure;
    }
    
    /**
     * Analisa um arquivo XML para extrair informações de estrutura
     * 
     * @param string $filePath Caminho completo do arquivo
     * @return array Informações de estrutura do XML
     */
    private function analyzeXML(string $filePath): array
    {
        $xml = simplexml_load_file($filePath);
        
        if ($xml === false) {
            return ['error' => 'Failed to parse XML'];
        }
        
        // Converter para JSON e depois para array para análise mais fácil
        $json = json_encode($xml);
        $data = json_decode($json, true);
        
        return [
            'root_element' => $xml->getName(),
            'structure' => $this->analyzeXMLStructure($data),
            'sample' => $data
        ];
    }
    
    /**
     * Analisa a estrutura de um objeto XML
     * 
     * @param array $data Os dados a serem analisados
     * @return array A estrutura dos dados
     */
    private function analyzeXMLStructure(array $data): array
    {
        $structure = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value[0])) {
                    // É um array de elementos
                    $structure[$key] = [
                        'type' => 'array',
                        'count' => count($value),
                        'sample' => array_slice($value, 0, 3)
                    ];
                    
                    if (is_array($value[0])) {
                        $structure[$key]['item_structure'] = $this->analyzeXMLStructure($value[0]);
                    }
                } else {
                    // É um elemento com subelementos
                    $structure[$key] = [
                        'type' => 'element',
                        'structure' => $this->analyzeXMLStructure($value)
                    ];
                }
            } else {
                // É um valor simples
                $structure[$key] = [
                    'type' => gettype($value),
                    'sample' => $value
                ];
            }
        }
        
        return $structure;
    }
    
    /**
     * Analisa um arquivo Excel para extrair informações de estrutura
     * 
     * @param string $filePath Caminho completo do arquivo
     * @param string $type Tipo do arquivo (xlsx ou xls)
     * @return array Informações de estrutura do Excel
     */
    private function analyzeExcel(string $filePath, string $type): array
    {
        // Usar PhpSpreadsheet para análise
        $reader = ($type === 'xlsx') 
            ? new \PhpOffice\PhpSpreadsheet\Reader\Xlsx() 
            : new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            
        try {
            $spreadsheet = $reader->load($filePath);
            
            $sheetInfo = [];
            
            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                
                // Pegar uma amostra das primeiras linhas
                $headerRow = $sheet->getRowIterator(1, 1)->current();
                $headers = [];
                
                foreach ($headerRow->getCellIterator() as $cell) {
                    $headers[] = $cell->getValue();
                }
                
                // Pegar algumas linhas de amostra
                $sampleRows = [];
                $dataRows = $sheet->getRowIterator(2, 6);
                
                foreach ($dataRows as $row) {
                    $rowData = [];
                    foreach ($row->getCellIterator() as $cell) {
                        $rowData[] = $cell->getValue();
                    }
                    $sampleRows[] = $rowData;
                }
                
                $sheetInfo[$sheetName] = [
                    'headers' => $headers,
                    'sample_rows' => $sampleRows
                ];
            }
            
            return [
                'sheets' => $sheetInfo,
                'count_sheets' => count($sheetInfo)
            ];
        } catch (\Exception $e) {
            return ['error' => 'Failed to parse Excel file: ' . $e->getMessage()];
        }
    }
}

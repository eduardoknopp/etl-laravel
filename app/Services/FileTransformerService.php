<?php

namespace App\Services;

use App\Models\ImportedFile;
use App\Models\TransformationMap;
use App\Services\Transformers\TransformerFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class FileTransformerService
{
    public function processFile(ImportedFile $importedFile): array
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

        // Buscar um mapa de transformação adequado
        $transformationMap = TransformationMap::where('from_type', $fileType)->first();

        if (!$transformationMap) {
            Log::error("No transformation map found for file type: {$fileType}");
            $importedFile->update(['status' => 'failed']);
            return ['error' => "No transformation map found for file type: {$fileType}"];
        }

        try {
            // Preparar dados para transformação
            $data = [
                'path' => Storage::path($filePath),
                'type' => $fileType
            ];

            // Obter o template apropriado
            $templateName = $transformationMap->template ?? 'default';

            // Criar o transformador adequado e processar
            $transformer = TransformerFactory::createTransformer($transformationMap->to_type);
            $result = $transformer->transform($data, $transformationMap->rules, $templateName);
            
            // Salvar o resultado transformado
            $outputPath = 'transformed/' . time() . '_' . pathinfo($importedFile->filename, PATHINFO_FILENAME) . '.' . $transformationMap->to_type;
            Storage::put($outputPath, $result);
            
            Log::info('File transformation completed for: ' . $importedFile->filename);

            // Atualiza o status do arquivo no banco para "completed"
            $importedFile->update(['status' => 'completed']);

            return [
                'message' => 'File transformed successfully',
                'file_id' => $importedFile->id,
                'status' => 'completed',
                'output_path' => $outputPath
            ];
        } catch (Exception $e) {
            Log::error('Error transforming file: ' . $e->getMessage(), [
                'file' => $importedFile->filename,
                'trace' => $e->getTraceAsString()
            ]);
            
            $importedFile->update(['status' => 'failed']);
            
            return [
                'error' => 'Error transforming file: ' . $e->getMessage(),
                'file_id' => $importedFile->id,
                'status' => 'failed'
            ];
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ImportedFile;
use App\Services\FileTransformerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class ETLProcessController extends Controller
{
    protected FileTransformerService $fileTransformerService;

    public function __construct(FileTransformerService $fileTransformerService)
    {
        $this->fileTransformerService = $fileTransformerService;
    }

    public function processFile(Request $request, int $fileId): JsonResponse
    {
        Log::info("ETL processing request for file ID: {$fileId}");

        // Buscar o arquivo no banco de dados
        $importedFile = ImportedFile::find($fileId);

        if (!$importedFile) {
            Log::error("File with ID {$fileId} not found in database");
            return response()->json(['error' => 'File not found'], 404);
        }

        // Verificar se o arquivo já foi processado
        if ($importedFile->status === 'completed') {
            Log::warning("File ID {$fileId} already processed");
            return response()->json(['message' => 'File already processed'], 400);
        }

        // Chamar o serviço para processar o arquivo
        $result = $this->fileTransformerService->processFile($importedFile);

        return response()->json($result);
    }
}

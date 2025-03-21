<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessFileJob;
use App\Models\ImportedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class ETLProcessController extends Controller
{
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

        // Enviar para processamento assíncrono
        ProcessFileJob::dispatch($importedFile);

        return response()->json([
            'message' => 'File queued for processing',
            'file_id' => $importedFile->id,
            'status' => 'processing'
        ]);
    }
}

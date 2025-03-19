<?php

namespace App\Services;

use App\Models\ImportedFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function uploadFile(UploadedFile $file, string $source): array
    {
        Log::info('Processing file upload for: ' . $file->getClientOriginalName());

        $validExtensions = ['json', 'xml', 'xlsx', 'csv'];
        $extension = $file->getClientOriginalExtension();

        if (!in_array($extension, $validExtensions)) {
            Log::warning('Invalid file type: ' . $extension);
            return ['error' => 'Invalid file type. Only .json, .xml, .xlsx, and .csv are allowed.'];
        }

        // Salvar o arquivo no storage
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads', $filename);

        Log::info('File uploaded to: ' . $path);

        // Salvar os detalhes do arquivo no banco de dados
        $importedFile = ImportedFile::create([
            'filename' => $filename,
            'path' => $path,
            'status' => 'pending',
            'source' => $source, // 'upload' ou 'email'
        ]);

        return [
            'message' => 'File uploaded successfully',
            'file_id' => $importedFile->id, // Retorna o ID do arquivo no banco
            'path' => $path
        ];
    }
}

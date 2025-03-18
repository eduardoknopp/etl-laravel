<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileUploadService
{
    public function uploadFile(UploadedFile $file): array
    {
        Log::info('Processing file upload for: ' . $file->getClientOriginalName());

        $validExtensions = ['json', 'xml', 'xlsx', 'csv'];
        $extension = $file->getClientOriginalExtension();

        if (!in_array($extension, $validExtensions)) {
            Log::warning('Invalid file type: ' . $extension);
            return ['error' => 'Invalid file type. Only .json, .xml, .xlsx, and .csv are allowed.'];
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads', $filename);

        Log::info('File uploaded to: ' . $path);

        return [
            'message' => 'File uploaded successfully',
            'path' => $path
        ];
    }
}


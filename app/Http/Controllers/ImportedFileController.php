<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImportedFileController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function upload(Request $request): JsonResponse
    {
        Log::info('Upload request received');

        // Validar o arquivo enviado
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:json,xml,csv,xlsx|max:10240', // 10MB limite máximo
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed: ' . json_encode($validator->errors()->toArray()));
            return response()->json(['error' => $validator->errors()], 422);
        }

        if ($request->hasFile('file')) {
            Log::info('File found: ' . $request->file('file')->getClientOriginalName());
        } else {
            Log::warning('No file received in the request');
            return response()->json(['error' => 'No file received'], 400);
        }

        $result = $this->fileUploadService->uploadFile($request->file('file'), 'upload');

        Log::info('Upload result: ' . json_encode($result));

        return response()->json($result);
    }
}


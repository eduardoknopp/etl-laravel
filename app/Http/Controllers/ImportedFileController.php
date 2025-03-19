<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

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

        if ($request->hasFile('file')) {
            Log::info('File found: ' . $request->file('file')->getClientOriginalName());
        } else {
            Log::warning('No file received in the request');
        }

        $result = $this->fileUploadService->uploadFile($request->file('file'), 'upload');

        Log::info('Upload result: ' . json_encode($result));

        return response()->json($result);
    }
}


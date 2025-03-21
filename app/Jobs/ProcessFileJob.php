<?php

namespace App\Jobs;

use App\Models\ImportedFile;
use App\Services\FileTransformerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importedFile;

    /**
     * Create a new job instance.
     */
    public function __construct(ImportedFile $importedFile)
    {
        $this->importedFile = $importedFile;
    }

    /**
     * Execute the job.
     */
    public function handle(FileTransformerService $fileTransformerService): void
    {
        Log::info("Processing file in background job: {$this->importedFile->filename}");

        // Atualizar o status para "processing"
        $this->importedFile->update(['status' => 'processing']);

        try {
            // Processar o arquivo
            $result = $fileTransformerService->processFile($this->importedFile);
            
            Log::info("File processed successfully: {$this->importedFile->filename}", $result);
        } catch (\Exception $e) {
            // Em caso de erro, atualizar o status para "failed"
            $this->importedFile->update(['status' => 'failed']);
            
            Log::error("Error processing file: {$this->importedFile->filename}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Relançar a exceção para que o trabalho seja marcado como falha
            throw $e;
        }
    }
} 
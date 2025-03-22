<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportedFileController;
use App\Http\Controllers\TransformationMapController;
use App\Http\Controllers\ETLProcessController;
use App\Http\Controllers\TransformerTestController;

// Rotas para o ImportedFileController
Route::post('/upload', [ImportedFileController::class, 'upload']);

// Rotas para o TransformationMapController
Route::get('/transformation-maps', [TransformationMapController::class, 'index']); // Lista todos os registros
Route::get('/transformation-maps/{id}', [TransformationMapController::class, 'show']); // Exibe um registro específico
Route::post('/transformation-maps', [TransformationMapController::class, 'store']); // Cria um novo registro
Route::put('/transformation-maps/{id}', [TransformationMapController::class, 'update']); // Atualiza um registro
Route::delete('/transformation-maps/{id}', [TransformationMapController::class, 'destroy']); // Deleta um registro

// Rotas para o ETLProcessController
Route::post('/etl/process/{fileId}', [ETLProcessController::class, 'processFile']);

// Rotas para teste de transformadores
Route::prefix('transformers')->group(function () {
    Route::post('/test', [TransformerTestController::class, 'testTransformation']);
    Route::get('/list', [TransformerTestController::class, 'listTransformations']);
    Route::post('/analyze', [TransformerTestController::class, 'analyzeFile']);
});

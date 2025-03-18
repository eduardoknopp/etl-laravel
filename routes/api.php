<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Rota para fazer o upload de arquivos
use App\Http\Controllers\ImportedFileController;

Route::post('/upload', [ImportedFileController::class, 'upload']);

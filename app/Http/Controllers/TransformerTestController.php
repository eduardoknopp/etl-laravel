<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FileTransformerService;
use App\Models\ImportedFile;
use App\Models\TransformationMap;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TransformerTestController extends Controller
{
    protected $transformerService;
    
    public function __construct(FileTransformerService $transformerService)
    {
        $this->transformerService = $transformerService;
        
        // Middleware de autenticação para todos os métodos
        // Comentado por enquanto para desenvolvimento, mas facilmente habilitável no futuro
        // $this->middleware('auth');
        
        // Opcionalmente, podemos aplicar middleware apenas para determinadas ações
        // $this->middleware('auth')->except(['index']);
    }
    
    /**
     * Exibe a interface para testar transformações
     */
    public function index()
    {
        $transformationMaps = TransformationMap::all();
        return view('transformers.test', compact('transformationMaps'));
    }
    
    /**
     * Testa uma transformação de arquivo
     */
    public function testTransformation(Request $request)
    {
        // Validar a requisição
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
            'transformation_map_id' => 'required|exists:transformation_maps,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Obter o mapa de transformação
            $transformationMap = TransformationMap::findOrFail($request->transformation_map_id);
            
            // Processar o upload do arquivo
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            // Verificar se o tipo de arquivo é compatível com o mapa de transformação
            if (strtolower($extension) !== $transformationMap->from_type) {
                return response()->json([
                    'success' => false,
                    'message' => "O tipo de arquivo ({$extension}) não corresponde ao tipo de origem do mapa de transformação ({$transformationMap->from_type})"
                ], 422);
            }
            
            // Salvar o arquivo
            $path = $file->store('test_uploads');
            
            // Criar um registro de arquivo importado temporário
            $importedFile = ImportedFile::create([
                'filename' => $filename,
                'original_filename' => $filename,
                'path' => $path,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'pending',
                'source' => 'test',
                'metadata' => [
                    'test' => true,
                    'uploaded_at' => now()->toIso8601String()
                ]
            ]);
            
            // Processar a transformação
            $result = $this->transformerService->processFile($importedFile, $transformationMap);
            
            if (isset($result['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }
            
            // Preparar a resposta com os links para download
            $outputPath = $result['output_path'] ?? null;
            $outputUrl = $result['output_url'] ?? null;
            
            return response()->json([
                'success' => true,
                'message' => 'Arquivo transformado com sucesso',
                'process_id' => $result['process_id'],
                'output_url' => $outputUrl,
                'from_type' => $transformationMap->from_type,
                'to_type' => $transformationMap->to_type
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar a transformação: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Exibe a lista de transformações disponíveis
     */
    public function listTransformations()
    {
        $transformations = [
            'from_json' => [],
            'from_xml' => [],
            'from_csv' => [],
            'from_xlsx' => []
        ];
        
        $maps = TransformationMap::all();
        
        foreach ($maps as $map) {
            $key = 'from_' . $map->from_type;
            if (isset($transformations[$key])) {
                $transformations[$key][] = [
                    'id' => $map->id,
                    'name' => $map->name,
                    'from' => $map->from_type,
                    'to' => $map->to_type,
                    'description' => $map->description
                ];
            }
        }
        
        return response()->json($transformations);
    }
    
    /**
     * Analisa o arquivo enviado
     */
    public function analyzeFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Processar o upload do arquivo
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            
            // Salvar o arquivo
            $path = $file->store('test_uploads');
            
            // Criar um registro de arquivo importado temporário
            $importedFile = ImportedFile::create([
                'filename' => $filename,
                'original_filename' => $filename,
                'path' => $path,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'pending',
                'source' => 'analysis',
                'metadata' => [
                    'analysis' => true,
                    'uploaded_at' => now()->toIso8601String()
                ]
            ]);
            
            // Analisar o arquivo
            $result = $this->transformerService->analyzeFile($importedFile);
            
            if (isset($result['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'analysis' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao analisar o arquivo: ' . $e->getMessage()
            ], 500);
        }
    }
} 
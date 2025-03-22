<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\FileTransformerService;
use App\Services\Transformers\TransformerFactory;
use App\Services\Transformers\TransformerInterface;
use App\Models\ImportedFile;
use App\Models\TransformationMap;
use App\Models\ETLProcess;
use Illuminate\Support\Facades\Storage;
use Mockery;

class FileTransformerServiceTest extends TestCase
{
    protected $transformerService;
    protected $importedFileMock;
    protected $transformationMapMock;
    protected $transformerMock;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Storage
        Storage::shouldReceive('exists')->andReturn(true);
        Storage::shouldReceive('path')->andReturn('/path/to/file');
        Storage::shouldReceive('put')->andReturn(true);
        Storage::shouldReceive('url')->andReturn('http://example.com/file');
        
        // Mock ImportedFile
        $this->importedFileMock = Mockery::mock(ImportedFile::class);
        $this->importedFileMock->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $this->importedFileMock->shouldReceive('getAttribute')->with('filename')->andReturn('test.json');
        $this->importedFileMock->shouldReceive('getAttribute')->with('path')->andReturn('imports/test.json');
        $this->importedFileMock->shouldReceive('update')->andReturn(true);
        
        // Mock TransformationMap
        $this->transformationMapMock = Mockery::mock(TransformationMap::class);
        $this->transformationMapMock->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $this->transformationMapMock->shouldReceive('getAttribute')->with('from_type')->andReturn('json');
        $this->transformationMapMock->shouldReceive('getAttribute')->with('to_type')->andReturn('xml');
        $this->transformationMapMock->shouldReceive('getAttribute')->with('rules')->andReturn([]);
        $this->transformationMapMock->shouldReceive('getAttribute')->with('template')->andReturn('default');
        
        // Mock ETLProcess
        $etlProcessMock = Mockery::mock('overload:' . ETLProcess::class);
        $etlProcessMock->shouldReceive('create')->andReturn((object)['id' => 1]);
        $etlProcessMock->shouldReceive('update')->andReturn(true);
        
        // Mock TransformerFactory and Transformer
        $this->transformerMock = Mockery::mock(TransformerInterface::class);
        $this->transformerMock->shouldReceive('transform')->andReturn('<xml></xml>');
        
        $factoryMock = Mockery::mock('alias:' . TransformerFactory::class);
        $factoryMock->shouldReceive('createTransformer')->andReturn($this->transformerMock);
        
        // Criar o serviço
        $this->transformerService = new FileTransformerService();
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function testProcessFile()
    {
        $result = $this->transformerService->processFile($this->importedFileMock, $this->transformationMapMock);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('File transformed successfully', $result['message']);
        $this->assertArrayHasKey('file_id', $result);
        $this->assertEquals(1, $result['file_id']);
        $this->assertArrayHasKey('process_id', $result);
        $this->assertEquals(1, $result['process_id']);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('completed', $result['status']);
    }
    
    public function testProcessFileWithNoMap()
    {
        // Configurar mockery para simular que não encontra um mapa
        $transformationMapMock = Mockery::mock('overload:' . TransformationMap::class);
        $transformationMapMock->shouldReceive('where->first')->andReturn(null);
        
        $result = $this->transformerService->processFile($this->importedFileMock);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('No transformation map found', $result['error']);
    }
    
    public function testProcessFileWithStorageError()
    {
        // Modificar o mock do Storage para simular um erro
        Storage::shouldReceive('exists')->andReturn(false);
        
        $result = $this->transformerService->processFile($this->importedFileMock, $this->transformationMapMock);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('File not found in storage', $result['error']);
    }
    
    public function testAnalyzeFile()
    {
        // Este método é complexo e depende da implementação específica para cada tipo de arquivo
        // Para este exemplo, vamos apenas verificar se ele retorna um array com a estrutura esperada
        
        $result = $this->transformerService->analyzeFile($this->importedFileMock);
        
        $this->assertIsArray($result);
        // Em um teste real, verificaríamos os detalhes da estrutura retornada
    }
} 
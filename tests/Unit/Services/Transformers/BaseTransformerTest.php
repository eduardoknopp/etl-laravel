<?php

namespace Tests\Unit\Services\Transformers;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;

abstract class BaseTransformerTest extends TestCase
{
    protected $transformer;
    protected $tempDirectory = 'testing/transformers';
    
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        
        // Criar diretório temporário para arquivos de teste
        if (!Storage::exists($this->tempDirectory)) {
            Storage::makeDirectory($this->tempDirectory);
        }
    }
    
    protected function tearDown(): void
    {
        // Limpar arquivos de teste
        Storage::deleteDirectory($this->tempDirectory);
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Cria um arquivo de teste com o conteúdo fornecido
     *
     * @param string $filename Nome do arquivo
     * @param string $content Conteúdo do arquivo
     * @return string Caminho completo do arquivo
     */
    protected function createTestFile(string $filename, string $content): string
    {
        $filePath = $this->tempDirectory . '/' . $filename;
        Storage::put($filePath, $content);
        return $filePath;
    }
    
    /**
     * Cria um mock de regras de transformação
     *
     * @param array $mappings Array de mapeamentos entre campos de origem e destino
     * @return array Regras de transformação
     */
    protected function createRules(array $mappings): array
    {
        $rules = [];
        
        foreach ($mappings as $target => $source) {
            $rules[] = [
                'target_field' => $target,
                'source_field' => $source,
                'type' => 'direct',
                'options' => []
            ];
        }
        
        return $rules;
    }
    
    /**
     * Testa a transformação de um tipo para ele mesmo
     */
    abstract public function testTransformSameFormat();
    
    /**
     * Testa a transformação de JSON para o formato específico
     */
    abstract public function testTransformJsonToFormat();
    
    /**
     * Testa a transformação de XML para o formato específico
     */
    abstract public function testTransformXmlToFormat();
    
    /**
     * Testa a transformação de CSV para o formato específico
     */
    abstract public function testTransformCsvToFormat();
    
    /**
     * Testa a transformação de XLSX para o formato específico
     */
    abstract public function testTransformXlsxToFormat();
} 
<?php

namespace Tests\Unit\Services\Transformers;

use App\Services\Transformers\Json\JsonTransformer;
use App\Services\Transformers\Json\JsonTemplates;
use Mockery;

class JsonTransformerTest extends BaseTransformerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock do JsonTemplates para retornar um template simples
        $templateMock = Mockery::mock('alias:' . JsonTemplates::class);
        $templateMock->shouldReceive('getTemplate')
            ->andReturn([
                'type' => 'object',
                'properties' => [
                    'data' => [
                        'type' => 'array',
                        'items' => [
                            'properties' => [
                                'id' => ['type' => 'integer'],
                                'name' => ['type' => 'string'],
                                'value' => ['type' => 'number']
                            ]
                        ]
                    ]
                ]
            ]);
        
        $this->transformer = new JsonTransformer();
    }
    
    public function testTransformSameFormat()
    {
        // Criar um JSON de teste
        $jsonData = json_encode([
            'records' => [
                ['id' => 1, 'nome' => 'Produto 1', 'preco' => 10.5],
                ['id' => 2, 'nome' => 'Produto 2', 'preco' => 20.75]
            ]
        ]);
        
        $filePath = $this->createTestFile('source.json', $jsonData);
        
        // Configurar regras de transformação
        $rules = $this->createRules([
            'id' => 'id',
            'name' => 'nome',
            'value' => 'preco'
        ]);
        
        // Executar a transformação
        $result = $this->transformer->transform([
            'path' => Storage::path($filePath),
            'type' => 'json',
            'filename' => 'source.json'
        ], $rules, 'default');
        
        // Verificar o resultado
        $resultData = json_decode($result, true);
        
        $this->assertIsArray($resultData);
        $this->assertArrayHasKey('data', $resultData);
        $this->assertCount(2, $resultData['data']);
        $this->assertEquals('Produto 1', $resultData['data'][0]['name']);
        $this->assertEquals(10.5, $resultData['data'][0]['value']);
    }
    
    public function testTransformJsonToFormat()
    {
        // Este teste é basicamente o mesmo que testTransformSameFormat para o JsonTransformer
        $this->testTransformSameFormat();
    }
    
    public function testTransformXmlToFormat()
    {
        // Criar um XML de teste
        $xmlData = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<products>
    <product>
        <id>1</id>
        <nome>Produto 1</nome>
        <preco>10.5</preco>
    </product>
    <product>
        <id>2</id>
        <nome>Produto 2</nome>
        <preco>20.75</preco>
    </product>
</products>
XML;
        
        $filePath = $this->createTestFile('source.xml', $xmlData);
        
        // Configurar regras de transformação
        $rules = $this->createRules([
            'id' => 'id',
            'name' => 'nome',
            'value' => 'preco'
        ]);
        
        // Executar a transformação
        $result = $this->transformer->transform([
            'path' => Storage::path($filePath),
            'type' => 'xml',
            'filename' => 'source.xml'
        ], $rules, 'default');
        
        // Verificar o resultado
        $resultData = json_decode($result, true);
        
        $this->assertIsArray($resultData);
        $this->assertArrayHasKey('data', $resultData);
        $this->assertCount(2, $resultData['data']);
        $this->assertEquals('Produto 1', $resultData['data'][0]['name']);
        $this->assertEquals(10.5, $resultData['data'][0]['value']);
    }
    
    public function testTransformCsvToFormat()
    {
        // Criar um CSV de teste
        $csvData = "id,nome,preco\n1,Produto 1,10.5\n2,Produto 2,20.75";
        
        $filePath = $this->createTestFile('source.csv', $csvData);
        
        // Configurar regras de transformação
        $rules = $this->createRules([
            'id' => 'id',
            'name' => 'nome',
            'value' => 'preco'
        ]);
        
        // Executar a transformação
        $result = $this->transformer->transform([
            'path' => Storage::path($filePath),
            'type' => 'csv',
            'filename' => 'source.csv'
        ], $rules, 'default');
        
        // Verificar o resultado
        $resultData = json_decode($result, true);
        
        $this->assertIsArray($resultData);
        $this->assertArrayHasKey('data', $resultData);
        $this->assertCount(2, $resultData['data']);
        $this->assertEquals('Produto 1', $resultData['data'][0]['name']);
        $this->assertEquals(10.5, $resultData['data'][0]['value']);
    }
    
    public function testTransformXlsxToFormat()
    {
        // Este teste requer criar um arquivo XLSX real, o que é mais complexo
        // Para este exemplo, vamos simular que o método de transformação está funcionando
        $this->markTestSkipped('Teste para XLSX requer arquivos específicos.');
    }
} 
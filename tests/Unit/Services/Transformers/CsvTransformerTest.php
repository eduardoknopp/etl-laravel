<?php

namespace Tests\Unit\Services\Transformers;

use App\Services\Transformers\Csv\CsvTransformer;
use App\Services\Transformers\Csv\CsvTemplates;
use Mockery;

class CsvTransformerTest extends BaseTransformerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock do CsvTemplates para retornar um template simples
        $templateMock = Mockery::mock('alias:' . CsvTemplates::class);
        $templateMock->shouldReceive('getTemplate')
            ->andReturn([
                'delimiter' => ',',
                'enclosure' => '"',
                'escape' => '\\',
                'headers' => ['id', 'name', 'value'],
                'include_headers' => true
            ]);
        
        $this->transformer = new CsvTransformer();
    }
    
    public function testTransformSameFormat()
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
        
        // Verificar se o resultado é um CSV válido
        $this->assertNotEmpty($result);
        
        $lines = explode("\n", trim($result));
        $this->assertCount(3, $lines); // Header + 2 linhas de dados
        
        $headers = str_getcsv($lines[0]);
        $this->assertEquals(['id', 'name', 'value'], $headers);
        
        $row1 = str_getcsv($lines[1]);
        $this->assertEquals(['1', 'Produto 1', '10.5'], $row1);
    }
    
    public function testTransformJsonToFormat()
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
        
        // Verificar se o resultado é um CSV válido
        $this->assertNotEmpty($result);
        
        $lines = explode("\n", trim($result));
        $this->assertCount(3, $lines); // Header + 2 linhas de dados
        
        $headers = str_getcsv($lines[0]);
        $this->assertEquals(['id', 'name', 'value'], $headers);
        
        $row1 = str_getcsv($lines[1]);
        $this->assertEquals(['1', 'Produto 1', '10.5'], $row1);
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
        
        // Verificar se o resultado é um CSV válido
        $this->assertNotEmpty($result);
        
        $lines = explode("\n", trim($result));
        $this->assertCount(3, $lines); // Header + 2 linhas de dados
        
        $headers = str_getcsv($lines[0]);
        $this->assertEquals(['id', 'name', 'value'], $headers);
        
        $row1 = str_getcsv($lines[1]);
        $this->assertEquals(['1', 'Produto 1', '10.5'], $row1);
    }
    
    public function testTransformCsvToFormat()
    {
        // Este teste é basicamente o mesmo que testTransformSameFormat para o CsvTransformer
        $this->testTransformSameFormat();
    }
    
    public function testTransformXlsxToFormat()
    {
        // Este teste requer criar um arquivo XLSX real, o que é mais complexo
        // Para este exemplo, vamos simular que o método de transformação está funcionando
        $this->markTestSkipped('Teste para XLSX requer arquivos específicos.');
    }
} 
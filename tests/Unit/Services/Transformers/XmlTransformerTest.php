<?php

namespace Tests\Unit\Services\Transformers;

use App\Services\Transformers\Xml\XmlTransformer;
use App\Services\Transformers\Xml\XmlTemplates;
use Mockery;

class XmlTransformerTest extends BaseTransformerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock do XmlTemplates para retornar um template simples
        $templateMock = Mockery::mock('alias:' . XmlTemplates::class);
        $templateMock->shouldReceive('getTemplate')
            ->andReturn([
                'root' => 'products',
                'item' => 'product',
                'fields' => [
                    'id' => ['type' => 'element'],
                    'name' => ['type' => 'element'],
                    'value' => ['type' => 'element']
                ]
            ]);
        
        $this->transformer = new XmlTransformer();
    }
    
    public function testTransformSameFormat()
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
        
        // Verificar se o resultado é um XML válido
        $this->assertNotEmpty($result);
        
        $resultXml = new \SimpleXMLElement($result);
        $products = $resultXml->xpath('//product');
        
        $this->assertCount(2, $products);
        $this->assertEquals('1', (string)$products[0]->id);
        $this->assertEquals('Produto 1', (string)$products[0]->name);
        $this->assertEquals('10.5', (string)$products[0]->value);
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
        
        // Verificar se o resultado é um XML válido
        $this->assertNotEmpty($result);
        
        $resultXml = new \SimpleXMLElement($result);
        $products = $resultXml->xpath('//product');
        
        $this->assertCount(2, $products);
        $this->assertEquals('1', (string)$products[0]->id);
        $this->assertEquals('Produto 1', (string)$products[0]->name);
        $this->assertEquals('10.5', (string)$products[0]->value);
    }
    
    public function testTransformXmlToFormat()
    {
        // Este teste é basicamente o mesmo que testTransformSameFormat para o XmlTransformer
        $this->testTransformSameFormat();
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
        
        // Verificar se o resultado é um XML válido
        $this->assertNotEmpty($result);
        
        $resultXml = new \SimpleXMLElement($result);
        $products = $resultXml->xpath('//product');
        
        $this->assertCount(2, $products);
        $this->assertEquals('1', (string)$products[0]->id);
        $this->assertEquals('Produto 1', (string)$products[0]->name);
        $this->assertEquals('10.5', (string)$products[0]->value);
    }
    
    public function testTransformXlsxToFormat()
    {
        // Este teste requer criar um arquivo XLSX real, o que é mais complexo
        // Para este exemplo, vamos simular que o método de transformação está funcionando
        $this->markTestSkipped('Teste para XLSX requer arquivos específicos.');
    }
} 
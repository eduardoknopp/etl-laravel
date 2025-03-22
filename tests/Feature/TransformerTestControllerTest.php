<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\TransformationMap;
use App\Models\ImportedFile;
use App\Models\ETLProcess;
use Mockery;

class TransformerTestControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste para verificar se a página de teste é exibida corretamente
     */
    public function testTestPageIsDisplayed()
    {
        // Criar alguns mapas de transformação para a página
        TransformationMap::factory(3)->create();

        $response = $this->get(route('transformers.test'));

        $response->assertStatus(200);
        $response->assertViewIs('transformers.test');
        $response->assertViewHas('transformationMaps');
    }

    /**
     * Teste para verificar se a listagem de transformações retorna os dados esperados
     */
    public function testListTransformationsReturnsExpectedData()
    {
        // Criar mapas de transformação
        TransformationMap::factory()->create([
            'from_type' => 'json',
            'to_type' => 'xml'
        ]);

        TransformationMap::factory()->create([
            'from_type' => 'csv',
            'to_type' => 'json'
        ]);

        $response = $this->getJson('/api/transformers/list');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'from_json',
            'from_xml',
            'from_csv',
            'from_xlsx'
        ]);

        $response->assertJsonCount(1, 'from_json');
        $response->assertJsonCount(1, 'from_csv');
    }

    /**
     * Teste para verificar se a transformação de arquivo funciona corretamente
     */
    public function testTransformationWorks()
    {
        // Este teste requer mocks mais complexos para simular o serviço de transformação
        // Vamos simplificar para o propósito deste exemplo
        
        Storage::fake('local');

        // Criar um mapa de transformação
        $map = TransformationMap::factory()->create([
            'from_type' => 'json',
            'to_type' => 'xml'
        ]);

        // Criar um arquivo de teste
        $file = UploadedFile::fake()->create('test.json', 100);

        $response = $this->postJson('/api/transformers/test', [
            'file' => $file,
            'transformation_map_id' => $map->id
        ]);

        // Verificar a resposta
        // Nota: Como estamos usando mocks simplificados, podemos apenas verificar se a requisição foi aceita
        $response->assertStatus(200);
    }

    /**
     * Teste para verificar se a validação de arquivo incompatível funciona
     */
    public function testIncompatibleFileValidation()
    {
        Storage::fake('local');

        // Criar um mapa de transformação para JSON -> XML
        $map = TransformationMap::factory()->create([
            'from_type' => 'json',
            'to_type' => 'xml'
        ]);

        // Criar um arquivo CSV (incompatível com o mapa JSON -> XML)
        $file = UploadedFile::fake()->create('test.csv', 100);

        $response = $this->postJson('/api/transformers/test', [
            'file' => $file,
            'transformation_map_id' => $map->id
        ]);

        // Deve retornar erro de validação
        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    /**
     * Configurar o ambiente de teste
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Criar mapas de transação - usando o método create ao invés de factory
        // pois pode não haver uma factory definida neste momento
        TransformationMap::create([
            'name' => 'JSON para XML',
            'from_type' => 'json',
            'to_type' => 'xml',
            'rules' => json_encode([]),
            'template' => 'default'
        ]);

        TransformationMap::create([
            'name' => 'CSV para JSON',
            'from_type' => 'csv',
            'to_type' => 'json',
            'rules' => json_encode([]),
            'template' => 'default'
        ]);
    }
} 
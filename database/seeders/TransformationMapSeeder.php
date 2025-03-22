<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransformationMap;

class TransformationMapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mapa 1: JSON para XML
        TransformationMap::create([
            'name' => 'JSON para XML',
            'description' => 'Converte arquivo JSON para formato XML',
            'from_type' => 'json',
            'to_type' => 'xml',
            'rules' => [
                'mappings' => [
                    ['target_field' => 'id', 'source_field' => 'id', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'nome', 'source_field' => 'nome', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'preco', 'source_field' => 'preco', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'estoque', 'source_field' => 'estoque', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'categoria', 'source_field' => 'categoria', 'type' => 'direct', 'options' => []],
                ],
                'sections' => [
                    'header' => [],
                    'row' => [],
                    'footer' => []
                ]
            ],
            'template' => 'default',
        ]);

        // Mapa 2: XML para JSON
        TransformationMap::create([
            'name' => 'XML para JSON',
            'description' => 'Converte arquivo XML para formato JSON',
            'from_type' => 'xml',
            'to_type' => 'json',
            'rules' => [
                'mappings' => [
                    ['target_field' => 'id', 'source_field' => 'id', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'nome', 'source_field' => 'nome', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'preco', 'source_field' => 'preco', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'estoque', 'source_field' => 'estoque', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'categoria', 'source_field' => 'categoria', 'type' => 'direct', 'options' => []],
                ],
                'sections' => [
                    'header' => [],
                    'row' => [],
                    'footer' => []
                ]
            ],
            'template' => 'default',
        ]);

        // Mapa 3: CSV para JSON
        TransformationMap::create([
            'name' => 'CSV para JSON',
            'description' => 'Converte arquivo CSV para formato JSON',
            'from_type' => 'csv',
            'to_type' => 'json',
            'rules' => [
                'mappings' => [
                    ['target_field' => 'id', 'source_field' => 'id', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'nome', 'source_field' => 'nome', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'preco', 'source_field' => 'preco', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'estoque', 'source_field' => 'estoque', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'categoria', 'source_field' => 'categoria', 'type' => 'direct', 'options' => []],
                ],
                'sections' => [
                    'header' => [],
                    'row' => [],
                    'footer' => []
                ]
            ],
            'template' => 'default',
        ]);

        // Mapa 4: JSON para CSV
        TransformationMap::create([
            'name' => 'JSON para CSV',
            'description' => 'Converte arquivo JSON para formato CSV',
            'from_type' => 'json',
            'to_type' => 'csv',
            'rules' => [
                'mappings' => [
                    ['target_field' => 'id', 'source_field' => 'id', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'nome', 'source_field' => 'nome', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'preco', 'source_field' => 'preco', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'estoque', 'source_field' => 'estoque', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'categoria', 'source_field' => 'categoria', 'type' => 'direct', 'options' => []],
                ],
                'sections' => [
                    'header' => [],
                    'row' => [],
                    'footer' => []
                ]
            ],
            'template' => 'default',
        ]);

        // Mapa 5: XLSX para CSV
        TransformationMap::create([
            'name' => 'XLSX para CSV',
            'description' => 'Converte arquivo Excel para formato CSV',
            'from_type' => 'xlsx',
            'to_type' => 'csv',
            'rules' => [
                'mappings' => [
                    ['target_field' => 'id', 'source_field' => 'id', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'nome', 'source_field' => 'nome', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'preco', 'source_field' => 'preco', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'estoque', 'source_field' => 'estoque', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'categoria', 'source_field' => 'categoria', 'type' => 'direct', 'options' => []],
                ],
                'sections' => [
                    'header' => [],
                    'row' => [],
                    'footer' => []
                ]
            ],
            'template' => 'default',
        ]);

        // Mapa 6: CSV para XLSX
        TransformationMap::create([
            'name' => 'CSV para XLSX',
            'description' => 'Converte arquivo CSV para formato Excel',
            'from_type' => 'csv',
            'to_type' => 'xlsx',
            'rules' => [
                'mappings' => [
                    ['target_field' => 'id', 'source_field' => 'id', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'nome', 'source_field' => 'nome', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'preco', 'source_field' => 'preco', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'estoque', 'source_field' => 'estoque', 'type' => 'direct', 'options' => []],
                    ['target_field' => 'categoria', 'source_field' => 'categoria', 'type' => 'direct', 'options' => []],
                ],
                'sections' => [
                    'header' => [],
                    'row' => [],
                    'footer' => []
                ]
            ],
            'template' => 'default',
        ]);
    }
} 
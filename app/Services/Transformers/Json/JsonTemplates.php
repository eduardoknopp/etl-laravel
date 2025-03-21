<?php

namespace App\Services\Transformers\Json;

class JsonTemplates
{
    /**
     * Obtém o template JSON com base no nome do template
     *
     * @param string $templateName Nome do template
     * @return array Estrutura do template JSON
     */
    public static function getTemplate(string $templateName = 'default'): array
    {
        $method = 'get' . ucfirst($templateName) . 'Template';
        
        if (method_exists(self::class, $method)) {
            return self::{$method}();
        }
        
        return self::getDefaultTemplate();
    }
    
    /**
     * Template JSON padrão
     *
     * @return array
     */
    public static function getDefaultTemplate(): array
    {
        return [
            'header' => [
                'title' => 'Exportação de Dados',
                'description' => 'Exportação genérica de dados',
                'date' => date('Y-m-d'),
            ],
            'items' => [
                'name' => 'itens',
                'fields' => [
                    'id' => [
                        'map' => 'ID',
                        'required' => true,
                    ],
                    'nome' => [
                        'map' => 'Nome',
                        'required' => true,
                    ],
                    'email' => [
                        'map' => 'Email',
                        'required' => false,
                    ],
                    'telefone' => [
                        'map' => 'Telefone',
                        'required' => false,
                    ],
                    'data_criacao' => [
                        'map' => 'Data',
                        'required' => false,
                        'format' => 'date',
                    ],
                ],
            ],
            'footer' => [
                'totalItems' => 0,
                'version' => '1.0',
            ],
        ];
    }
    
    /**
     * Template para clientes
     *
     * @return array
     */
    public static function getClientesTemplate(): array
    {
        return [
            'header' => [
                'title' => 'Exportação de Clientes',
                'description' => 'Lista de clientes cadastrados no sistema',
                'date' => date('Y-m-d'),
            ],
            'items' => [
                'name' => 'clientes',
                'fields' => [
                    'id' => [
                        'map' => 'Código',
                        'required' => true,
                    ],
                    'nome' => [
                        'map' => 'Nome Completo',
                        'required' => true,
                    ],
                    'email' => [
                        'map' => 'Email',
                        'required' => false,
                    ],
                    'telefone' => [
                        'map' => 'Telefone',
                        'required' => false,
                    ],
                    'celular' => [
                        'map' => 'Celular',
                        'required' => false,
                    ],
                    'endereco' => [
                        'map' => 'Endereço',
                        'required' => false,
                    ],
                    'numero' => [
                        'map' => 'Número',
                        'required' => false,
                    ],
                    'complemento' => [
                        'map' => 'Complemento',
                        'required' => false,
                    ],
                    'bairro' => [
                        'map' => 'Bairro',
                        'required' => false,
                    ],
                    'cidade' => [
                        'map' => 'Cidade',
                        'required' => false,
                    ],
                    'estado' => [
                        'map' => 'Estado',
                        'required' => false,
                    ],
                    'cep' => [
                        'map' => 'CEP',
                        'required' => false,
                    ],
                    'data_cadastro' => [
                        'map' => 'Data Cadastro',
                        'required' => false,
                        'format' => 'date',
                    ],
                    'status' => [
                        'map' => 'Status',
                        'required' => false,
                    ],
                ],
            ],
            'footer' => [
                'totalClientes' => 0,
                'version' => '1.0',
            ],
        ];
    }
    
    /**
     * Template para produtos
     *
     * @return array
     */
    public static function getProdutosTemplate(): array
    {
        return [
            'header' => [
                'title' => 'Catálogo de Produtos',
                'description' => 'Lista de produtos disponíveis no sistema',
                'date' => date('Y-m-d'),
            ],
            'items' => [
                'name' => 'produtos',
                'fields' => [
                    'sku' => [
                        'map' => 'SKU',
                        'required' => true,
                    ],
                    'nome' => [
                        'map' => 'Nome Produto',
                        'required' => true,
                    ],
                    'descricao' => [
                        'map' => 'Descrição',
                        'required' => false,
                    ],
                    'categoria' => [
                        'map' => 'Categoria',
                        'required' => false,
                    ],
                    'preco' => [
                        'map' => 'Valor',
                        'required' => true,
                        'format' => 'price',
                    ],
                    'estoque' => [
                        'map' => 'Estoque',
                        'required' => false,
                        'format' => 'number',
                    ],
                    'peso' => [
                        'map' => 'Peso',
                        'required' => false,
                    ],
                    'dimensoes' => [
                        'map' => 'Dimensões',
                        'required' => false,
                    ],
                    'status' => [
                        'map' => 'Status',
                        'required' => false,
                    ],
                    'data_atualizacao' => [
                        'map' => 'Data Atualização',
                        'required' => false,
                        'format' => 'date',
                    ],
                ],
            ],
            'footer' => [
                'totalProdutos' => 0,
                'version' => '1.0',
            ],
        ];
    }
    
    /**
     * Template para relatório financeiro
     *
     * @return array
     */
    public static function getRelatorioFinanceiroTemplate(): array
    {
        return [
            'header' => [
                'title' => 'Relatório Financeiro',
                'description' => 'Relatório de transações financeiras',
                'date' => date('Y-m-d'),
                'periodo' => [
                    'inicio' => date('Y-m-01'),
                    'fim' => date('Y-m-t'),
                ],
            ],
            'items' => [
                'name' => 'transacoes',
                'fields' => [
                    'id' => [
                        'map' => 'ID Transação',
                        'required' => true,
                    ],
                    'data' => [
                        'map' => 'Data',
                        'required' => true,
                        'format' => 'date',
                    ],
                    'tipo' => [
                        'map' => 'Tipo',
                        'required' => true,
                    ],
                    'descricao' => [
                        'map' => 'Descrição',
                        'required' => true,
                    ],
                    'categoria' => [
                        'map' => 'Categoria',
                        'required' => false,
                    ],
                    'valor' => [
                        'map' => 'Valor',
                        'required' => true,
                        'format' => 'price',
                    ],
                    'conta' => [
                        'map' => 'Conta',
                        'required' => false,
                    ],
                    'status' => [
                        'map' => 'Status',
                        'required' => false,
                    ],
                    'referencia' => [
                        'map' => 'Referência',
                        'required' => false,
                    ],
                ],
            ],
            'footer' => [
                'totalReceitas' => 0,
                'totalDespesas' => 0,
                'saldo' => 0,
                'version' => '1.0',
            ],
        ];
    }
} 
<?php

namespace App\Services\Transformers\Xml;

class XmlTemplates
{
    /**
     * Obtém o template XML com base no nome do template
     *
     * @param string $templateName Nome do template
     * @return array Configuração do template
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
     * Template padrão para arquivos XML
     * 
     * @return array Configuração do template
     */
    public static function getDefaultTemplate(): array
    {
        return [
            'root' => [
                'name' => 'data',
                'attributes' => [
                    'version' => '1.0',
                    'generated' => date('Y-m-d H:i:s'),
                ],
            ],
            'data' => [
                'item' => [
                    'name' => 'item',
                    'attributes' => [],
                    'children' => [
                        [
                            'name' => 'id',
                            'value' => ['field' => 'ID'],
                        ],
                        [
                            'name' => 'nome',
                            'value' => ['field' => 'Nome'],
                        ],
                        [
                            'name' => 'email',
                            'value' => ['field' => 'Email'],
                        ],
                        [
                            'name' => 'telefone',
                            'value' => ['field' => 'Telefone'],
                        ],
                        [
                            'name' => 'data',
                            'value' => ['field' => 'Data'],
                        ],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Template para clientes
     * 
     * @return array Configuração do template
     */
    public static function getClientesTemplate(): array
    {
        return [
            'root' => [
                'name' => 'clientes',
                'attributes' => [
                    'version' => '1.0',
                    'generated' => date('Y-m-d H:i:s'),
                ],
            ],
            'header' => [
                'element' => [
                    'name' => 'header',
                    'children' => [
                        [
                            'name' => 'title',
                            'value' => 'Lista de Clientes',
                        ],
                        [
                            'name' => 'description',
                            'value' => 'Exportação de dados de clientes',
                        ],
                        [
                            'name' => 'date',
                            'value' => date('Y-m-d'),
                        ],
                    ],
                ],
            ],
            'data' => [
                'item' => [
                    'name' => 'cliente',
                    'attributes' => [
                        'id' => ['field' => 'Código'],
                    ],
                    'children' => [
                        [
                            'name' => 'nome',
                            'value' => ['field' => 'Nome Completo'],
                        ],
                        [
                            'name' => 'email',
                            'value' => ['field' => 'Email'],
                        ],
                        [
                            'name' => 'telefone',
                            'value' => ['field' => 'Telefone'],
                        ],
                        [
                            'name' => 'celular',
                            'value' => ['field' => 'Celular'],
                        ],
                        [
                            'name' => 'endereco',
                            'children' => [
                                [
                                    'name' => 'logradouro',
                                    'value' => ['field' => 'Endereço'],
                                ],
                                [
                                    'name' => 'numero',
                                    'value' => ['field' => 'Número'],
                                ],
                                [
                                    'name' => 'complemento',
                                    'value' => ['field' => 'Complemento'],
                                ],
                                [
                                    'name' => 'bairro',
                                    'value' => ['field' => 'Bairro'],
                                ],
                                [
                                    'name' => 'cidade',
                                    'value' => ['field' => 'Cidade'],
                                ],
                                [
                                    'name' => 'estado',
                                    'value' => ['field' => 'Estado'],
                                ],
                                [
                                    'name' => 'cep',
                                    'value' => ['field' => 'CEP'],
                                ],
                            ],
                        ],
                        [
                            'name' => 'data_cadastro',
                            'value' => ['field' => 'Data Cadastro'],
                        ],
                        [
                            'name' => 'status',
                            'value' => ['field' => 'Status'],
                        ],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Template para pedidos
     * 
     * @return array Configuração do template
     */
    public static function getPedidosTemplate(): array
    {
        return [
            'root' => [
                'name' => 'pedidos',
                'attributes' => [
                    'version' => '1.0',
                    'generated' => date('Y-m-d H:i:s'),
                ],
            ],
            'header' => [
                'element' => [
                    'name' => 'header',
                    'children' => [
                        [
                            'name' => 'title',
                            'value' => 'Lista de Pedidos',
                        ],
                        [
                            'name' => 'description',
                            'value' => 'Exportação de dados de pedidos',
                        ],
                        [
                            'name' => 'date',
                            'value' => date('Y-m-d'),
                        ],
                    ],
                ],
            ],
            'data' => [
                'item' => [
                    'name' => 'pedido',
                    'attributes' => [
                        'numero' => ['field' => 'Número Pedido'],
                        'data' => ['field' => 'Data Pedido'],
                    ],
                    'children' => [
                        [
                            'name' => 'cliente',
                            'attributes' => [
                                'id' => ['field' => 'ID Cliente'],
                            ],
                            'value' => ['field' => 'Nome Cliente'],
                        ],
                        [
                            'name' => 'itens',
                            'children' => [
                                [
                                    'name' => 'item',
                                    'attributes' => [
                                        'codigo' => ['field' => 'Código Produto'],
                                        'quantidade' => ['field' => 'Quantidade'],
                                    ],
                                    'value' => ['field' => 'Nome Produto'],
                                ],
                            ],
                        ],
                        [
                            'name' => 'valor_total',
                            'value' => ['field' => 'Valor Total'],
                        ],
                        [
                            'name' => 'status',
                            'value' => ['field' => 'Status Pedido'],
                        ],
                        [
                            'name' => 'pagamento',
                            'attributes' => [
                                'tipo' => ['field' => 'Tipo Pagamento'],
                            ],
                            'value' => ['field' => 'Status Pagamento'],
                        ],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Template para produtos
     * 
     * @return array Configuração do template
     */
    public static function getProdutosTemplate(): array
    {
        return [
            'root' => [
                'name' => 'produtos',
                'attributes' => [
                    'version' => '1.0',
                    'generated' => date('Y-m-d H:i:s'),
                ],
            ],
            'header' => [
                'element' => [
                    'name' => 'header',
                    'children' => [
                        [
                            'name' => 'title',
                            'value' => 'Catálogo de Produtos',
                        ],
                        [
                            'name' => 'description',
                            'value' => 'Exportação de dados de produtos',
                        ],
                        [
                            'name' => 'date',
                            'value' => date('Y-m-d'),
                        ],
                    ],
                ],
            ],
            'data' => [
                'item' => [
                    'name' => 'produto',
                    'attributes' => [
                        'sku' => ['field' => 'SKU'],
                    ],
                    'children' => [
                        [
                            'name' => 'nome',
                            'value' => ['field' => 'Nome Produto'],
                        ],
                        [
                            'name' => 'descricao',
                            'value' => ['field' => 'Descrição'],
                        ],
                        [
                            'name' => 'categoria',
                            'value' => ['field' => 'Categoria'],
                        ],
                        [
                            'name' => 'preco',
                            'value' => ['field' => 'Valor'],
                        ],
                        [
                            'name' => 'estoque',
                            'value' => ['field' => 'Estoque'],
                        ],
                        [
                            'name' => 'peso',
                            'value' => ['field' => 'Peso'],
                        ],
                        [
                            'name' => 'dimensoes',
                            'value' => ['field' => 'Dimensões'],
                        ],
                        [
                            'name' => 'status',
                            'value' => ['field' => 'Status'],
                        ],
                        [
                            'name' => 'data_atualizacao',
                            'value' => ['field' => 'Data Atualização'],
                        ],
                    ],
                ],
            ],
        ];
    }
} 
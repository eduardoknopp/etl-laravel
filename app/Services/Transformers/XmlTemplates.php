<?php

namespace App\Services\Transformers;

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
     * Template XML padrão
     *
     * @return array
     */
    public static function getDefaultTemplate(): array
    {
        return [
            'root_element' => 'Document',
            'declaration' => '<?xml version="1.0" encoding="UTF-8"?>',
            'namespaces' => [],
            'structure' => [
                'Record' => [
                    'type' => 'collection', // Indica que este elemento se repete para cada item
                    'attributes' => [],
                    'elements' => [
                        'ID' => ['type' => 'field', 'attributes' => []],
                        'Name' => ['type' => 'field', 'attributes' => []],
                        'Email' => ['type' => 'field', 'attributes' => []],
                        'Phone' => ['type' => 'field', 'attributes' => []],
                        'Address' => ['type' => 'field', 'attributes' => []],
                        'CreatedAt' => ['type' => 'field', 'attributes' => []]
                    ]
                ]
            ],
            'footer' => [],
            'attributes' => [
                'version' => '1.0',
                'generated' => 'true'
            ]
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
            'root_element' => 'Clientes',
            'declaration' => '<?xml version="1.0" encoding="UTF-8"?>',
            'namespaces' => [],
            'structure' => [
                'Cliente' => [
                    'type' => 'collection',
                    'attributes' => ['tipo' => 'pessoa'],
                    'elements' => [
                        'ID' => ['type' => 'field', 'attributes' => []],
                        'NomeCompleto' => ['type' => 'field', 'attributes' => []],
                        'Email' => ['type' => 'field', 'attributes' => ['principal' => 'true']],
                        'Telefone' => ['type' => 'field', 'attributes' => []],
                        'Endereco' => [
                            'type' => 'group',
                            'attributes' => [],
                            'elements' => [
                                'Logradouro' => ['type' => 'field', 'attributes' => []],
                                'Numero' => ['type' => 'field', 'attributes' => []],
                                'Complemento' => ['type' => 'field', 'attributes' => []],
                                'Bairro' => ['type' => 'field', 'attributes' => []],
                                'Cidade' => ['type' => 'field', 'attributes' => []],
                                'Estado' => ['type' => 'field', 'attributes' => []],
                                'CEP' => ['type' => 'field', 'attributes' => []]
                            ]
                        ],
                        'DataCadastro' => ['type' => 'field', 'attributes' => ['formato' => 'data']]
                    ]
                ]
            ],
            'footer' => [
                'TotalClientes' => ['type' => 'count', 'count_of' => 'Cliente']
            ],
            'attributes' => [
                'version' => '1.0',
                'gerado_em' => 'timestamp'
            ]
        ];
    }
    
    /**
     * Template para pedidos
     *
     * @return array
     */
    public static function getPedidosTemplate(): array
    {
        return [
            'root_element' => 'Pedidos',
            'declaration' => '<?xml version="1.0" encoding="UTF-8"?>',
            'namespaces' => [
                'xsi' => 'http://www.w3.org/2001/XMLSchema-instance'
            ],
            'header' => [
                'Empresa' => ['type' => 'fixed', 'value' => 'Minha Empresa'],
                'DataGeracao' => ['type' => 'field', 'format' => 'datetime']
            ],
            'structure' => [
                'Pedido' => [
                    'type' => 'collection',
                    'attributes' => ['moeda' => 'BRL'],
                    'elements' => [
                        'NumeroPedido' => ['type' => 'field', 'attributes' => []],
                        'DataPedido' => ['type' => 'field', 'attributes' => ['formato' => 'data']],
                        'Cliente' => [
                            'type' => 'group',
                            'attributes' => [],
                            'elements' => [
                                'ID' => ['type' => 'field', 'attributes' => []],
                                'Nome' => ['type' => 'field', 'attributes' => []],
                                'Email' => ['type' => 'field', 'attributes' => []]
                            ]
                        ],
                        'Itens' => [
                            'type' => 'group',
                            'attributes' => [],
                            'elements' => [
                                'Item' => [
                                    'type' => 'collection',
                                    'attributes' => [],
                                    'elements' => [
                                        'SKU' => ['type' => 'field', 'attributes' => []],
                                        'Descricao' => ['type' => 'field', 'attributes' => []],
                                        'Quantidade' => ['type' => 'field', 'attributes' => []],
                                        'PrecoUnitario' => ['type' => 'field', 'attributes' => []],
                                        'Subtotal' => ['type' => 'field', 'attributes' => []]
                                    ]
                                ]
                            ]
                        ],
                        'ValorTotal' => ['type' => 'field', 'attributes' => []],
                        'FormaPagamento' => ['type' => 'field', 'attributes' => []],
                        'Status' => ['type' => 'field', 'attributes' => []]
                    ]
                ]
            ],
            'footer' => [
                'TotalPedidos' => ['type' => 'count', 'count_of' => 'Pedido'],
                'ValorTotalGeral' => ['type' => 'sum', 'sum_of' => 'Pedido.ValorTotal']
            ],
            'attributes' => [
                'version' => '2.0',
                'gerado_em' => 'timestamp'
            ]
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
            'root_element' => 'Produtos',
            'declaration' => '<?xml version="1.0" encoding="UTF-8"?>',
            'namespaces' => [],
            'structure' => [
                'Produto' => [
                    'type' => 'collection',
                    'attributes' => [],
                    'elements' => [
                        'SKU' => ['type' => 'field', 'attributes' => []],
                        'Nome' => ['type' => 'field', 'attributes' => []],
                        'Descricao' => ['type' => 'field', 'attributes' => []],
                        'Categoria' => ['type' => 'field', 'attributes' => []],
                        'SubCategoria' => ['type' => 'field', 'attributes' => []],
                        'Preco' => ['type' => 'field', 'attributes' => ['moeda' => 'BRL']],
                        'Estoque' => ['type' => 'field', 'attributes' => []],
                        'Dimensoes' => [
                            'type' => 'group',
                            'attributes' => [],
                            'elements' => [
                                'Peso' => ['type' => 'field', 'attributes' => ['unidade' => 'kg']],
                                'Altura' => ['type' => 'field', 'attributes' => ['unidade' => 'cm']],
                                'Largura' => ['type' => 'field', 'attributes' => ['unidade' => 'cm']],
                                'Comprimento' => ['type' => 'field', 'attributes' => ['unidade' => 'cm']]
                            ]
                        ],
                        'Status' => ['type' => 'field', 'attributes' => []]
                    ]
                ]
            ],
            'footer' => [
                'TotalProdutos' => ['type' => 'count', 'count_of' => 'Produto']
            ],
            'attributes' => [
                'version' => '1.0',
                'gerado_em' => 'timestamp'
            ]
        ];
    }
}

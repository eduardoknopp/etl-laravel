<?php

namespace App\Services\Transformers;

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
            // Propriedade raiz/wrapper para os dados 
            'root_property' => 'data',
            
            // Metadados gerais para o documento
            'metadata' => [
                'generated_at' => date('Y-m-d H:i:s'),
                'total_records' => ['field' => 'total'],
                'status' => 'success'
            ],
            
            // Estrutura de cada item
            'item_structure' => [
                'fields' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'address',
                    'created_at'
                ]
            ],
            
            // Opções de formatação
            'options' => [
                'pretty_print' => true,
                'encode_unicode' => true
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
            // Propriedade raiz/wrapper para os dados 
            'root_property' => 'customers',
            
            // Metadados gerais para o documento
            'metadata' => [
                'generated_at' => date('Y-m-d H:i:s'),
                'total_records' => ['field' => 'total'],
                'status' => 'success'
            ],
            
            // Estrutura de cada item
            'item_structure' => [
                'fields' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'status',
                    'created_at',
                    'updated_at'
                ],
                'nested_objects' => [
                    'address' => [
                        'fields' => [
                            'street',
                            'number',
                            'complement',
                            'district',
                            'city',
                            'state',
                            'zipcode'
                        ]
                    ]
                ]
            ],
            
            // Opções de formatação
            'options' => [
                'pretty_print' => true,
                'encode_unicode' => true
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
            // Propriedade raiz/wrapper para os dados 
            'root_property' => 'products',
            
            // Metadados gerais para o documento
            'metadata' => [
                'generated_at' => date('Y-m-d H:i:s'),
                'total_records' => ['field' => 'total'],
                'status' => 'success'
            ],
            
            // Estrutura de cada item
            'item_structure' => [
                'fields' => [
                    'sku',
                    'name',
                    'description',
                    'category',
                    'price',
                    'stock',
                    'active',
                    'updated_at'
                ],
                'nested_objects' => [
                    'dimensions' => [
                        'fields' => [
                            'weight',
                            'width',
                            'height',
                            'length'
                        ]
                    ],
                    'images' => [
                        'is_array' => true,
                        'fields' => [
                            'url',
                            'order'
                        ]
                    ]
                ]
            ],
            
            // Opções de formatação
            'options' => [
                'pretty_print' => true,
                'encode_unicode' => true
            ]
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
            // Propriedade raiz/wrapper para os dados 
            'root_property' => 'financial_report',
            
            // Metadados gerais para o documento
            'metadata' => [
                'generated_at' => date('Y-m-d H:i:s'),
                'period' => ['field' => 'period'],
                'report_type' => 'monthly'
            ],
            
            // Estrutura de cada item - resumo financeiro
            'item_structure' => [
                'fields' => [
                    'total_revenue',
                    'total_expenses',
                    'net_profit',
                    'profit_margin'
                ],
                'nested_objects' => [
                    'revenue_sources' => [
                        'is_array' => true,
                        'fields' => [
                            'source_name',
                            'amount',
                            'percentage'
                        ]
                    ],
                    'expense_categories' => [
                        'is_array' => true,
                        'fields' => [
                            'category_name',
                            'amount',
                            'percentage'
                        ]
                    ],
                    'monthly_summary' => [
                        'is_array' => true,
                        'fields' => [
                            'month',
                            'revenue',
                            'expenses',
                            'profit'
                        ]
                    ]
                ]
            ],
            
            // Opções de formatação
            'options' => [
                'pretty_print' => true,
                'encode_unicode' => true
            ]
        ];
    }
} 
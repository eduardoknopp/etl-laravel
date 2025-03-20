<?php

namespace App\Services\Transformers;

class XlsxTemplates
{
    /**
     * Obtém o template XLSX com base no nome do template
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
     * Template XLSX padrão
     *
     * @return array
     */
    public static function getDefaultTemplate(): array
    {
        return [
            'sheets' => [
                'Principal' => [
                    'headers' => [
                        'ID', 'Nome', 'Email', 'Telefone', 'Endereço', 
                        'Cidade', 'Estado', 'CEP', 'Data'
                    ],
                    'styles' => [
                        'headers' => [
                            'font' => ['bold' => true],
                            'fill' => ['color' => '#CCCCCC']
                        ]
                    ]
                ]
            ],
            'properties' => [
                'creator' => 'Sistema ETL',
                'title' => 'Relatório Exportado',
                'description' => 'Arquivo gerado automaticamente pelo sistema ETL'
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
            'sheets' => [
                'Resumo' => [
                    'headers' => [
                        'Período', 'Total Receitas', 'Total Despesas',
                        'Saldo', 'Taxa Crescimento'
                    ],
                    'styles' => [
                        'headers' => [
                            'font' => ['bold' => true, 'size' => 12],
                            'fill' => ['color' => '#4472C4'],
                            'font_color' => '#FFFFFF'
                        ]
                    ]
                ],
                'Receitas' => [
                    'headers' => [
                        'Data', 'Descrição', 'Categoria', 'Cliente/Fonte',
                        'Valor', 'Método', 'Status'
                    ],
                    'styles' => [
                        'headers' => [
                            'font' => ['bold' => true],
                            'fill' => ['color' => '#A9D08E']
                        ]
                    ]
                ],
                'Despesas' => [
                    'headers' => [
                        'Data', 'Descrição', 'Categoria', 'Fornecedor',
                        'Valor', 'Método', 'Status'
                    ],
                    'styles' => [
                        'headers' => [
                            'font' => ['bold' => true],
                            'fill' => ['color' => '#F8CBAD']
                        ]
                    ]
                ]
            ],
            'properties' => [
                'creator' => 'Sistema Financeiro',
                'title' => 'Relatório Financeiro',
                'description' => 'Relatório financeiro gerado pelo sistema ETL'
            ]
        ];
    }
    
    /**
     * Template para catálogo de produtos
     *
     * @return array
     */
    public static function getCatalogoProdutosTemplate(): array
    {
        return [
            'sheets' => [
                'Produtos' => [
                    'headers' => [
                        'SKU', 'Nome', 'Descrição', 'Categoria', 'Subcategoria', 
                        'Preço Custo', 'Preço Venda', 'Margem', 'Estoque', 
                        'Fornecedor', 'Última Atualização'
                    ],
                    'styles' => [
                        'headers' => [
                            'font' => ['bold' => true, 'size' => 11],
                            'fill' => ['color' => '#5B9BD5'],
                            'font_color' => '#FFFFFF'
                        ]
                    ]
                ]
            ],
            'properties' => [
                'creator' => 'Sistema de Produtos',
                'title' => 'Catálogo de Produtos',
                'description' => 'Catálogo completo de produtos'
            ]
        ];
    }
} 
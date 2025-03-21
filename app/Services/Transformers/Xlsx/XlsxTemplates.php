<?php

namespace App\Services\Transformers\Xlsx;

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
     * Template padrão para arquivos XLSX
     * 
     * @return array Configuração do template
     */
    public static function getDefaultTemplate(): array
    {
        return [
            'properties' => [
                'creator' => 'Sistema ETL',
                'title' => 'Relatório Padrão',
                'description' => 'Relatório gerado pelo sistema ETL',
            ],
            'sheets' => [
                'Dados' => [
                    'headers' => [
                        'ID', 'Nome', 'Email', 'Telefone', 'Criado em', 'Atualizado em'
                    ],
                    'styles' => [
                        'headers' => [
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                            ],
                            'fill' => [
                                'color' => '#DDDDDD',
                            ],
                            'font_color' => '#000000',
                        ],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Template para relatório financeiro
     * 
     * @return array Configuração do template
     */
    public static function getRelatorioFinanceiroTemplate(): array
    {
        return [
            'properties' => [
                'creator' => 'Sistema Financeiro',
                'title' => 'Relatório Financeiro',
                'description' => 'Relatório financeiro gerado pelo sistema ETL',
            ],
            'sheets' => [
                'Receitas' => [
                    'headers' => [
                        'ID', 'Descrição', 'Categoria', 'Valor', 'Data', 'Status'
                    ],
                    'styles' => [
                        'headers' => [
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                            ],
                            'fill' => [
                                'color' => '#C6EFCE',
                            ],
                            'font_color' => '#006100',
                        ],
                    ],
                ],
                'Despesas' => [
                    'headers' => [
                        'ID', 'Descrição', 'Categoria', 'Valor', 'Data', 'Status'
                    ],
                    'styles' => [
                        'headers' => [
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                            ],
                            'fill' => [
                                'color' => '#FFC7CE',
                            ],
                            'font_color' => '#9C0006',
                        ],
                    ],
                ],
                'Resumo' => [
                    'headers' => [
                        'Mês', 'Total Receitas', 'Total Despesas', 'Saldo'
                    ],
                    'styles' => [
                        'headers' => [
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                            ],
                            'fill' => [
                                'color' => '#FFEB9C',
                            ],
                            'font_color' => '#9C6500',
                        ],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Template para catálogo de produtos
     * 
     * @return array Configuração do template
     */
    public static function getCatalogoProdutosTemplate(): array
    {
        return [
            'properties' => [
                'creator' => 'Sistema de Produtos',
                'title' => 'Catálogo de Produtos',
                'description' => 'Catálogo de produtos gerado pelo sistema ETL',
            ],
            'sheets' => [
                'Produtos' => [
                    'headers' => [
                        'Código', 'Nome', 'Categoria', 'Preço', 'Estoque', 'Fornecedor'
                    ],
                    'styles' => [
                        'headers' => [
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                            ],
                            'fill' => [
                                'color' => '#B7DEE8',
                            ],
                            'font_color' => '#0070C0',
                        ],
                    ],
                ],
            ],
        ];
    }
} 
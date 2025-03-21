<?php

namespace App\Services\Transformers\Csv;

class CsvTemplates
{
    /**
     * Obtém o template CSV com base no nome do template
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
     * Template CSV padrão
     *
     * @return array
     */
    public static function getDefaultTemplate(): array
    {
        return [
            'headers' => [
                'ID', 'Nome', 'Email', 'Telefone', 'Endereço', 'Cidade', 'Estado', 'CEP', 'Data'
            ],
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_char' => '\\',
            'has_header_row' => true
        ];
    }
    
    /**
     * Template para dados de cliente
     *
     * @return array
     */
    public static function getClienteTemplate(): array
    {
        return [
            'headers' => [
                'Código', 'Nome Completo', 'Email', 'Telefone', 'Celular', 
                'Endereço', 'Número', 'Complemento', 'Bairro', 'Cidade', 
                'Estado', 'CEP', 'Data Cadastro', 'Status'
            ],
            'delimiter' => ';',
            'enclosure' => '"',
            'escape_char' => '\\',
            'has_header_row' => true
        ];
    }
    
    /**
     * Template para produtos
     *
     * @return array
     */
    public static function getProdutoTemplate(): array
    {
        return [
            'headers' => [
                'SKU', 'Nome Produto', 'Descrição', 'Categoria', 'Valor', 
                'Estoque', 'Peso', 'Dimensões', 'Status', 'Data Atualização'
            ],
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_char' => '\\',
            'has_header_row' => true
        ];
    }
    
    /**
     * Template para transações financeiras
     *
     * @return array
     */
    public static function getFinanceiroTemplate(): array
    {
        return [
            'headers' => [
                'ID Transação', 'Data', 'Tipo', 'Descrição', 'Categoria', 
                'Valor', 'Conta', 'Status', 'Referência'
            ],
            'delimiter' => ';',
            'enclosure' => '"',
            'escape_char' => '\\',
            'has_header_row' => true
        ];
    }
} 
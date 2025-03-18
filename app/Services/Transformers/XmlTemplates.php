<?php

namespace App\Services\Transformers;

class XmlTemplates
{
    // Template principal para Clientes
    public static function clientesTemplate(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
        <Clientes>
            {{rows}}
        </Clientes>
XML;
    }

    // Template principal para Pedidos
    public static function pedidosTemplate(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
        <Pedidos>
            {{header}}
            <Body>
                {{rows}}
            </Body>
            {{footer}}
        </Pedidos>
XML;
    }

    // Header para pedidos
    public static function pedidoHeaderTemplate(): string
    {
        return <<<XML
            <Header>
                <Empresa>Minha Empresa</Empresa>
                <DataGeracao>{{DataGeracao}}</DataGeracao>
            </Header>
XML;
    }

    // Footer para pedidos
    public static function pedidoFooterTemplate(): string
    {
        return <<<XML
            <Footer>
                <TotalPedidos>{{TotalPedidos}}</TotalPedidos>
            </Footer>
XML;
    }

    // Template de linha para Clientes
    public static function clienteRowTemplate(): string
    {
        return <<<XML
            <Cliente>
                <NomeCliente>{{NomeCliente}}</NomeCliente>
                <EmailContato>{{EmailContato}}</EmailContato>
            </Cliente>
XML;
    }

    // Template de linha para Pedidos
    public static function pedidoRowTemplate(): string
    {
        return <<<XML
            <Pedido>
                <NumeroPedido>{{NumeroPedido}}</NumeroPedido>
                <ValorTotal>{{ValorTotal}}</ValorTotal>
            </Pedido>
XML;
    }

    // Retorna o template principal
    public static function getTemplate(string $templateName): string
    {
        return match ($templateName) {
            'clientes' => self::clientesTemplate(),
            'pedidos' => self::pedidosTemplate(),
            default => throw new \InvalidArgumentException("Template '$templateName' não encontrado."),
        };
    }

    // Retorna o template de linha
    public static function getRowTemplate(string $templateName): string
    {
        return match ($templateName) {
            'clientes' => self::clienteRowTemplate(),
            'pedidos' => self::pedidoRowTemplate(),
            default => throw new \InvalidArgumentException("Template de linha '$templateName' não encontrado."),
        };
    }

    // Retorna o template de header, se aplicável
    public static function getHeaderTemplate(string $templateName): string
    {
        return match ($templateName) {
            'pedidos' => self::pedidoHeaderTemplate(),
            default => '',
        };
    }

    // Retorna o template de footer, se aplicável
    public static function getFooterTemplate(string $templateName): string
    {
        return match ($templateName) {
            'pedidos' => self::pedidoFooterTemplate(),
            default => '',
        };
    }
}

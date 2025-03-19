<?php

namespace App\Services\Transformers;

class XmlTransformer implements TransformerInterface
{
    public function transform(array $data, array $rules, string $templateName): string
    {
        // Obtém os templates do XML
        $templateXml    = XmlTemplates::getTemplate($templateName);
        $rowTemplate    = XmlTemplates::getRowTemplate($templateName);
        $headerTemplate = XmlTemplates::getHeaderTemplate($templateName);
        $footerTemplate = XmlTemplates::getFooterTemplate($templateName);

        // Processa o header
        $headerValues = $this->extractValues($rules['header'] ?? [], $data);
        $headerXml    = $this->replacePlaceholders($headerTemplate, $headerValues);

        // Processa as linhas
        $rowsXml = "";
        foreach ($data as $row) {
            $rowValues  = $this->extractValues($rules['row'] ?? [], $row);
            $rowsXml   .= $this->replacePlaceholders($rowTemplate, $rowValues) . "\n";
        }

        // Processa o footer
        $footerValues = $this->extractValues($rules['footer'] ?? [], $data);
        $footerXml    = $this->replacePlaceholders($footerTemplate, $footerValues);

        // Substitui os placeholders no template principal
        $finalXml = $this->replacePlaceholders($templateXml, [
            'header' => $headerXml,
            'rows'   => $rowsXml,
            'footer' => $footerXml,
        ]);

        return $finalXml;
    }

    /**
     * Extrai os valores dos campos com base nas regras definidas
     */
    private function extractValues(array $rules, array $data): array
    {
        $values = [];
        foreach ($rules as $rule) {
            $sourceField      = $rule['source_field'];
            $destinationField = $rule['destination_field'];
            $values[$destinationField] = $data[$sourceField] ?? '';
        }
        return $values;
    }

    /**
     * Substitui automaticamente os placeholders do template
     */
    private function replacePlaceholders(string $template, array $values): string
    {
        preg_match_all('/{{(.*?)}}/', $template, $matches);
        foreach ($matches[1] as $placeholder) {
            $template = str_replace("{{{$placeholder}}}", $values[$placeholder] ?? '', $template);
        }
        return $template;
    }
}

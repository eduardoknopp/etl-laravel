<?php

namespace App\Services\Transformers;

class XmlTransformer implements TransformerInterface
{
    public function transform(array $data, array $rules, string $templateName): string
    {
        // Obtém template XML completo e template de linha
        $templateXml = XmlTemplates::getTemplate($templateName);
        $rowTemplate = XmlTemplates::getRowTemplate($templateName);
        $headerTemplate = XmlTemplates::getHeaderTemplate($templateName);
        $footerTemplate = XmlTemplates::getFooterTemplate($templateName);

        $rowsXml = "";

        foreach ($data as $rowIndex => $row) {
            $currentRowXml = $rowTemplate;

            foreach ($rules as $rule) {
                $sourceField = $rule['source_field'] ?? null;
                $sourceIndex = $rule['source_index'] ?? null;
                $destinationField = $rule['destination_field'] ?? null;
                $destinationIndex = $rule['destination_index'] ?? null;

                // Obtém o valor do source_field pelo índice ou nome do campo
                $value = $sourceIndex !== null && isset($row[$sourceIndex])
                    ? $row[$sourceIndex]
                    : ($row[$sourceField] ?? null);

                if ($value !== null) {
                    $destinationPlaceholder = $destinationIndex !== null
                        ? "{{{$destinationField}_{$destinationIndex}}}"
                        : "{{{$destinationField}}}";

                    $currentRowXml = str_replace($destinationPlaceholder, htmlspecialchars($value), $currentRowXml);
                }
            }

            $rowsXml .= $currentRowXml . "\n";
        }

        // Processa cabeçalho, rodapé e o XML final
        $headerXml = str_replace("{{DataGeracao}}", date('Y-m-d H:i:s'), $headerTemplate);
        $footerXml = str_replace("{{TotalPedidos}}", count($data), $footerTemplate);
        $finalXml = str_replace("{{rows}}", $rowsXml, $templateXml);

        return $headerXml . "\n" . $finalXml . "\n" . $footerXml;
    }
}

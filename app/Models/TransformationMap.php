<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Casts\RulesCast;
use App\DTOs\TransformationMapRule;
use App\Services\Transformers\CsvTemplates;
use App\Services\Transformers\JsonTemplates;
use App\Services\Transformers\XlsxTemplates;
use App\Services\Transformers\XmlTemplates;

class TransformationMap extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'from_type',
        'to_type',
        'rules',
        'template',
    ];

    protected $casts = [
        'rules' => RulesCast::class,
    ];

    /**
     * Lista os formatos de arquivo suportados
     *
     * @return array
     */
    public static function supportedFileTypes(): array
    {
        return [
            'csv' => 'CSV (Valores Separados por Vírgula)',
            'json' => 'JSON (JavaScript Object Notation)',
            'xml' => 'XML (eXtensible Markup Language)',
            'xlsx' => 'Excel (XLSX)',
            'xls' => 'Excel (XLS)',
        ];
    }

    /**
     * Obter templates disponíveis para um tipo de saída
     *
     * @param string $outputType Tipo de saída (csv, json, xml, xlsx)
     * @return array
     */
    public static function getAvailableTemplates(string $outputType): array
    {
        switch (strtolower($outputType)) {
            case 'csv':
                return self::getTemplateNames(CsvTemplates::class);
            case 'json':
                return self::getTemplateNames(JsonTemplates::class);
            case 'xml':
                return self::getTemplateNames(XmlTemplates::class);
            case 'xlsx':
            case 'xls':
                return self::getTemplateNames(XlsxTemplates::class);
            default:
                return ['default' => 'Modelo Padrão'];
        }
    }

    /**
     * Recupera nomes de métodos de template de uma classe
     *
     * @param string $className Nome da classe de template
     * @return array
     */
    private static function getTemplateNames(string $className): array
    {
        $templates = ['default' => 'Modelo Padrão'];
        
        $methods = get_class_methods($className);
        foreach ($methods as $method) {
            if (strpos($method, 'get') === 0 && strpos($method, 'Template') !== false && $method !== 'getTemplate') {
                $templateName = str_replace(['get', 'Template'], '', $method);
                $templateName = lcfirst($templateName);
                $readableName = ucwords(str_replace('_', ' ', $templateName));
                
                $templates[$templateName] = $readableName;
            }
        }
        
        return $templates;
    }

    /**
     * Adiciona uma regra de transformação ao mapa
     *
     * @param TransformationMapRule $rule Regra a ser adicionada
     * @param string $section Seção onde a regra será adicionada (mappings, header, row, footer)
     * @return $this
     */
    public function addRule(TransformationMapRule $rule, string $section = 'mappings'): self
    {
        if ($section === 'mappings') {
            $this->rules['mappings'][] = $rule;
        } else if (in_array($section, ['header', 'row', 'footer'])) {
            $this->rules['sections'][$section][] = $rule;
        }
        
        return $this;
    }

    /**
     * Limpa todas as regras de uma seção específica
     *
     * @param string $section Seção a ser limpa (mappings, header, row, footer)
     * @return $this
     */
    public function clearRules(string $section = 'mappings'): self
    {
        if ($section === 'mappings') {
            $this->rules['mappings'] = [];
        } else if (in_array($section, ['header', 'row', 'footer'])) {
            $this->rules['sections'][$section] = [];
        }
        
        return $this;
    }

    /**
     * Relacionamento com os processos ETL
     */
    public function etlProcesses(): HasMany
    {
        return $this->hasMany(ETLProcess::class, 'map_id');
    }

    /**
     * Verifica se este mapa pode ser aplicado a um determinado arquivo
     *
     * @param ImportedFile $file Arquivo a ser verificado
     * @return bool
     */
    public function isCompatibleWith(ImportedFile $file): bool
    {
        $fileExtension = pathinfo($file->filename, PATHINFO_EXTENSION);
        return strtolower($fileExtension) === strtolower($this->from_type);
    }
}

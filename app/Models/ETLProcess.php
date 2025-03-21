<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ETLProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'map_id',
        'status',
        'processed_at',
    ];

    // Relacionamento com o arquivo importado
    public function importedFile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ImportedFile::class, 'file_id');
    }

    // Relacionamento com o mapa de transformação
    public function transformationMap(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TransformationMap::class, 'map_id');
    }
}

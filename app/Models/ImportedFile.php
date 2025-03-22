<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename', 
        'original_filename',
        'path', 
        'status', 
        'source',
        'size',
        'mime_type',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function etlProcesses(): HasMany
    {
        return $this->hasMany(ETLProcess::class, 'file_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportedFile extends Model
{
    use HasFactory;

    protected $fillable = ['filename', 'path', 'type', 'status', 'source'];

    public function etlProcesses(): HasMany
    {
        return $this->hasMany(ETLProcess::class, 'file_id');
    }
}

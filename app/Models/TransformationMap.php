<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransformationMap extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'from_type',
        'to_type',
        'rules',
    ];

    protected $casts = [
        'rules' => 'array', // O campo 'rules' será um array
    ];
}

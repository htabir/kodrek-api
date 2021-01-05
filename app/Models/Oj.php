<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oj extends Model
{
    use HasFactory;

    protected $casts = [
        'solvedSet' => 'array',
        'unsolvedSet' => 'array'
    ];
     
}

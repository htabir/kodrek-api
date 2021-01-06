<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UvaProblem extends Model
{
    protected $fillable = [
        'uvaId',
        'uvaNum',
    ];

    use HasFactory;
}

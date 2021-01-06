<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresetProblem extends Model
{
    protected $fillable = [
        'presetId',
        'ojName',
        'problemId'
    ];
    use HasFactory;
}

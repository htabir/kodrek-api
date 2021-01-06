<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresetUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'presetId',
        'status',
        'days'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ownerName'
    ];

    // public function preset_users(){
    //     return $this->hasMany(PresetUser::class, 'presetId', 'presetId');
    // }

    // public function preset_problems(){
    //     return $this->hasMany(PresetProblem::class, 'presetId', 'presetId');
    // }
}

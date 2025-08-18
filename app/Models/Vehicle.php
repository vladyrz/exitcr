<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Vehicle extends Model
{
    use HasFactory, HasFilamentComments;

    protected $fillable = [
        'numero_contrato',
        'placa',
        'marca',
        'estilo',
        'año',
        'status',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

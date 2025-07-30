<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Empleado extends Model
{
    use HasFactory, HasFilamentComments;

    protected $fillable = [
        'nombre',
        'correo_empresarial',
        'estado_contrato',
        'estado_progreso',
        'puesto_de_trabajo',
        'cedula',
        'telefono',
        'correo_personal',
        'profesion',
        'placa',
        'direccion',
        'fecha_nacimiento',
        'estado_civil',
        'contrato',
    ];
}

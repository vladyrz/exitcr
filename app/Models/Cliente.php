<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Cliente extends Model
{
    use HasFactory, HasFilamentComments;

    protected $fillable = [
        'nombre',
        'cedula',
        'email',
        'telefono',
        'direccion',
        'contacto_preferido',
        'tipo_cliente',
        'otro_tipo',
        'observaciones',
        'fecha_ingreso',
        'documentos',
    ];

    protected $casts = [
        'documentos' => 'array',
    ];

    public function movimiento()
    {
        return $this->hasMany(Movimiento::class);
    }
}

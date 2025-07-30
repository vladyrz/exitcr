<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Proyecto extends Model
{
    use HasFactory, HasFilamentComments;

    protected $fillable = [
        'nombre',
        'user_id',
        'progreso',
        'estado',
        'prioridad',
        'beneficio_esperado',
        'fecha_solicitud',
        'ultima_actualizacion',
        'observaciones',
        'documentos',
    ];

    protected $casts = [
        'documentos' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

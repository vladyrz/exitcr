<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Vacacion extends Model
{
    use HasFactory, HasFilamentComments;

    protected $fillable = [
        'user_id',
        'tipo_solicitud',
        'observaciones',
        'estado_solicitud',
        'saldo_vacaciones',
        'fecha_permiso',
        'opciones_permiso',
        'hora_inicio',
        'hora_fin',
        'fecha_inicio',
        'fecha_fin',
        'total_vacaciones',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

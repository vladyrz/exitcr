<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Movimiento extends Model
{
    use HasFactory, HasFilamentComments;

    protected $fillable = [
        'tipo_movimiento',
        'fecha_movimiento',
        'user_id',
        'vehicle_id',
        'kilometraje_inicial',
        'kilometraje_final',
        'archivos',
    ];

    protected $casts = [
        'archivos' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

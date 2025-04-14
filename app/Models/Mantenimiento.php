<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Mantenimiento extends Model
{
    use HasFactory, HasFilamentComments;

    protected $fillable = [
        'tipo_mantenimiento',
        'fecha_mantenimiento',
        'user_id',
        'vehicle_id',
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

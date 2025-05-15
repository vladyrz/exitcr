<?php

namespace App\Filament\Personal\Resources\MovimientoResource\Pages;

use App\Filament\Personal\Resources\MovimientoResource;
use App\Mail\MovimientoStatus\Pendiente;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateMovimiento extends CreateRecord
{
    protected static string $resource = MovimientoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asigna automáticamente el ID del usuario autenticado
        $data['user_id'] = Auth::user()->id;
        return $data;
    }

    protected function afterCreate(): void
    {
        $movimiento = $this->record;

        $usuario = Auth::user();
        $vehiculo = Vehicle::find($movimiento->vehicle_id);

        if (!$usuario || !$vehiculo) {
            return; // Protección contra datos incompletos
        }

        $superAdminEmails = User::role('super_admin')->pluck('email')->toArray();

        // Obtener otros usuarios asignados al vehículo, excluyendo al actual
        $otrosUsuarios = $vehiculo->users()
            ->where('users.id', '!=', $usuario->id)
            ->pluck('email')
            ->toArray();

        $dataToSend = [
            'tipo_movimiento' => ucfirst($movimiento->tipo_movimiento),
            'fecha_movimiento' => $movimiento->fecha_movimiento,
            'placa' => $vehiculo->placa,
            'name' => $usuario->name,
            'email' => $usuario->email,
        ];

        // Validar que exista al menos un correo válido
        if ($usuario->email || !empty($otrosUsuarios) || !empty($superAdminEmails)) {
            Mail::to($usuario->email)
                ->cc([...$superAdminEmails, ...$otrosUsuarios])
                ->send(new Pendiente($dataToSend));
        }
    }
}

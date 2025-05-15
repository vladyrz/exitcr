<?php

namespace App\Filament\Resources\MovimientoResource\Pages;

use App\Filament\Resources\MovimientoResource;
use App\Mail\MovimientoStatus\AdminPending;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateMovimiento extends CreateRecord
{
    protected static string $resource = MovimientoResource::class;

    protected function afterCreate(): void
    {
        $movimiento = $this->record;

        $agente = User::find($movimiento->user_id);
        $vehiculo = Vehicle::find($movimiento->vehicle_id);

        if (!$agente || !$vehiculo) {
            return; // Salir si hay datos incompletos
        }

        $superAdminEmails = User::role('super_admin')->pluck('email')->toArray();

        // Obtener otros usuarios asignados al vehículo, excluyendo al agente
        $otrosUsuarios = $vehiculo->users()
            ->where('users.id', '!=', $agente->id)
            ->pluck('email')
            ->toArray();

        $dataToSend = [
            'tipo_movimiento' => ucfirst($movimiento->tipo_movimiento),
            'fecha_movimiento' => $movimiento->fecha_movimiento,
            'name' => $agente->name,
            'email' => $agente->email,
            'placa' => $vehiculo->placa,
        ];

        // Validar que al menos haya un destinatario
        if ($agente->email || !empty($otrosUsuarios)) {
            Mail::to($agente->email)
                ->cc([...$superAdminEmails, ...$otrosUsuarios])
                ->send(new AdminPending($dataToSend));
        }
    }
}

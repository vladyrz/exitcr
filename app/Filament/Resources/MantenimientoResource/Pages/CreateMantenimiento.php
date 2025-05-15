<?php

namespace App\Filament\Resources\MantenimientoResource\Pages;

use App\Filament\Resources\MantenimientoResource;
use App\Mail\MantenimientoStatus\AdminPendiente;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateMantenimiento extends CreateRecord
{
    protected static string $resource = MantenimientoResource::class;

    protected function afterCreate(): void
    {
        $mantenimiento = $this->record;

        $agente = User::find($mantenimiento->user_id);
        $vehiculo = Vehicle::find($mantenimiento->vehicle_id);

        if (!$agente || !$vehiculo) {
            return; // Validación mínima por seguridad
        }

        $superAdminEmails = User::role('super_admin')->pluck('email')->toArray();

        // Obtener otros usuarios asignados al vehículo, excluyendo al agente
        $otrosUsuarios = $vehiculo->users()
            ->where('users.id', '!=', $agente->id)
            ->pluck('email')
            ->toArray();

        $dataToSend = [
            'tipo_mantenimiento' => ucfirst($mantenimiento->tipo_mantenimiento),
            'fecha_mantenimiento' => $mantenimiento->fecha_mantenimiento,
            'name' => $agente->name,
            'email' => $agente->email,
            'placa' => $vehiculo->placa,
        ];

        // Enviar solo si hay destinatarios válidos
        if ($agente->email || !empty($otrosUsuarios) || !empty($superAdminEmails)) {
            Mail::to($agente->email)
                ->cc([...$superAdminEmails, ...$otrosUsuarios])
                ->send(new AdminPendiente($dataToSend));
        }
    }
}

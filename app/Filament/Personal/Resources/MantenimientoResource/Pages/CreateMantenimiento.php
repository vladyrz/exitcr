<?php

namespace App\Filament\Personal\Resources\MantenimientoResource\Pages;

use App\Filament\Personal\Resources\MantenimientoResource;
use App\Mail\MantenimientoStatus\Pendiente;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateMantenimiento extends CreateRecord
{
    protected static string $resource = MantenimientoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar automáticamente el ID del usuario autenticado
        $data['user_id'] = Auth::user()->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $mantenimiento = $this->record;

        $usuario = Auth::user();
        $vehiculo = Vehicle::find($mantenimiento->vehicle_id);

        if (!$usuario || !$vehiculo) {
            return; // Evita errores si algo no se encuentra
        }

        $superAdminEmails = User::role('super_admin')->pluck('email')->toArray();

        // Obtener otros usuarios asignados al vehículo, excluyendo al actual
        $otrosUsuarios = $vehiculo->users()
            ->where('users.id', '!=', $usuario->id)
            ->pluck('email')
            ->toArray();

        $dataToSend = [
            'tipo_mantenimiento' => ucfirst($mantenimiento->tipo_mantenimiento),
            'fecha_mantenimiento' => $mantenimiento->fecha_mantenimiento,
            'placa' => $vehiculo->placa,
            'name' => $usuario->name,
            'email' => $usuario->email,
        ];

        // Enviar solo si hay al menos un destinatario válido
        if ($usuario->email || !empty($otrosUsuarios) || !empty($superAdminEmails)) {
            Mail::to($usuario->email)
                ->cc([...$superAdminEmails, ...$otrosUsuarios])
                ->send(new Pendiente($dataToSend));
        }
    }
}

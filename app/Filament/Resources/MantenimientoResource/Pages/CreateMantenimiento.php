<?php

namespace App\Filament\Resources\MantenimientoResource\Pages;

use App\Filament\Resources\MantenimientoResource;
use App\Mail\MantenimientoStatus\AdminPendiente;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateMantenimiento extends CreateRecord
{
    protected static string $resource = MantenimientoResource::class;

    protected function afterCreate(): void
    {
        $mantenimiento = $this->record;

        $agenteEmail = User::find($mantenimiento->user_id)->email;
        $superAdminEmails = User::role('super_admin')->pluck('email')->toArray();

        $dataToSend = [
            'tipo_mantenimiento' => ucfirst($mantenimiento->tipo_mantenimiento),
            'fecha_mantenimiento' => $mantenimiento->fecha_mantenimiento,
            'name' => User::find($mantenimiento->user_id)->name,
            'email' => $agenteEmail,
            'placa' => Vehicle::find($mantenimiento->vehicle_id)->placa,
        ];

        if ($agenteEmail) {
            Mail::to($agenteEmail)
                ->cc($superAdminEmails)
                ->send(new AdminPendiente($dataToSend));
        }
    }
}

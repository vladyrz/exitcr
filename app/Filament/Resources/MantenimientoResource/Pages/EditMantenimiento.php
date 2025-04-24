<?php

namespace App\Filament\Resources\MantenimientoResource\Pages;

use App\Filament\Resources\MantenimientoResource;
use App\Mail\MantenimientoStatus\Aprobado;
use App\Mail\MantenimientoStatus\Rechazado;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditMantenimiento extends EditRecord
{
    protected static string $resource = MantenimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
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

        if ($agenteEmail && $mantenimiento->mantenimiento_status === 'aprobado') {
            Mail::to($agenteEmail)
                ->cc($superAdminEmails)
                ->send(new Aprobado($dataToSend));
        }

        if ($agenteEmail && $mantenimiento->mantenimiento_status === 'rechazado') {
            Mail::to($agenteEmail)
                ->cc($superAdminEmails)
                ->send(new Rechazado($dataToSend));
        }
    }
}

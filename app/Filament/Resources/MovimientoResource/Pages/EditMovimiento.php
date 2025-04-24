<?php

namespace App\Filament\Resources\MovimientoResource\Pages;

use App\Filament\Resources\MovimientoResource;
use App\Mail\MovimientoStatus\Aprobado;
use App\Mail\MovimientoStatus\Rechazado;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditMovimiento extends EditRecord
{
    protected static string $resource = MovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $movimiento = $this->record;

        $agenteEmail = User::find($movimiento->user_id)->email;
        $superAdminEmails = User::role('super_admin')->pluck('email')->toArray();

        $dataToSend = [
            'tipo_movimiento' => ucfirst($movimiento->tipo_movimiento),
            'fecha_movimiento' => $movimiento->fecha_movimiento,
            'name' => User::find($movimiento->user_id)->name,
            'email' => $agenteEmail,
            'placa' => Vehicle::find($movimiento->vehicle_id)->placa,
        ];

        if ($agenteEmail && $movimiento->movimiento_status === 'aprobado') {
            Mail::to($agenteEmail)
                ->cc($superAdminEmails)
                ->send(new Aprobado($dataToSend));
        }

        if ($agenteEmail && $movimiento->movimiento_status === 'rechazado') {
            Mail::to($agenteEmail)
                ->cc($superAdminEmails)
                ->send(new Rechazado($dataToSend));
        }
    }
}

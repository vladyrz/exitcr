<?php

namespace App\Filament\Resources\MovimientoResource\Pages;

use App\Filament\Resources\MovimientoResource;
use App\Mail\MovimientoStatus\AdminPending;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateMovimiento extends CreateRecord
{
    protected static string $resource = MovimientoResource::class;

    protected function afterCreate(): void
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

        if ($agenteEmail) {
            Mail::to($agenteEmail)
                ->cc($superAdminEmails)
                ->send(new AdminPending($dataToSend));
        }
    }
}

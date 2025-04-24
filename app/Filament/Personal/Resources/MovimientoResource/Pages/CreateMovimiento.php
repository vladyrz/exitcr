<?php

namespace App\Filament\Personal\Resources\MovimientoResource\Pages;

use App\Filament\Personal\Resources\MovimientoResource;
use App\Mail\MovimientoStatus\Pendiente;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateMovimiento extends CreateRecord
{
    protected static string $resource = MovimientoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $movimiento = $this->record;

        $superAdminEmails = User::role('super_admin')->pluck('email')->toArray();

        $dataToSend = [
            'tipo_movimiento' => ucfirst($movimiento->tipo_movimiento),
            'fecha_movimiento' => $movimiento->fecha_movimiento,
            'placa' => Vehicle::find($movimiento->vehicle_id)->placa,
            'name' => User::find($movimiento->user_id)->name,
            'email' => User::find($movimiento->user_id)->email,
        ];

        Mail::to($superAdminEmails)->send(new Pendiente($dataToSend));
    }
}

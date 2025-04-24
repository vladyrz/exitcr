<?php

namespace App\Filament\Personal\Resources\MantenimientoResource\Pages;

use App\Filament\Personal\Resources\MantenimientoResource;
use App\Mail\MantenimientoStatus\Pendiente;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateMantenimiento extends CreateRecord
{
    protected static string $resource = MantenimientoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $mantenimiento = $this->record;

        $superAdminEmails = User::role('super_admin')->pluck('email')->toArray();

        $dataToSend = [
            'tipo_mantenimiento' => ucfirst($mantenimiento->tipo_mantenimiento),
            'fecha_mantenimiento' => $mantenimiento->fecha_mantenimiento,
            'placa' => Vehicle::find($mantenimiento->vehicle_id)->placa,
            'name' => User::find($mantenimiento->user_id)->name,
            'email' => User::find($mantenimiento->user_id)->email,
        ];

        Mail::to($superAdminEmails)->send(new Pendiente($dataToSend));
    }
}

<?php

namespace App\Filament\Personal\Resources\VacacionResource\Pages;

use App\Filament\Personal\Resources\VacacionResource;
use App\Mail\VacacionStatus;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateVacacion extends CreateRecord
{
    protected static string $resource = VacacionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        $user = $record->user;

        $dataToSend = [
            'tipo_solicitud' => $record->tipo_solicitud,
            'name' => $user->name,
            'email' => $user->email,
        ];

        $userRoles = User::role('super_admin')->pluck('email')->toArray();

        if (!empty($userRoles)) {
            Mail::to(array_map(fn ($email) => new Address($email), $userRoles))
                ->cc(new Address($user->email))
                ->send(new VacacionStatus($dataToSend));
        }
    }
}

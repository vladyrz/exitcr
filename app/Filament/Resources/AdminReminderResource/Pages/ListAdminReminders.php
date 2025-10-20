<?php

namespace App\Filament\Resources\AdminReminderResource\Pages;

use App\Filament\Resources\AdminReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdminReminders extends ListRecords
{
    protected static string $resource = AdminReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

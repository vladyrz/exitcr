<?php

namespace App\Filament\Resources\VacacionResource\Pages;

use App\Filament\Resources\VacacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVacacions extends ListRecords
{
    protected static string $resource = VacacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

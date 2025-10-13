<?php

namespace App\Filament\Personal\Resources\VacacionResource\Pages;

use App\Filament\Personal\Resources\VacacionResource;
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

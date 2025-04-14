<?php

namespace App\Filament\Personal\Resources\MantenimientoResource\Pages;

use App\Filament\Personal\Resources\MantenimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMantenimiento extends EditRecord
{
    protected static string $resource = MantenimientoResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
}

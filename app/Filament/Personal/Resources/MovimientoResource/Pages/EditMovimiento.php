<?php

namespace App\Filament\Personal\Resources\MovimientoResource\Pages;

use App\Filament\Personal\Resources\MovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMovimiento extends EditRecord
{
    protected static string $resource = MovimientoResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
}

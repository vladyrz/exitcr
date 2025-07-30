<?php

namespace App\Filament\Resources\EmpleadoResource\Pages;

use App\Filament\Resources\EmpleadoResource;
use App\Models\Empleado;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListEmpleados extends ListRecords
{
    protected static string $resource = EmpleadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('General')
                ->badge($this->orderByStatus() ?? 0)
                ->badgeColor(Color::Orange),
            Tab::make('Pendientes')
                ->query(fn ($query) => $query->where('estado_progreso', 'pendiente'))
                ->badge($this->orderByStatus('pendiente') ?? 0)
                ->badgeColor(Color::Amber),
            Tab::make('En formación')
                ->query(fn ($query) => $query->where('estado_progreso', 'en_formacion'))
                ->badge($this->orderByStatus('en_formacion') ?? 0)
                ->badgeColor(Color::Indigo),
            Tab::make('Certificado')
                ->query(fn ($query) => $query->where('estado_progreso', 'certificado'))
                ->badge($this->orderByStatus('certificado') ?? 0)
                ->badgeColor(Color::Green),
            Tab::make('Retirado')
                ->query(fn ($query) => $query->where('estado_progreso', 'retirado'))
                ->badge($this->orderByStatus('retirado') ?? 0)
                ->badgeColor(Color::Red),
        ];
    }

    private function orderByStatus(?string $status = null) {
        if(blank($status)) {
            return Empleado::count();
        }
        return Empleado::where('estado_progreso', $status)->count();
    }
}

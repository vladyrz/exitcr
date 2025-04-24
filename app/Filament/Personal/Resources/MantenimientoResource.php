<?php

namespace App\Filament\Personal\Resources;

use App\Filament\Personal\Resources\MantenimientoResource\Pages;
use App\Filament\Personal\Resources\MantenimientoResource\RelationManagers;
use App\Models\Mantenimiento;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class MantenimientoResource extends Resource
{
    protected static ?string $model = Mantenimiento::class;

    protected static ?string $navigationGroup = 'Gestión de vehículos';

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::user()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del mantenimiento')
                    ->columns(2)
                    ->schema([
                        Select::make('tipo_mantenimiento')
                            ->label('Tipo de mantenimiento')
                            ->options([
                                'limpieza' => 'Limpieza',
                                'reparacion' => 'Reparación',
                                'mantenimiento' => 'Mantenimiento',
                                'otro' => 'Otro',
                            ]),
                        Select::make('vehicle_id')
                            ->label('Vehículo')
                            ->options(Vehicle::query()
                                ->where('user_id', Auth::user()->id)
                                ->pluck('placa', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        DateTimePicker::make('fecha_mantenimiento')
                            ->label('Fecha del mantenimiento')
                            ->required(),
                        FileUpload::make('archivos')
                            ->label('Archivos adjuntos')
                            ->multiple()
                            ->downloadable()
                            ->directory('archivos/' .now()->format('Y/m/d'))
                            ->maxFiles(5),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tipo_mantenimiento')
                    ->label('Tipo de mantenimiento')
                    ->badge()
                    ->formatStateUsing(function ($state){
                        return match ($state) {
                            'limpieza' => 'Limpieza',
                            'reparacion' => 'Reparación',
                            'mantenimiento' => 'Mantenimiento',
                            'otro' => 'Otro',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'limpieza' => 'success',
                        'reparacion' => 'warning',
                        'mantenimiento' => 'info',
                        'otro' => 'danger',
                    })
                    ->alignCenter(),
                TextColumn::make('fecha_mantenimiento')
                    ->label('Fecha del mantenimiento')
                    ->dateTime()
                    ->alignCenter(),
                TextColumn::make('vehicle.placa')
                    ->label('Placa')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('mantenimiento_status')
                    ->label('Estado del mantenimiento')
                    ->badge()
                    ->formatStateUsing(function ($state){
                        return match ($state) {
                            'pendiente' => 'Pendiente',
                            'aprobado' => 'Aprobado',
                            'rechazado' => 'Rechazado',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'aprobado' => 'success',
                        'rechazado' => 'danger',
                    })
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo_mantenimiento')
                    ->label('Tipo de mantenimiento')
                    ->options([
                        'limpieza' => 'Limpieza',
                        'reparacion' => 'Reparación',
                        'mantenimiento' => 'Mantenimiento',
                        'otro' => 'Otro',
                    ]),
                SelectFilter::make('mantenimiento_status')
                    ->label('Estado del mantenimiento')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ]),
            ])
            ->actions([
                CommentsAction::make()
                    ->label('Comentarios')
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMantenimientos::route('/'),
            'create' => Pages\CreateMantenimiento::route('/create'),
            'edit' => Pages\EditMantenimiento::route('/{record}/edit'),
        ];
    }
}

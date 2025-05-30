<?php

namespace App\Filament\Personal\Resources;

use App\Filament\Personal\Resources\MovimientoResource\Pages;
use App\Filament\Personal\Resources\MovimientoResource\RelationManagers;
use App\Models\Movimiento;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class MovimientoResource extends Resource
{
    protected static ?string $model = Movimiento::class;

    protected static ?string $navigationGroup = 'Gestión de vehículos';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-pointing-out';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::user()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del movimiento')
                    ->columns(2)
                    ->schema([
                        Select::make('tipo_movimiento')
                            ->label('Tipo de movimiento')
                            ->options([
                                'salida' => 'Salida',
                                'ingreso' => 'Ingreso',
                            ])
                            ->required()
                            ->reactive(),
                        DateTimePicker::make('fecha_movimiento')
                            ->label('Fecha del movimiento')
                            ->required(),
                        Select::make('vehicle_id')
                            ->label('Vehículo')
                            ->options(fn () => Auth::user()
                                ->vehicles()
                                ->pluck('placa', 'vehicles.id')),
                        TextInput::make('kilometraje_inicial')
                            ->label('Kilometraje inicial')
                            ->maxLength(20)
                            ->visible(fn (Get $get): bool => $get('tipo_movimiento') === 'ingreso'),
                        TextInput::make('kilometraje_final')
                            ->label('Kilometraje final')
                            ->maxLength(20)
                            ->visible(fn (Get $get): bool => $get('tipo_movimiento') === 'salida'),
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
                TextColumn::make('tipo_movimiento')
                    ->label('Tipo de movimiento')
                    ->badge()
                    ->formatStateUsing(function ($state){
                        return match ($state) {
                            'salida' => 'Salida',
                            'ingreso' => 'Ingreso',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'salida' => 'warning',
                        'ingreso' => 'success',
                    })
                    ->alignCenter(),
                TextColumn::make('fecha_movimiento')
                    ->label('Fecha del movimiento')
                    ->dateTime()
                    ->alignCenter(),
                TextColumn::make('vehicle.placa')
                    ->label('Placa')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('kilometraje_inicial')
                    ->label('Kilometraje inicial')
                    ->alignCenter(),
                TextColumn::make('kilometraje_final')
                    ->label('Kilometraje final')
                    ->alignCenter(),
                TextColumn::make('movimiento_status')
                    ->label('Estado del movimiento')
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
                SelectFilter::make('tipo_movimiento')
                    ->label('Tipo de movimiento')
                    ->options([
                        'salida' => 'Salida',
                        'ingreso' => 'Ingreso',
                    ]),
                SelectFilter::make('movimiento_status')
                    ->label('Estado del movimiento')
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
            'index' => Pages\ListMovimientos::route('/'),
            'create' => Pages\CreateMovimiento::route('/create'),
            'edit' => Pages\EditMovimiento::route('/{record}/edit'),
        ];
    }
}

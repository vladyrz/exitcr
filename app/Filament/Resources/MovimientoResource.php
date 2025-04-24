<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimientoResource\Pages;
use App\Filament\Resources\MovimientoResource\RelationManagers;
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
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class MovimientoResource extends Resource
{
    protected static ?string $model = Movimiento::class;

    protected static ?string $navigationGroup = 'Gestión de Vehículos';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-pointing-out';

    public static function form(Form $form): Form
    {

        $isCreate = $form->getOperation() === 'create';

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
                            ->required(),
                        DateTimePicker::make('fecha_movimiento')
                            ->label('Fecha del movimiento')
                            ->required(),
                        Select::make('user_id')
                            ->label('Agente')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set){
                                $set('vehicle_id', null);
                            }),
                        Select::make('vehicle_id')
                            ->label('Vehículo')
                            ->options(fn (Get $get): Collection => Vehicle::query()
                                ->where('user_id', $get('user_id'))
                                ->pluck('placa', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('kilometraje_inicial')
                            ->label('Kilometraje inicial')
                            ->maxLength(20),
                        TextInput::make('kilometraje_final')
                            ->label('Kilometraje final')
                            ->maxLength(20),
                        Select::make('movimiento_status')
                            ->label('Estado del movimiento')
                            ->options(
                                $isCreate ? ['pendiente' => 'Pendiente']
                                : [
                                    'pendiente' => 'Pendiente',
                                    'aprobado' => 'Aprobado',
                                    'rechazado' => 'Rechazado',
                                ]
                            )
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
                TextColumn::make('user.name')
                    ->label('Agente')
                    ->searchable()
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
                SelectFilter::make('user_id')
                    ->label('Agente')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                    )
                    ->searchable()
                    ->preload(),
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
                ActionGroup::make([
                    CommentsAction::make()
                    ->label('Comentarios')
                    ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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

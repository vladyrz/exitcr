<?php

namespace App\Filament\Personal\Resources;

use App\Filament\Personal\Resources\VehicleResource\Pages;
use App\Filament\Personal\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $label = 'Vehículo';

    protected static ?string $pluralLabel = 'Vehículos';

    protected static ?string $navigationLabel = 'Vehículos';

    protected static ?string $navigationGroup = 'Gestión de vehículos';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('users', function ($query) {
                $query->where('users.id', auth()->id());
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del vehículo')
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero_contrato')
                            ->label('Número de contrato')
                            ->required(),
                        TextInput::make('placa')
                            ->label('Placa')
                            ->required()
                            ->maxLength(15),
                        TextInput::make('marca')
                            ->label('Marca')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('estilo')
                            ->label('Estilo')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('año')
                            ->label('Año')
                            ->required()
                            ->maxLength(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_contrato')
                    ->label('Número de contrato')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('placa')
                    ->label('Placa')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('marca')
                    ->label('Marca')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('estilo')
                    ->label('Estilo')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('año')
                    ->label('Año')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label('Estado del vehículo')
                    ->badge()
                    ->formatStateUsing(function ($state){
                        return match ($state) {
                            'activo' => 'Activo',
                            'inactivo' => 'Inactivo',
                            'vendido' => 'Vendido',
                            'eliminado' => 'Eliminado',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'warning',
                        'vendido' => 'info',
                        'eliminado' => 'danger',
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
                SelectFilter::make('status')
                    ->label('Estado del vehículo')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                        'vendido' => 'Vendido',
                        'eliminado' => 'Eliminado',
                    ])
            ])
            ->actions([
                CommentsAction::make()
                    ->label('Comentarios')
                    ->color('info'),
                Tables\Actions\ViewAction::make(),
            ])
            ->recordUrl(function ($record){
                return static::getUrl('view', ['record' => $record]);
            });
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
            'index' => Pages\ListVehicles::route('/'),
            // 'create' => Pages\CreateVehicle::route('/create'),
            // 'edit' => Pages\EditVehicle::route('/{record}/edit'),
            'view' => Pages\ViewVehicle::route('/{record}'),
        ];
    }
}

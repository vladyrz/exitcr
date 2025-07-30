<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProyectoResource\Pages;
use App\Filament\Resources\ProyectoResource\RelationManagers;
use App\Models\Proyecto;
use App\Models\User;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class ProyectoResource extends Resource
{
    protected static ?string $model = Proyecto::class;

    protected static ?string $navigationGroup = 'Gestión administrativa';

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Proyecto')
                    ->columns(3)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre/Descripción del Proyecto')
                            ->required(),
                        Select::make('user_id')
                            ->label('Responsable')
                            ->options(
                                User::whereDoesntHave('roles', function ($q){
                                    $q->where('name', 'admin');
                                })->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('progreso')
                            ->label('% de avance')
                            ->suffix('%')
                            ->required(),
                        Select::make('estado')
                            ->label('Estado del proyecto')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'en_progreso' => 'En progreso',
                                'finalizado' => 'Finalizado',
                                'detenido' => 'Detenido',
                                'cancelado' => 'Cancelado',
                            ])
                            ->required(),
                        Select::make('prioridad')
                            ->label('Prioridad')
                            ->options([
                                'alta' => 'Alta',
                                'media' => 'Media',
                                'baja' => 'Baja',
                            ])
                            ->required(),
                        Textarea::make('beneficio_esperado')
                            ->label('Beneficio esperado'),
                        DatePicker::make('fecha_solicitud')
                            ->label('Fecha de solicitud'),
                        DatePicker::make('ultima_actualizacion')
                            ->label('Ultima actualización'),
                        Textarea::make('observaciones')
                            ->label('Observaciones'),
                        FileUpload::make('documentos')
                            ->label('Documentos')
                            ->multiple()
                            ->columnSpanFull()
                            ->downloadable()
                            ->directory('proyectos/' .now()->format('Y/m/d'))
                            ->maxFiles(5),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre/Descripción del Proyecto')
                    ->searchable()
                    ->alignLeft(),
                TextColumn::make('user.name')
                    ->label('Responsable')
                    ->searchable()
                    ->alignLeft(),
                TextColumn::make('progreso')
                    ->label('% de avance')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => $state.'%'),
                TextColumn::make('estado')
                    ->label('Estado del proyecto')
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'pendiente' => 'Pendiente',
                            'en_progreso' => 'En progreso',
                            'finalizado' => 'Finalizado',
                            'detenido' => 'Detenido',
                            'cancelado' => 'Cancelado',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'en_progreso' => 'info',
                        'finalizado' => 'success',
                        'detenido' => 'danger',
                        'cancelado' => 'danger',
                    }),
                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'alta' => 'Alta',
                            'media' => 'Media',
                            'baja' => 'Baja',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'alta' => 'danger',
                        'media' => 'info',
                        'baja' => 'warning',
                    }),
                TextColumn::make('beneficio_esperado')
                    ->label('Beneficio esperado')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('fecha_solicitud')
                    ->label('Fecha de solicitud')
                    ->date()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('ultima_actualizacion')
                    ->label('Ultima actualización')
                    ->date()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado del proyecto')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_progreso' => 'En progreso',
                        'finalizado' => 'Finalizado',
                        'detenido' => 'Detenido',
                        'cancelado' => 'Cancelado',
                    ]),
                SelectFilter::make('prioridad')
                    ->label('Prioridad')
                    ->options([
                        'alta' => 'Alta',
                        'media' => 'Media',
                        'baja' => 'Baja',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    CommentsAction::make()
                        ->color('info'),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListProyectos::route('/'),
            'create' => Pages\CreateProyecto::route('/create'),
            'edit' => Pages\EditProyecto::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VacacionResource\Pages;
use App\Filament\Resources\VacacionResource\RelationManagers;
use App\Models\Vacacion;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class VacacionResource extends Resource
{
    protected static ?string $model = Vacacion::class;

    protected static ?string $label = 'vacacion';

    protected static ?string $pluralLabel = 'Vacaciones';

    protected static ?string $navigationGroup = 'Gestión administrativa';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la solicitud')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')->label('Agente')->relationship(name: 'user', titleAttribute: 'name')->searchable()->preload()->required(),
                        Radio::make('tipo_solicitud')->label('Tipo de solicitud')->options([
                            'permiso' => 'Permiso',
                            'vacaciones' => 'Vacaciones',
                        ])->reactive()->required(),
                        Select::make('estado_solicitud')->label('Estado de la solicitud')->options([
                            'pendiente' => 'Pendiente',
                            'aprobada' => 'Aprobada',
                            'denegada' => 'Denegada',
                        ])->required(),
                        Textarea::make('observaciones')->label('Observaciones'),
                    ]),

                Section::make('Permisos')
                    ->columns(2)
                    ->visible(fn (Get $get) => $get('tipo_solicitud') === 'permiso')
                    ->schema([
                        DatePicker::make('fecha_permiso')->label('Fecha de permiso'),
                        Radio::make('opciones_permiso')->label('Opciones de permiso')->options([
                            'medio_dia' => 'Medio día',
                            'dia' => 'Día completo',
                            'horas' => 'Rango de horas',
                        ])->reactive(),
                        TimePicker::make('hora_inicio')->label('Hora de inicio')->visible(fn (Get $get) => $get('opciones_permiso') === 'horas')->seconds(false),
                        TimePicker::make('hora_fin')->label('Hora de fin')->visible(fn (Get $get) => $get('opciones_permiso') === 'horas')->seconds(false),
                    ]),

                Section::make('Vacaciones')
                    ->columns(2)
                    ->visible(fn (Get $get) => $get('tipo_solicitud') === 'vacaciones')
                    ->schema([
                        DatePicker::make('fecha_inicio')->label('Fecha de inicio')->reactive()->afterStateUpdated(function ($state, Set $set, Get $get){
                            $start = Carbon::parse($state);
                            $end = $get('fecha_fin') ? Carbon::parse($get('fecha_fin')) : null;

                            if ($start && $end && $start <= $end) {
                                $days = $start->diffInDays($end->copy()->addDay());
                                $set('total_vacaciones', $days);
                            } else {
                                $set('total_vacaciones', null);
                            }
                        }),

                        DatePicker::make('fecha_fin')->label('Fecha de fin')->reactive()->afterStateUpdated(function ($state, Set $set, Get $get){
                            $end = Carbon::parse($state);
                            $start = $get('fecha_inicio') ? Carbon::parse($get('fecha_inicio')) : null;

                            if ($start && $end && $start <= $end) {
                                $days = $start->diffInDays($end->copy()->addDay());
                                $set('total_vacaciones', $days);
                            } else {
                                $set('total_vacaciones', null);
                            }
                        }),

                        TextInput::make('total_vacaciones')->label('Total de vacaciones')->disabled()->dehydrated(true),

                        TextInput::make('saldo_vacaciones')->label('Saldo de vacaciones')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Agente')->searchable(),
                TextColumn::make('tipo_solicitud')->label('Tipo de solicitud')->alignCenter()->badge()->formatStateUsing(function ($state) {
                    return match ($state) {
                        'permiso' => 'Permiso',
                        'vacaciones' => 'Vacaciones',
                    };
                })->color(fn (string $state): string => match ($state) {
                    'permiso' => 'warning',
                    'vacaciones' => 'info',
                }),
                TextColumn::make('estado_solicitud')->label('Estado de la solicitud')->alignCenter()->badge()->formatStateUsing(function ($state) {
                    return match ($state) {
                        'pendiente' => 'Pendiente',
                        'aprobada' => 'Aprobada',
                        'denegada' => 'Denegada',
                    };
                })->color(fn (string $state): string => match ($state) {
                    'pendiente' => 'warning',
                    'aprobada' => 'success',
                    'denegada' => 'danger',
                }),
                TextColumn::make('observaciones')->label('Observaciones'),
                TextColumn::make('created_at')->label('Creado el')->dateTime()->sortable()->alignCenter(),
                TextColumn::make('updated_at')->label('Actualizado el')->dateTime()->sortable()->alignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo_solicitud')->label('Tipo de solicitud')->options([
                    'permiso' => 'Permiso',
                    'vacaciones' => 'Vacaciones',
                ]),
            ])
            ->actions([
                CommentsAction::make()->color('info'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListVacacions::route('/'),
            'create' => Pages\CreateVacacion::route('/create'),
            'edit' => Pages\EditVacacion::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpleadoResource\Pages;
use App\Filament\Resources\EmpleadoResource\RelationManagers;
use App\Models\Empleado;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class EmpleadoResource extends Resource
{
    protected static ?string $model = Empleado::class;

    protected static ?string $navigationGroup = 'Gestión administrativa';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Empleado')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required(),
                        TextInput::make('correo_empresarial')
                            ->label('Correo empresarial')
                            ->email(),
                        Select::make('puesto_de_trabajo')
                            ->label('Puesto de trabajo')
                            ->options([
                                'administrativo' => 'Administrativo',
                                'agente' => 'Agente',
                            ])
                            ->required(),
                        Select::make('estado_progreso')
                            ->label('Estado de progreso')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'en_formacion' => 'En formación',
                                'certificado' => 'Certificado',
                                'retirado' => 'Retirado',
                            ])
                            ->required(),
                        Toggle::make('estado_contrato')
                            ->label('Estado de contrato')
                            ->required(),
                    ]),

                Section::make('Información Personal')
                    ->columns(3)
                    ->schema([
                        TextInput::make('correo_personal')
                            ->label('Correo personal')
                            ->email(),
                        Select::make('estado_civil')
                            ->label('Estado civil')
                            ->options([
                                'soltero/a' => 'Soltero/a',
                                'casado/a' => 'Casado/a',
                                'viudo/a' => 'Viudo/a',
                                'divorciado/a' => 'Divorciado/a',
                                'union_libre' => 'Union libre',
                            ]),
                        TextInput::make('cedula')
                            ->label('Cedula')
                            ->maxLength(20),
                        TextInput::make('telefono')
                            ->label('Telefono')
                            ->maxLength(20),
                        TextInput::make('profesion')
                            ->label('Profesion'),
                        TextInput::make('placa')
                            ->label('Placa'),
                        DatePicker::make('fecha_nacimiento')
                            ->label('Fecha de nacimiento'),
                        Textarea::make('direccion')
                            ->label('Direccion'),
                        FileUpload::make('contrato')
                            ->label('Contrato')
                            ->downloadable()
                            ->directory('empleados/' .now()->format('Y/m/d')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('correo_empresarial')
                    ->label('Correo empresarial')
                    ->searchable(),
                IconColumn::make('estado_contrato')
                    ->label('Estado contrato')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('estado_progreso')
                    ->label('Estado progreso')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'pendiente' => 'Pendiente',
                            'en_formacion' => 'En formación',
                            'certificado' => 'Certificado',
                            'retirado' => 'Retirado',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'en_formacion' => 'info',
                        'certificado' => 'success',
                        'retirado' => 'danger',
                    })
                    ->alignCenter(),
                TextColumn::make('puesto_de_trabajo')
                    ->label('Puesto de trabajo')
                    ->searchable()
                    ->alignCenter()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'administrativo' => 'Administrativo',
                            'agente' => 'Agente',
                        };
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('cedula')
                    ->label('Cedula')
                    ->searchable()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('telefono')
                    ->label('Telefono')
                    ->searchable()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('correo_personal')
                    ->label('Correo personal')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('profesion')
                    ->label('Profesion')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('placa')
                    ->label('Placa')
                    ->searchable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('direccion')
                    ->label('Direccion')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fecha_nacimiento')
                    ->label('Fecha de nacimiento')
                    ->date()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estado_civil')
                    ->label('Estado civil')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'soltero/a' => 'Soltero/a',
                            'casado/a' => 'Casado/a',
                            'viudo/a' => 'Viudo/a',
                            'divorciado/a' => 'Divorciado/a',
                            'union_libre' => 'Union libre',
                        };
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('puesto_de_trabajo')
                    ->label('Puesto de trabajo')
                    ->options([
                        'administrativo' => 'Administrativo',
                        'agente' => 'Agente',
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
            'index' => Pages\ListEmpleados::route('/'),
            'create' => Pages\CreateEmpleado::route('/create'),
            'edit' => Pages\EditEmpleado::route('/{record}/edit'),
        ];
    }
}

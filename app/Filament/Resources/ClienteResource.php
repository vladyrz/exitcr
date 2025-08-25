<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationGroup = 'Gestión administrativa';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Cliente')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del cliente')
                            ->required(),
                        TextInput::make('cedula')
                            ->label('Cédula'),
                        TextInput::make('email')
                            ->label('Correo electrónico'),
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->maxLength(20)
                            ->required(),
                        Textarea::make('direccion')
                            ->label('Dirección'),
                        Select::make('contacto_preferido')
                            ->label('Preferencia de contacto')
                            ->options([
                                'email' => 'Correo electrónico',
                                'telefono' => 'Teléfono',
                                'whatsapp' => 'Whatsapp',
                            ]),
                        Select::make('tipo_cliente')
                            ->label('Tipo de cliente')
                            ->options([
                                'pripa' => 'Autos Pripa',
                                'exit' => 'Exit Rentacar',
                                'otro' => 'Otro',
                            ])
                            ->reactive()
                            ->required(),
                        TextInput::make('otro_tipo')
                            ->label('Detalles del tipo de cliente')
                            ->visible(fn (Get $get): bool => $get('tipo_cliente') === 'otro'),
                        Textarea::make('observaciones')
                            ->label('Observaciones'),
                        DatePicker::make('fecha_ingreso')
                            ->label('Fecha de ingreso')
                            ->required(),
                        FileUpload::make('documentos')
                            ->label('Documentos')
                            ->multiple()
                            ->downloadable()
                            ->directory('clientes/' .now()->format('Y/m/d'))
                            ->maxFiles(5),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre del cliente')
                    ->searchable(),
                TextColumn::make('cedula')
                    ->label('Cédula')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->alignRight(),
                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('contacto_preferido')
                    ->label('Preferencia de contacto')
                    ->badge()
                    ->formatStateUsing(function ($state){
                        return match ($state) {
                            'email' => 'Correo electrónico',
                            'telefono' => 'Teléfono',
                            'whatsapp' => 'Whatsapp',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'info',
                        'telefono' => 'warning',
                        'whatsapp' => 'success',
                    }),
                TextColumn::make('tipo_cliente')
                    ->label('Tipo de cliente')
                    ->badge()
                    ->formatStateUsing(function ($state){
                        return match ($state) {
                            'pripa' => 'Autos Pripa',
                            'exit' => 'Exit Rentacar',
                            'otro' => 'Otro',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pripa' => 'info',
                        'exit' => 'danger',
                        'otro' => 'warning',
                    }),
                TextColumn::make('otro_tipo')
                    ->label('Detalles del tipo de cliente')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fecha_ingreso')
                    ->label('Fecha de ingreso')
                    ->date()
                    ->searchable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                SelectFilter::make('contacto_preferido')
                    ->label('Preferencia de contacto')
                    ->options([
                        'email' => 'Correo electrónico',
                        'telefono' => 'Teléfono',
                        'whatsapp' => 'Whatsapp',
                    ]),
            ])
            ->actions([
                CommentsAction::make()
                    ->label('Comentarios')
                    ->color('info'),
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
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminReminderResource\Pages;
use App\Models\AdminReminder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdminReminderResource extends Resource
{
    protected static ?string $model = AdminReminder::class;

    protected static ?string $label = 'recordatorio';

    protected static ?string $pluralLabel = 'Recordatorios';

    protected static ?string $navigationGroup = 'Gestión administrativa';

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Asignación')->schema([
                    Select::make('user_id')->label('Agente')->relationship(name: 'user', titleAttribute: 'name')->searchable()->preload()->required(),
                    TextInput::make('reminder_type')->label('Tipo de recordatorio')->maxLength(100)->required(),
                    Select::make('frequency')->label('Frecuencia')->options([
                        'daily' => 'Diaria',
                        'weekly' => 'Semanal',
                        'monthly' => 'Mensual',
                        'quarterly' => 'Trimestral',
                        'yearly' => 'Anual',
                    ])->required()->reactive()->afterStateUpdated(function ($state, Set $set) {
                        $defaults = match ($state) {
                            'weekly'    => ['day_of_week' => 1],
                            'monthly'   => ['day_of_month' => 28],
                            'quarterly' => ['day_of_month' => 15],
                            'yearly'    => ['month' => 1, 'day_of_month' => 10],
                            default     => [],
                        };
                        $set('meta', $defaults);
                    }),
                ])->columns(3),

                Section::make('Programación')->schema([
                    Toggle::make('is_active')->label('Activo')->default(true),
                    TextInput::make('timezone')->label('Zona horaria')->default('America/Costa_Rica')->helperText('Zona horaria para calcular "hoy" y la hora de envío.'),
                    DateTimePicker::make('starts_at')->label('Comienza desde')->seconds(false),
                    TimePicker::make('send_at')->label('Hora preferida de envío')->seconds(false),

                    KeyValue::make('meta')->label('Parámetros de frecuencia')->addActionLabel('Agregar')->reorderActionLabel('Reordenar')->keyPlaceholder('p. ej. day_of_week')->valuePlaceholder('p. ej. 1')->reorderable()->helperText(fn(Get $get) => match ($get('frequency')) {
                        'weekly'    => 'Usa day_of_week (0=Dom,1=Lun,...6=Sáb)',
                        'monthly'   => 'Usa day_of_month (1..31)',
                        'quarterly' => 'Usa day_of_month (1..31)',
                        'yearly'    => 'Usa month (1..12) y day_of_month (1..31)',
                        default     => 'Sin parámetros requeridos',
                    })->columnSpanFull(),
                ])->columns(2),

                Section::make('Contenido')->schema([
                    Textarea::make('task_details')->label('Detalles de la tarea')->rows(6)->required(),
                ]),

                Section::make('Siguiente ejecución')->schema([
                    Placeholder::make('next_due_preview')->label('Próximo vencimiento (preview)')->content(
                        fn(?AdminReminder $record) =>
                        $record?->next_due_at?->tz($record->timezone ?? 'UTC')->format('Y-m-d H:i') ?? '-'
                    ),
                ])->hidden(fn($get) => !$get('id')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_active')->boolean()->label('Act.'),
                TextColumn::make('user.name')->label('Agente')->searchable(),
                TextColumn::make('reminder_type')->label('Tipo de recordatorio')->searchable(),
                TextColumn::make('frequency')->label('Frecuencia')->badge()->formatStateUsing(fn($state) => match ($state) {
                    'daily' => 'Diaria',
                    'weekly' => 'Semanal',
                    'monthly' => 'Mensual',
                    'quarterly' => 'Trimestral',
                    'yearly' => 'Anual',
                })->color(fn(string $state): string => match ($state) {
                    'daily' => 'success',
                    'weekly' => 'info',
                    'monthly' => 'warning',
                    'quarterly' => 'danger',
                    'yearly' => 'gray',
                }),
                TextColumn::make('next_due_at')->dateTime()->label('Próximo')->alignCenter(),
                TextColumn::make('last_sent_at')->dateTime()->label('Último envío')->alignCenter()->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('timezone')->label('Zona horaria')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Creado')->dateTime()->sortable()->alignCenter()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Actualizado')->since()->alignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make()->label('Borrados'),
                TernaryFilter::make('is_active')->label('Activos'),
                SelectFilter::make('frequency')->label('Frecuencia')->options([
                    'daily' => 'Diaria',
                    'weekly' => 'Semanal',
                    'monthly' => 'Mensual',
                    'quarterly' => 'Trimestral',
                    'yearly' => 'Anual',
                ]),
                Filter::make('due_today')->label('Vencimientos de hoy')->query(function ($query) {
                    return $query->whereNotNull('next_due_at')->where('next_due_at', '<=', now()->endOfDay());
                }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->color('warning'),
                    Tables\Actions\Action::make('forceSend')->label('Enviar ahora')->requiresConfirmation()->action(function (AdminReminder $record) {
                        $record->user?->notify(new \App\Notifications\AdminReminderDue($record));
                        $record->forceFill(['last_sent_at' => now($record->timezone)])->save();
                        $record->advanceNextDue();
                    })->icon('heroicon-o-paper-airplane')->color('success'),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminReminders::route('/'),
            'create' => Pages\CreateAdminReminder::route('/create'),
            'edit' => Pages\EditAdminReminder::route('/{record}/edit'),
        ];
    }
}

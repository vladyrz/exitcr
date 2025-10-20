<?php

namespace App\Filament\Resources\AdminReminderResource\Pages;

use App\Filament\Resources\AdminReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateAdminReminder extends CreateRecord
{
    protected static string $resource = AdminReminderResource::class;

    protected function failOn(string $msg, string $key = 'meta'): never
    {
        try {
            $path = $this->getForm('form')->getPath() ?? 'data';
        } catch (\Throwable $e) {
            $path = 'data';
        }

        throw ValidationException::withMessages([
            "{$path}.{$key}" => $msg,
        ]);
    }

    protected function normalizeMeta(array $data): array
    {
        $meta = (array)($data['meta'] ?? []);

        $toInt = function (string $key) use (&$meta) {
            if (isset($meta[$key]) && $meta[$key] !== '') {
                $meta[$key] = (int)$meta[$key];
            }
        };

        switch ($data['frequency'] ?? null) {
            case 'weekly':
                $toInt('day_of_week');
                break;
            case 'monthly':
            case 'quarterly':
                $toInt('day_of_month');
                break;
            case 'yearly':
                $toInt('month');
                $toInt('day_of_month');
                break;
            default:
                $meta = [];
        }

        $data['meta'] = $meta;
        return $data;
    }

    protected function validateMeta(array $data): void
    {
        $meta = (array)($data['meta'] ?? []);
        $freq = $data['frequency'] ?? null;

        $only = function (array $allowed) use ($meta) {
            // validar que no vengan claves extra
            $extra = array_diff(array_keys($meta), $allowed);
            if ($extra) {
                $this->failOn('Claves no permitidas en meta: '.implode(', ', $extra));
            }
        };

        switch ($freq) {
            case 'weekly':
                $only(['day_of_week']);
                if (!isset($meta['day_of_week']) || $meta['day_of_week'] < 0 || $meta['day_of_week'] > 6) {
                    $this->failOn('day_of_week debe ser un entero entre 0 (Dom) y 6 (Sáb).', 'meta.day_of_week');
                }
                break;

            case 'monthly':
            case 'quarterly':
                $only(['day_of_month']);
                if (!isset($meta['day_of_month']) || $meta['day_of_month'] < 1 || $meta['day_of_month'] > 31) {
                    $this->failOn('day_of_month debe ser un entero entre 1 y 31.', 'meta.day_of_month');
                }
                break;

            case 'yearly':
                $only(['month', 'day_of_month']);
                if (!isset($meta['month']) || $meta['month'] < 1 || $meta['month'] > 12) {
                    $this->failOn('month debe ser un entero entre 1 y 12.', 'meta.month');
                }
                if (!isset($meta['day_of_month']) || $meta['day_of_month'] < 1 || $meta['day_of_month'] > 31) {
                    $this->failOn('day_of_month debe ser un entero entre 1 y 31.', 'meta.day_of_month');
                }
                break;

            default:
                if (!empty($meta)) {
                    $this->failOn('Esta frecuencia no requiere parámetros en meta.');
                }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->normalizeMeta($data);
        $this->validateMeta($data);
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->advanceNextDue();
    }
}

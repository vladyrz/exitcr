<?php

namespace App\Traits;

use App\Models\Vehicle;
use Flowframe\Trend\Trend;

trait VehiculoStatusTrait
{
    protected function getChartData(): array
    {
        $statuses = ['activo', 'inactivo', 'vendido', 'eliminado'];
        $trendData = [];

        foreach ($statuses as $status) {
            $trendData[$status] = Trend::query(
                Vehicle::where('status', $status)
            )
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear()
                )
                ->perMonth()
                ->count();
        }

        $formattedData = [];
        foreach ($trendData[array_key_first($trendData)] as $trendValue) {
            $month = $trendValue->date;
            $formattedData[$month] = [
                'activo' => $trendData['activo']->firstWhere('date', $month)?->aggregate ?? 0,
                'inactivo' => $trendData['inactivo']->firstWhere('date', $month)?->aggregate ?? 0,
                'vendido' => $trendData['vendido']->firstWhere('date', $month)?->aggregate ?? 0,
                'eliminado' => $trendData['eliminado']->firstWhere('date', $month)?->aggregate ?? 0,
            ];
        }

        return $formattedData;
    }
}

<?php

namespace App\Filament\Personal\Traits;

use App\Models\Vehicle;
use Flowframe\Trend\Trend;
use Illuminate\Support\Facades\Auth;

trait PersonalVehicleTrait
{
    protected function getChartData(): array
    {
        $user = Auth::user();

        $statuses = ['activo', 'inactivo', 'vendido', 'eliminado'];
        $trendData = [];

        foreach ($statuses as $status) {
            $vehicleIds = $user->vehicles()
                ->where('status', $status)
                ->pluck('vehicles.id');

            $trendData[$status] = Trend::query(
                Vehicle::whereIn('id', $vehicleIds)
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

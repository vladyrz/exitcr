<?php

namespace App\Filament\Personal\Widgets;

use App\Filament\Personal\Traits\PersonalVehicleTrait;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ApexPersonalVehicleStatus extends ApexChartWidget
{

    use PersonalVehicleTrait;

    protected static string $chartId = 'apexPersonalVehicleStatus';
    protected static ?string $heading = 'Cantidad de vehículos';
    protected int|string|array $columnSpan = 'full';

    protected function getOptions(): array
    {

        $chartData = $this->getChartData();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 450,
                'width' => '100%',
                'stacked' => true,
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'center',
                'fontFamily' => 'inherit',
            ],
            'series' => [
                [
                    'name' => 'Activos',
                    'data' => array_column($chartData, 'activo'),
                    'color' => '#10b981',
                ],
                [
                    'name' => 'Inactivos',
                    'data' => array_column($chartData, 'inactivo'),
                    'color' => '#f59e0b',
                ],
                [
                    'name' => 'Vendidos',
                    'data' => array_column($chartData, 'vendido'),
                    'color' => '#3b82f6',
                ],
                [
                    'name' => 'Eliminados',
                    'data' => array_column($chartData, 'eliminado'),
                    'color' => '#ef4444',
                ],
            ],
            'xaxis' => [
                'categories' => array_keys($chartData),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => true,
                ],
            ],
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalStorage = \App\Models\Image::sum('size');
        $storageFormatted = number_format($totalStorage / 1048576, 2) . ' MB';

        return [
            Stat::make('Total Users', \App\Models\User::count()),
            Stat::make('Total Images', \App\Models\Image::count()),
            Stat::make('Storage Used', $storageFormatted),
        ];
    }
}

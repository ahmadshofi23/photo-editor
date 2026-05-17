<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class UploadChart extends ChartWidget
{
    protected static ?string $heading = 'Uploads (Last 7 Days)';

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');
            $data[] = \App\Models\Image::whereDate('created_at', $date->toDateString())->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Images Uploaded',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

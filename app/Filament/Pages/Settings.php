<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = \Illuminate\Support\Facades\Storage::disk('local')->exists('settings.json')
            ? json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get('settings.json'), true)
            : [
                'max_size_kb' => config('security.uploads.max_size_kb'),
                'max_per_day' => config('security.uploads.max_per_day'),
                'allowed_extensions' => implode(',', config('security.uploads.allowed_extensions')),
            ];

        $this->form->fill($settings);
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('Upload Settings')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('max_size_kb')
                            ->label('Max Upload Size (KB)')
                            ->numeric()
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('max_per_day')
                            ->label('Max Uploads Per Day')
                            ->numeric()
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('allowed_extensions')
                            ->label('Allowed Extensions (comma separated)')
                            ->required(),
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        \Illuminate\Support\Facades\Storage::disk('local')->put('settings.json', json_encode($data));

        // Update config dynamically for current request if needed, 
        // in real app this would be loaded in a service provider.

        \Filament\Notifications\Notification::make()
            ->title('Settings saved successfully.')
            ->success()
            ->send();
    }
}

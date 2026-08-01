<?php

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasUser;
use Livewire\Component;

class CustomProfileComponent extends Component implements HasForms
{
    use HasSort;
    use HasUser;
    use InteractsWithForms;

    public ?array $data = [];

    protected static int $sort = 0;

    public function mount(): void
    {
        $this->user = $this->getUser();

        $this->form->fill($this->user->only(['username', 'locale', 'theme_color']));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Additional Information')
                    ->aside()
                    ->description('Custom component description')
                    ->schema([
                        TextInput::make('username')
                            ->placeholder('crusher01')
                            ->disabled()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Section::make('Preferences')
                            ->description('Optional display preferences used by the admin profile plugin.')
                            ->schema([
                                TextInput::make('locale')
                                    ->placeholder('en'),
                                TextInput::make('theme_color')
                                    ->placeholder('amber'),
                            ])
                            ->columns(2),
                    ]),
            ])
            ->model($this->getUser())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->user->update($data);

        Notification::make()
            ->success()
            ->title(__('filament-edit-profile::default.saved_successfully'))
            ->send();
    }

    public function render(): View
    {
        return view('livewire.custom-profile-component');
    }
}

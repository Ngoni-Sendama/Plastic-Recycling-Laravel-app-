<?php

namespace App\Filament\Resources\SyncConflicts\Pages;

use App\Filament\Resources\SyncConflicts\SyncConflictResource;
use App\Models\User;
use App\Services\SyncConflictResolver;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSyncConflict extends ViewRecord
{
    protected static string $resource = SyncConflictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('keepServer')
                ->label('Keep server version')
                ->icon('heroicon-o-shield-check')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'pending')
                ->action(function (): void {
                    app(SyncConflictResolver::class)->keepServer($this->record, $this->currentUser());
                    $this->record->refresh();

                    Notification::make()
                        ->title('Server version kept')
                        ->success()
                        ->send();
                }),

            Action::make('acceptSubmitted')
                ->label('Accept submitted version')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'pending')
                ->action(function (): void {
                    app(SyncConflictResolver::class)->acceptSubmitted($this->record, $this->currentUser());
                    $this->record->refresh();

                    Notification::make()
                        ->title('Submitted version applied')
                        ->success()
                        ->send();
                }),

            Action::make('mergeFields')
                ->label('Merge selected fields')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('warning')
                ->visible(fn (): bool => $this->record->status === 'pending')
                ->schema([
                    CheckboxList::make('fields')
                        ->label('Fields to apply from the submitted change')
                        ->options(fn (): array => $this->changedFieldOptions())
                        ->default(fn (): array => $this->record->changed_fields ?? [])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    app(SyncConflictResolver::class)->mergeFields($this->record, $this->currentUser(), $data['fields'] ?? []);
                    $this->record->refresh();

                    Notification::make()
                        ->title('Selected fields merged')
                        ->success()
                        ->send();
                }),

            Action::make('discard')
                ->label('Discard submitted change')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'pending')
                ->action(function (): void {
                    app(SyncConflictResolver::class)->discard($this->record, $this->currentUser());
                    $this->record->refresh();

                    Notification::make()
                        ->title('Submitted change discarded')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * Build the [field => field] option map for the merge modal.
     *
     * @return array<string, string>
     */
    protected function changedFieldOptions(): array
    {
        $fields = $this->record->changed_fields ?? [];

        return array_combine($fields, $fields) ?: [];
    }

    /**
     * The authenticated reviewer. The page is behind auth middleware, so the
     * user is always present.
     */
    protected function currentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}

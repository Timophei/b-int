<?php

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSection extends EditRecord
{
    protected static string $resource = SectionResource::class;

    public function getTitle(): string
    {
        return $this->record->nav_label;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('open')->label('Открыть на сайте')->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => $this->record->url(), true)->color('gray'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['path'])) {
            $data['path'] = trim((string) $data['path'], '/');
        }

        return $data;
    }
}

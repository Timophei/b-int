<?php

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateSection extends CreateRecord
{
    protected static string $resource = SectionResource::class;

    protected static ?string $title = 'Новая страница';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ключ нужен сайту, чтобы отличать разделы; у новых страниц он свой, по адресу
        $data['key'] = 'page-'.Str::slug($data['path'] ?: $data['nav_label']).'-'.Str::lower(Str::random(4));
        $data['path'] = trim((string) ($data['path'] ?? ''), '/') ?: Str::slug($data['nav_label']);

        return $data;
    }
}

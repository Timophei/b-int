<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use App\Models\PageView;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Заявки с формы «Написать»: кто, что хочет, откуда пришёл и что с заявкой сейчас.
 */
class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'заявки';

    protected static ?string $navigationLabel = 'Заявки';

    protected static ?string $breadcrumb = 'Заявки';

    protected static ?string $slug = 'leads';

    protected static ?int $navigationSort = 0;

    public static function getNavigationBadge(): ?string
    {
        return (string) (Lead::where('status', 'new')->count() ?: '') ?: null;
    }

    public static function topicLabel(?string $topic): string
    {
        return match (true) {
            $topic === null => '—',
            $topic === 'other' => 'Другое',
            default => Section::firstWhere('key', $topic)?->nav_label ?? $topic,
        };
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')->label('Статус')->options(Lead::STATUSES)->required()->native(false),
            Forms\Components\Textarea::make('note')->label('Заметка')->rows(4)->helperText('Видна только в админке'),
        ])->columns(1);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->columns(3)->schema([
                Infolists\Components\TextEntry::make('name')->label('Имя'),
                Infolists\Components\TextEntry::make('contact')->label('Контакт')->copyable(),
                Infolists\Components\TextEntry::make('company')->label('Компания')->placeholder('—'),
                Infolists\Components\TextEntry::make('topic')->label('Тема')->formatStateUsing(fn (?string $state) => self::topicLabel($state)),
                Infolists\Components\TextEntry::make('created_at')->label('Когда')->dateTime('d.m.Y H:i', config('app.site_timezone')),
                Infolists\Components\TextEntry::make('status')->label('Статус')->badge()->formatStateUsing(fn (string $state) => Lead::STATUSES[$state] ?? $state),
                Infolists\Components\TextEntry::make('message')->label('Сообщение')->placeholder('—')->columnSpanFull(),
            ]),
            Infolists\Components\Section::make('Откуда пришёл')->columns(4)->collapsible()->schema([
                Infolists\Components\TextEntry::make('source')->label('Источник')->formatStateUsing(fn (?string $state) => PageView::SOURCES[$state] ?? $state)->placeholder('—'),
                Infolists\Components\TextEntry::make('source_name')->label('Откуда')->placeholder('—'),
                Infolists\Components\TextEntry::make('utm_campaign')->label('Метка')->formatStateUsing(fn (Lead $record) => trim($record->utm_source.' / '.$record->utm_campaign, ' /'))->placeholder('—'),
                Infolists\Components\TextEntry::make('page')->label('Первая страница')->placeholder('—'),
            ]),
            Infolists\Components\TextEntry::make('note')->label('Заметка')->placeholder('—'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Когда')->dateTime('d.m.Y H:i', config('app.site_timezone'))->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Имя')->weight('bold')->searchable()
                    ->description(fn (Lead $record) => $record->company),
                Tables\Columns\TextColumn::make('contact')->label('Контакт')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('topic')->label('Тема')->formatStateUsing(fn (?string $state) => self::topicLabel($state)),
                Tables\Columns\TextColumn::make('message')->label('Сообщение')->limit(60)->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('source_name')->label('Откуда')
                    ->formatStateUsing(fn (Lead $record) => $record->source_name ?: (PageView::SOURCES[$record->source] ?? '—'))->toggleable(),
                Tables\Columns\TextColumn::make('status')->label('Статус')->badge()
                    ->formatStateUsing(fn (string $state) => Lead::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) { 'new' => 'danger', 'work' => 'warning', 'done' => 'success', default => 'gray' }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Статус')->options(Lead::STATUSES),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->label('Статус'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'view' => Pages\ViewLead::route('/{record}'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

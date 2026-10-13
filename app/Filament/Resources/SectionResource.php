<?php

namespace App\Filament\Resources;

use App\Filament\Forms\SeoFields;
use App\Filament\Resources\SectionResource\Pages;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Разделы сайта. Каждый — отдельная страница: тексты, услуги, тариф, шаги и SEO в одной форме.
 */
class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'раздел';

    protected static ?string $pluralModelLabel = 'разделы';

    protected static ?string $navigationLabel = 'Разделы и страницы';

    protected static ?string $breadcrumb = 'Разделы и страницы';

    protected static ?string $slug = 'sections';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make()->columnSpanFull()->persistTabInQueryString()->tabs([
                Forms\Components\Tabs\Tab::make('Основное')->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('nav_label')->label('Название в меню')->required()->maxLength(60)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state, string $operation) {
                                if ($operation === 'create' && blank($get('path'))) {
                                    $set('path', Str::slug((string) $state));
                                }
                            }),
                        Forms\Components\TextInput::make('path')->label('Адрес')->prefix('b-integration.ru/')->suffix('/')
                            ->maxLength(120)->unique(ignoreRecord: true)
                            ->regex('/^[a-z0-9\-\/]*$/')->validationMessages(['regex' => 'Только латиница в нижнем регистре, цифры, дефис и /'])
                            ->disabled(fn (?Section $record) => $record?->key === 'home')->dehydrated(fn (?Section $record) => $record?->key !== 'home')
                            ->helperText(fn (?Section $record) => $record?->key === 'home' ? 'Главная всегда на корне сайта' : null),
                        Forms\Components\TextInput::make('sort')->label('Порядок в меню')->numeric()->default(0),
                    ]),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Toggle::make('is_published')->label('Опубликован')->default(true)
                            ->helperText('Черновик видите только вы, пока вошли в админку'),
                        Forms\Components\Toggle::make('in_nav')->label('Показывать в меню и на главной')->default(true),
                        Forms\Components\TextInput::make('external_url')->label('Ссылка на другой сайт')->url()->maxLength(255)
                            ->helperText('Если заполнено, раздел ведёт туда (как «Курсы»)'),
                    ]),
                    Forms\Components\TextInput::make('eyebrow')->label('Надпись над заголовком')->maxLength(255),
                    Forms\Components\Textarea::make('title')->label('Заголовок')->required()->rows(2)->maxLength(255)
                        ->helperText('Точка в конце подсвечивается голубым. Перенос строки — Enter.'),
                    Forms\Components\TextInput::make('subtitle')->label('Подзаголовок')->maxLength(255),
                    Forms\Components\Textarea::make('lead')->label('Вводный абзац')->rows(3)->maxLength(500),
                    Forms\Components\TextInput::make('teaser')->label('Строка о разделе на главной')->maxLength(255),
                    Forms\Components\FileUpload::make('cover')->label('Картинка шапки')->image()->disk('public')->directory('covers')
                        ->maxSize(8192)->imageEditor()
                        ->helperText('Пусто — фирменная ночная гладь с кольцом. Ставьте тёмную широкую картинку от 1600 px.'),
                    Forms\Components\RichEditor::make('body')->label('Текст страницы')->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')
                        ->toolbarButtons(['h2', 'h3', 'bold', 'italic', 'link', 'bulletList', 'orderedList', 'blockquote', 'attachFiles', 'undo', 'redo']),
                ]),
                Forms\Components\Tabs\Tab::make('Услуги')->badge(fn (?Section $record) => $record?->services()->count() ?: null)->schema([
                    Forms\Components\Repeater::make('services')->relationship()->hiddenLabel()
                        ->orderColumn('sort')->reorderableWithButtons()->collapsible()->cloneable()
                        ->itemLabel(fn (array $state) => $state['title'] ?? null)
                        ->addActionLabel('Добавить услугу')
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('Название')->required()->maxLength(255),
                            Forms\Components\TextInput::make('description')->label('Пояснение')->maxLength(500),
                        ])->columns(2),
                ]),
                Forms\Components\Tabs\Tab::make('Тариф')->schema([
                    Forms\Components\Repeater::make('plans')->relationship()->hiddenLabel()
                        ->orderColumn('sort')->reorderableWithButtons()->collapsible()
                        ->itemLabel(fn (array $state) => trim(($state['name'] ?? '').' '.($state['price'] ?? '')) ?: null)
                        ->addActionLabel('Добавить тариф')
                        ->helperText('Первый тариф — карточка рядом с услугами. Нет тарифа — вместо цены «Обсудим задачу».')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('name')->label('Название тарифа')->maxLength(255)->placeholder('необязательно'),
                                Forms\Components\TextInput::make('price')->label('Цена')->required()->maxLength(60)->placeholder('25 000 ₽'),
                                Forms\Components\TextInput::make('period')->label('За что')->maxLength(60)->placeholder('/ месяц'),
                            ]),
                            Forms\Components\TagsInput::make('features')->label('Что входит')->placeholder('Добавить пункт и Enter')->reorderable(),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('button_label')->label('Текст кнопки')->maxLength(60)->placeholder('Обсудить поддержку'),
                                Forms\Components\TextInput::make('note')->label('Сноска')->maxLength(500),
                            ]),
                        ]),
                    Forms\Components\TextInput::make('cta_label')->label('Текст кнопки, если тарифа нет')->maxLength(255),
                ]),
                Forms\Components\Tabs\Tab::make('Шаги')->badge(fn (?Section $record) => $record?->steps()->count() ?: null)->schema([
                    Forms\Components\TextInput::make('steps_title')->label('Заголовок блока')->maxLength(255)->placeholder('Начнём с вашего портала.'),
                    Forms\Components\Repeater::make('steps')->relationship()->hiddenLabel()
                        ->orderColumn('sort')->reorderableWithButtons()->collapsible()
                        ->itemLabel(fn (array $state) => $state['title'] ?? null)
                        ->addActionLabel('Добавить шаг')
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('Шаг')->required()->maxLength(255),
                            Forms\Components\TextInput::make('text')->label('Пояснение')->maxLength(500),
                        ])->columns(2),
                ]),
                Forms\Components\Tabs\Tab::make('SEO')->schema(SeoFields::make()),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('nav_label')->label('Раздел')->weight('bold')
                    ->description(fn (Section $record) => Str::limit(str_replace("\n", ' ', $record->title), 60)),
                Tables\Columns\TextColumn::make('path')->label('Адрес')
                    ->formatStateUsing(fn (Section $record) => $record->isExternal() ? $record->external_url : $record->url())
                    ->url(fn (Section $record) => $record->url(), true)->color('gray'),
                Tables\Columns\IconColumn::make('is_published')->label('Опубликован')->boolean(),
                Tables\Columns\IconColumn::make('in_nav')->label('В меню')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Изменён')->since()->color('gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')->iconButton()->tooltip('Открыть на сайте')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Section $record) => $record->url(), true)->color('gray'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->hidden(fn (Section $record) => array_key_exists($record->key, Section::KEYS)),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}

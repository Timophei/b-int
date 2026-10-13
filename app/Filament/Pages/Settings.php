<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Контакты, уведомления о заявках, реквизиты и счётчики.
 *
 * @property Form $form
 */
class Settings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Настройки';

    protected static ?string $title = 'Настройки сайта';

    protected static ?string $slug = 'nastroiki';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::current()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Section::make('Контакты')->description('Показываются в подвале и на странице «Написать». Пустые не показываются.')->columns(2)->schema([
                Forms\Components\TextInput::make('company_name')->label('Название компании')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->label('Почта')->email()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label('Телефон')->tel()->maxLength(40),
                Forms\Components\TextInput::make('telegram')->label('Telegram')->maxLength(120)->placeholder('@username или ссылка'),
                Forms\Components\TextInput::make('whatsapp')->label('WhatsApp')->maxLength(40)->placeholder('+7 900 000-00-00'),
                Forms\Components\TextInput::make('max_link')->label('MAX')->url()->maxLength(255)->placeholder('Ссылка на профиль'),
            ]),
            Forms\Components\Section::make('Заявки')->schema([
                Forms\Components\TextInput::make('notify_email')->label('Присылать заявки на почту')->email()->maxLength(255)
                    ->helperText('Заявки всегда сохраняются в разделе «Заявки». Письма уходят, если на сервере настроена почта (MAIL_* в .env).'),
            ]),
            Forms\Components\Section::make('Подвал')->schema([
                Forms\Components\Textarea::make('requisites')->label('Реквизиты')->rows(2)->maxLength(1000)->placeholder('ИП Иванов И. И., ИНН …'),
            ]),
            Forms\Components\Section::make('Счётчики')->columns(1)->schema([
                Forms\Components\TextInput::make('metrika_id')->label('Номер счётчика Яндекс.Метрики')->numeric()->maxLength(20)
                    ->helperText('Своя аналитика в админке работает и без Метрики.'),
                Forms\Components\Textarea::make('head_code')->label('Код в <head>')->rows(3)
                    ->helperText('Метатеги подтверждения для Яндекс.Вебмастера, Google и т.п. Вставляется только на основном домене.'),
            ]),
        ]);
    }

    public function save(): void
    {
        Setting::current()->update($this->form->getState());
        Notification::make()->title('Сохранено')->success()->send();
    }
}

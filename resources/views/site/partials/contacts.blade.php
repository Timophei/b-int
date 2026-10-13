{{-- Контакты из настроек; каждое нажатие считается в аналитике --}}
@php($compact ??= false)
<ul @class(['contacts', 'contacts-compact' => $compact])>
    @if ($settings->email)<li><a href="mailto:{{ $settings->email }}" data-track="email">{{ $settings->email }}</a></li>@endif
    @if ($settings->phoneHref())<li><a href="{{ $settings->phoneHref() }}" data-track="phone">{{ $settings->phone }}</a></li>@endif
    @if ($settings->telegramUrl())<li><a href="{{ $settings->telegramUrl() }}" target="_blank" rel="noopener" data-track="telegram">Telegram</a></li>@endif
    @if ($settings->whatsappUrl())<li><a href="{{ $settings->whatsappUrl() }}" target="_blank" rel="noopener" data-track="whatsapp">WhatsApp</a></li>@endif
    @if ($settings->max_link)<li><a href="{{ $settings->max_link }}" target="_blank" rel="noopener" data-track="max">MAX</a></li>@endif
</ul>

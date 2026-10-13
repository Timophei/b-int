@php($contact = \App\Models\Section::byKey('contact'))
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <defs>
        <linearGradient id="mark-g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2bb4ea"/><stop offset="1" stop-color="#0f7fc0"/></linearGradient>
        <symbol id="mark" viewBox="0 0 40 40">
            <circle cx="13.5" cy="10" r="8" fill="url(#mark-g)"/>
            <circle cx="26" cy="20" r="7" fill="url(#mark-g)" opacity=".9"/>
            <circle cx="13" cy="30.5" r="8.5" fill="url(#mark-g)"/>
            <circle cx="30.5" cy="8" r="3.2" fill="#2bb4ea"/>
        </symbol>
        <symbol id="i-arrow-out" viewBox="0 0 16 16"><path d="M4.5 11.5l7-7M6 4.5h5.5V10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="i-arrow-right" viewBox="0 0 16 16"><path d="M2.5 8h11M9 3.5L13.5 8 9 12.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="i-check" viewBox="0 0 20 20"><circle cx="10" cy="10" r="8.5" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M6.3 10.2l2.4 2.4 5-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    </defs>
</svg>
<header class="header">
    <div class="wrap header-in">
        @include('site.partials.brand')
        <nav class="nav" aria-label="Разделы">
            @foreach (\App\Models\Section::navigation() as $item)
                @if ($item->isExternal())
                    <a href="{{ $item->url() }}" target="_blank" rel="noopener" data-track="external" data-label="{{ $item->nav_label }}">{{ $item->nav_label }}<svg class="i"><use href="#i-arrow-out"/></svg></a>
                @else
                    <a href="{{ $item->url() }}" @if (isset($section) && $section?->is($item)) aria-current="page" @endif>{{ $item->nav_label }}</a>
                @endif
            @endforeach
        </nav>
        @if ($contact)
            <a class="header-cta" href="{{ $contact->url() }}" data-track="cta" data-label="Шапка: {{ $contact->nav_label }}">{{ $contact->nav_label }}<svg class="i"><use href="#i-arrow-out"/></svg></a>
        @endif
    </div>
</header>

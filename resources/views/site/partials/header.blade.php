@php($contact = \App\Models\Section::byKey('contact'))
@php($menu = \App\Models\Section::navigation())
@include('site.partials.icons')
<header class="header">
    <div class="wrap header-in">
        @include('site.partials.logo')
        <nav class="nav" id="site-menu" aria-label="Разделы">
            <ul>
                @foreach ($menu as $item)
                    <li>
                        @if ($item->isExternal())
                            <a href="{{ $item->url() }}" target="_blank" rel="noopener" data-track="external" data-label="{{ $item->nav_label }}">{{ $item->nav_label }}<svg class="i"><use href="#i-arrow-out"/></svg></a>
                        @else
                            <a href="{{ $item->url() }}" @if (isset($section) && $section?->is($item)) aria-current="page" @endif>{{ $item->nav_label }}</a>
                        @endif
                    </li>
                @endforeach
                @if ($contact)
                    <li class="nav-cta"><a href="{{ $contact->url() }}" data-track="cta" data-label="Меню: {{ $contact->nav_label }}" @if (isset($section) && $section?->is($contact)) aria-current="page" @endif>{{ $contact->nav_label }}<svg class="i"><use href="#i-arrow-out"/></svg></a></li>
                @endif
            </ul>
        </nav>
        <button class="burger" type="button" aria-controls="site-menu" aria-expanded="false" aria-label="Меню" data-menu-toggle>
            <svg class="burger-open"><use href="#i-menu"/></svg>
            <svg class="burger-close"><use href="#i-close"/></svg>
        </button>
    </div>
</header>

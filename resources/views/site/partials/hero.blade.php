{{-- Тёмная шапка с фоновой картинкой раздела. Кнопка — если в админке задан её текст. --}}
@php($big ??= false)
@php($image = $section->heroImage())
@php($contact = \App\Models\Section::byKey('contact'))
<section @class(['hero', 'hero-big' => $big, 'hero-'.$section->key])>
    <img class="hero-photo" src="{{ $image['src'] }}" @if ($image['srcset']) srcset="{{ $image['srcset'] }}" sizes="(max-width: 600px) 300vw, 100vw" @endif
         alt="" fetchpriority="high">
    <div class="wrap hero-in">
        <div class="hero-text">
            @if ($eyebrow ?? $section->eyebrow)<p class="eyebrow">{{ $eyebrow ?? $section->eyebrow }}</p>@endif
            <h1 class="hero-title">{{ \App\Support\Typo::title($section->title) }}</h1>
            @if ($section->subtitle)<p class="hero-sub">{{ \App\Support\Typo::title($section->subtitle, $accentSub ?? true) }}</p>@endif
            @if ($section->lead)<p class="hero-lead">{{ $section->lead }}</p>@endif
            @if ($section->hero_button && $contact)
                <a class="btn-ghost" href="{{ $contact->url() }}?tema={{ $section->key }}" data-track="cta" data-label="{{ $section->nav_label }}: {{ $section->hero_button }}">
                    {{ $section->hero_button }}<svg class="i"><use href="#i-arrow-out"/></svg>
                </a>
            @elseif ($big)
                <a class="hero-down" href="#content" aria-label="К содержанию"><svg><use href="#i-arrow-down"/></svg></a>
            @endif
        </div>
    </div>
</section>

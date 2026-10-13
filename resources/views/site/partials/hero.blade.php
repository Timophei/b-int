{{-- Тёмная шапка: подложка с кольцом над водой или своя картинка раздела из админки --}}
@php($big ??= false)
<section @class(['hero', 'hero-big' => $big])>
    @if ($section->cover)
        <img class="hero-photo" src="/storage/{{ $section->cover }}" alt="" fetchpriority="high">
    @else
        <img class="hero-photo hero-photo-default" src="/img/hero.jpg" srcset="/img/hero-1024.jpg 1024w, /img/hero.jpg 2048w"
             sizes="(max-width: 600px) 300vw, 100vw" alt="" width="2048" height="768" fetchpriority="high">
    @endif
    <div class="wrap hero-in">
        <div class="hero-text">
            @if ($eyebrow ?? $section->eyebrow)<p class="eyebrow">{{ $eyebrow ?? $section->eyebrow }}</p>@endif
            <h1 class="hero-title">{{ \App\Support\Typo::title($section->title) }}</h1>
            @if ($section->subtitle)<p class="hero-sub">{{ \App\Support\Typo::title($section->subtitle) }}</p>@endif
            @if ($section->lead)<p class="hero-lead">{{ $section->lead }}</p>@endif
            @if ($big)<a class="hero-down" href="#content" aria-label="К содержанию"><svg><use href="#i-arrow-down"/></svg></a>@endif
        </div>
    </div>
</section>

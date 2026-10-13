{{-- Тёмная шапка раздела. Без своей картинки — фирменная ночная гладь с кольцом. --}}
@php($big ??= false)
<section @class(['hero', 'hero-big' => $big])>
    <div class="hero-bg" aria-hidden="true">
        @if ($section->cover)
            <img src="/storage/{{ $section->cover }}" alt="" fetchpriority="high">
        @else
            @include('site.partials.scene')
        @endif
    </div>
    <div class="wrap hero-in">
        @if ($eyebrow ?? $section->eyebrow)<p class="eyebrow">{{ $eyebrow ?? $section->eyebrow }}</p>@endif
        @if ($big)
            <h1 class="hero-title">{{ \App\Support\Typo::title($section->title) }}</h1>
        @else
            <h1 class="hero-title hero-title-sm">{{ \App\Support\Typo::title($section->title) }}</h1>
        @endif
        @if ($section->subtitle)<p class="hero-sub">{{ \App\Support\Typo::title($section->subtitle) }}</p>@endif
        @if ($section->lead)<p class="hero-lead">{{ $section->lead }}</p>@endif
        @if ($big)<a class="hero-down" href="#content" aria-label="К содержанию"><svg viewBox="0 0 24 40"><path d="M12 2v34M3 27l9 9 9-9" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>@endif
    </div>
</section>

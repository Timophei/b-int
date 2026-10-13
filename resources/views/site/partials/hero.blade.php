{{-- Тёмная шапка. Кольцо — фрагмент из макета справа, слева продолжение тёмной воды.
     Своя картинка из админки (широкая) заменяет и то и другое. --}}
@php($big ??= false)
<section @class(['hero', 'hero-big' => $big, 'hero-cover' => $section->cover])>
    @if ($section->cover)
        <img class="hero-photo" src="/storage/{{ $section->cover }}" alt="" fetchpriority="high">
    @endif
    <div class="wrap hero-in">
        @unless ($section->cover)
            <img class="hero-ring" src="/img/hero-ring.png" alt="" width="168" height="326" fetchpriority="high">
        @endunless
        <div class="hero-text">
            @if ($eyebrow ?? $section->eyebrow)<p class="eyebrow">{{ $eyebrow ?? $section->eyebrow }}</p>@endif
            <h1 class="hero-title">{{ \App\Support\Typo::title($section->title) }}</h1>
            @if ($section->subtitle)<p class="hero-sub">{{ \App\Support\Typo::title($section->subtitle) }}</p>@endif
            @if ($section->lead)<p class="hero-lead">{{ $section->lead }}</p>@endif
            @if ($big)<a class="hero-down" href="#content" aria-label="К содержанию"><svg><use href="#i-arrow-down"/></svg></a>@endif
        </div>
    </div>
</section>

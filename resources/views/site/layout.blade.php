<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $section ??= null;
        $root = config('app.canonical_root');
        $canonical = $root.(request()->path() === '/' ? '/' : '/'.trim(request()->path(), '/').'/');
        $plainTitle = trim(str_replace("\n", ' ', (string) $section?->title));
        $pageTitle = $section?->seo_title ?: trim($plainTitle.' — '.$settings->company_name, ' —');
        $pageDescription = Str::squish($section?->seo_description ?: ($section?->lead ?: 'Поддержка, обновление и восстановление коробочных порталов Битрикс24.'));
        $ogImage = $section?->og_image ?: $section?->cover;
        $ogImageUrl = $ogImage ? (str_starts_with($ogImage, 'http') ? $ogImage : $root.'/storage/'.$ogImage) : $root.'/img/og.png';
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if ($section?->seo_keywords)<meta name="keywords" content="{{ $section->seo_keywords }}">@endif
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="icon" href="/img/icon.png" type="image/png">
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <meta name="theme-color" content="#ffffff">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $settings->company_name }}">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ Str::squish($section?->og_title ?: $pageTitle) }}">
    <meta property="og:description" content="{{ Str::squish($section?->og_description ?: $pageDescription) }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="/css/site.css?v={{ @filemtime(public_path('css/site.css')) }}">
    @if (\App\Http\Controllers\SeoController::isMainHost(request()) && $settings->head_code){!! $settings->head_code !!}@endif
    @php
        $organization = array_filter([
            '@'.'context' => 'https://schema.org', '@'.'type' => 'Organization', 'name' => $settings->company_name, 'url' => $root,
            'email' => $settings->email, 'telephone' => $settings->phone, 'logo' => $root.'/img/logo-header.png',
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($organization, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</head>
<body class="@yield('body_class')">
    @auth
        @if ($section && ! $section->is_published)<div class="draft-bar">Черновик — гости этот раздел не видят</div>@endif
    @endauth
    @include('site.partials.header')
    <main>
        @yield('content')
    </main>
    @include('site.partials.footer')
    <script src="/js/site.js?v={{ @filemtime(public_path('js/site.js')) }}" defer></script>
    @include('site.partials.metrika')
</body>
</html>

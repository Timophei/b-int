@extends('site.layout')

{{-- «ИИ сотрудники» по макету: примеры, акцент, возможности, призыв, запуск, цены, финальный баннер.
     Каждый блок выводится, только если для него заполнены данные в админке. --}}
@section('content')
    @include('site.partials.hero', ['accentSub' => false])

    @php
        $contact = \App\Models\Section::byKey('contact');
        $ctaUrl = $contact ? $contact->url().'?tema='.$section->key : null;
        $setup = $section->plans->firstWhere('group', 'setup');
        $tiers = $section->plans->where('group', 'monthly')->values();
        $number = 0;
        $kicker = function (?string $label) use (&$number) {
            $number++;

            return str_pad((string) $number, 2, '0', STR_PAD_LEFT);
        };
    @endphp

    <div class="wrap" id="content">
        @if ($section->cases->isNotEmpty())
            <section class="block" aria-labelledby="cases-title">
                <p class="kicker"><span class="num">{{ $kicker($section->cases_kicker) }}</span><span class="slash">/</span>{{ $section->cases_kicker ?: 'Примеры' }}</p>
                @if ($section->cases_title)<h2 class="h-support" id="cases-title">{{ \App\Support\Typo::title($section->cases_title) }}</h2>@endif
                <ol class="tiles">
                    @foreach ($section->cases as $case)
                        <li class="tile">
                            <span class="num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            @if ($case->icon)<span class="tile-icon"><svg><use href="#ic-{{ $case->icon }}"/></svg></span>@endif
                            <div class="tile-body">
                                <h3>{{ $case->title }}</h3>
                                @if ($case->tag)<span class="tag">{{ $case->tag }}</span>@endif
                                @if ($case->text)<p>{{ $case->text }}</p>@endif
                                @if ($case->link_url)
                                    <a class="tile-link" href="{{ $case->link_url }}" target="_blank" rel="noopener" data-track="external" data-label="Пример: {{ $case->link_label ?: $case->link_url }}">
                                        {{ $case->link_label ?: preg_replace('#^https?://#', '', $case->link_url) }}<svg class="i"><use href="#i-arrow-out"/></svg>
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if ($section->highlight_title)
            <section class="split">
                <h2 class="h2 split-title">{{ \App\Support\Typo::title($section->highlight_title) }}</h2>
                @if ($section->highlight_text)<p class="split-text">{{ $section->highlight_text }}</p>@endif
            </section>
        @endif

        @if ($section->services->isNotEmpty())
            <section class="block block-lined" aria-labelledby="services-title">
                <p class="kicker"><span class="num">{{ $kicker($section->services_kicker) }}</span><span class="slash">/</span>{{ $section->services_kicker ?: 'Возможности' }}</p>
                @if ($section->services_title)<h2 class="h-support" id="services-title">{{ \App\Support\Typo::title($section->services_title) }}</h2>@endif
                <ol class="caps">
                    @foreach ($section->services as $service)
                        <li>
                            <span class="num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $service->title }}</h3>
                            @if ($service->description)<p>{{ $service->description }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if ($section->band_title)
            <section class="cta-light">
                <div>
                    <h2 class="h2">{{ \App\Support\Typo::title($section->band_title) }}</h2>
                    @if ($section->band_text)<p>{{ $section->band_text }}</p>@endif
                </div>
                @if ($ctaUrl)
                    <a class="btn" href="{{ $ctaUrl }}" data-track="cta" data-label="{{ $section->nav_label }}: {{ $section->cta_label ?: 'Обсудить задачу' }}">
                        {{ $section->cta_label ?: 'Обсудить задачу' }}<svg class="i"><use href="#i-arrow-out"/></svg>
                    </a>
                @endif
            </section>
        @endif

        @if ($section->steps->isNotEmpty())
            <section class="block" aria-labelledby="launch-title">
                <p class="kicker"><span class="num">{{ $kicker($section->steps_kicker) }}</span><span class="slash">/</span>{{ $section->steps_kicker ?: 'Запуск' }}</p>
                @if ($section->steps_title)<h2 class="h-support" id="launch-title">{{ \App\Support\Typo::title($section->steps_title) }}</h2>@endif
                <ol class="tiles tiles-steps">
                    @foreach ($section->steps as $step)
                        <li class="tile">
                            <span class="num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            @if ($step->icon)<span class="tile-icon"><svg><use href="#ic-{{ $step->icon }}"/></svg></span>@endif
                            <div class="tile-body">
                                <h3>{{ $step->title }}</h3>
                                @if ($step->text)<p>{{ $step->text }}</p>@endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if ($setup)
            <section class="setup">
                <div class="setup-main">
                    <h2 class="h2">{{ $setup->name }}</h2>
                    @if ($setup->note)<p>{{ $setup->note }}</p>@endif
                </div>
                <p class="setup-price"><b>{{ $setup->price }}</b>@if ($setup->period)<span>{{ $setup->period }}</span>@endif</p>
            </section>
        @endif

        @if ($tiers->isNotEmpty())
            <section class="tiers-block">
                @if ($section->plans_title)<h2 class="h-support accent-top">{{ \App\Support\Typo::title($section->plans_title) }}</h2>@endif
                <ul class="tiers">
                    @foreach ($tiers as $tier)
                        <li>
                            @if ($tier->name)<h3>{{ $tier->name }}</h3>@endif
                            <p class="tier-price"><b>{{ $tier->price }}</b>@if ($tier->period)<span>{{ $tier->period }}</span>@endif</p>
                            @if ($tier->note)<p class="tier-note">{{ $tier->note }}</p>@endif
                        </li>
                    @endforeach
                </ul>
                @if ($section->plans_note)<p class="tiers-note">{{ $section->plans_note }}</p>@endif
            </section>
        @endif

        @if ($section->final_title)
            <section class="cta-dark">
                <img src="/img/hero-1024.jpg" srcset="/img/hero-1024.jpg 1024w, /img/hero.jpg 2048w" sizes="(max-width: 1240px) 100vw, 1180px" alt="" loading="lazy">
                <div class="cta-dark-in">
                    <h2 class="h2">{{ \App\Support\Typo::title($section->final_title) }}</h2>
                    @if ($section->final_text)<p>{{ $section->final_text }}</p>@endif
                    @if ($ctaUrl)
                        <a class="btn-ghost" href="{{ $ctaUrl }}" data-track="cta" data-label="{{ $section->nav_label }}: {{ $section->final_button ?: 'Обсудить' }}">
                            {{ $section->final_button ?: 'Обсудить' }}<svg class="i"><use href="#i-arrow-out"/></svg>
                        </a>
                    @endif
                </div>
            </section>
        @endif

        @if ($section->body)
            <section class="prose">{!! $section->body !!}</section>
        @endif
    </div>
@endsection

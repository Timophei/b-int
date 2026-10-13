{{-- Содержимое раздела: услуги с тарифом, акцентный блок, «Как работаем», шаги. На главной — с заголовком раздела. --}}
@php($withHeading ??= false)
<section class="block" @if ($withHeading) aria-labelledby="landing-{{ $section->key }}" @endif>
    @if ($withHeading)
        <p class="kicker"><span class="num">{{ $section->number() }}</span><span class="slash">/</span>{{ $section->eyebrow ?: $section->nav_label }}</p>
        <h2 class="h-support" id="landing-{{ $section->key }}">{{ \App\Support\Typo::title($section->title) }}</h2>
    @endif
    @if ($section->services->isNotEmpty() || $section->plans->isNotEmpty() || $section->cta_label)
        @include('site.partials.offer')
    @endif
</section>

@if ($section->highlight_title || $section->process)
    <div class="pair">
        @if ($section->highlight_title)
            <section class="highlight">
                <h2 class="h2">{{ \App\Support\Typo::title($section->highlight_title) }}</h2>
                @if ($section->highlight_text)<p class="muted-lg">{{ $section->highlight_text }}</p>@endif
            </section>
        @endif
        @if ($section->process)
            <section class="process">
                @if ($section->process_title)<h2 class="h2">{{ \App\Support\Typo::title($section->process_title) }}</h2>@endif
                <ol class="numbered numbered-lines">
                    @foreach ($section->process as $item)
                        <li><span class="num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><p>{{ $item }}</p></li>
                    @endforeach
                </ol>
            </section>
        @endif
    </div>
@endif

@if ($showBody ?? false)
    @if ($section->body)<section class="prose">{!! $section->body !!}</section>@endif
@endif

@include('site.partials.steps')

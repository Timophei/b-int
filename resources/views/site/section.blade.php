@extends('site.layout')

@section('content')
    @include('site.partials.hero', ['eyebrow' => $section->number().' / '.($section->eyebrow ?: $section->nav_label)])

    <div class="wrap" id="content">
        @if ($section->services->isNotEmpty() || $section->plans->isNotEmpty())
            <section class="block">
                @include('site.partials.offer')
            </section>
        @endif

        @if ($section->plans->count() > 1)
            <div class="plans-more">
                @foreach ($section->plans->skip(1) as $plan)
                    <div class="plan plan-flat">
                        @if ($plan->name)<p class="plan-name">{{ $plan->name }}</p>@endif
                        <p class="plan-price"><b>{{ $plan->price }}</b>@if ($plan->period)<span>{{ $plan->period }}</span>@endif</p>
                        @if ($plan->features)
                            <ul class="plan-features">@foreach ($plan->features as $feature)<li><svg class="i-check"><use href="#i-check"/></svg>{{ $feature }}</li>@endforeach</ul>
                        @endif
                        @if ($plan->note)<p class="plan-note">{{ $plan->note }}</p>@endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($section->body)
            <section class="prose">{!! $section->body !!}</section>
        @endif

        @include('site.partials.steps')

        @php($others = \App\Models\Section::navigation()->reject(fn ($s) => $s->is($section)))
        @if ($others->isNotEmpty())
            <h2 class="h-mid rows-title">Ещё у нас<span class="dot">.</span></h2>
            @include('site.partials.rows', ['rows' => $others])
        @endif
    </div>
@endsection

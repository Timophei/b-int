@extends('site.layout')
@section('body_class', 'is-home')

@section('content')
    @include('site.partials.hero', ['big' => true])

    <div class="wrap" id="content">
        @if ($featured)
            <section class="block">
                <div class="block-head">
                    <p class="kicker"><span class="num">{{ $featured->number() }}</span><span class="slash">/</span>{{ $featured->nav_label }}</p>
                    <a class="kicker-link" href="{{ $featured->url() }}">Подробнее<svg class="i"><use href="#i-arrow-right"/></svg></a>
                </div>
                <h2 class="h-big"><a href="{{ $featured->url() }}">{{ \App\Support\Typo::title($featured->title) }}</a></h2>
                @include('site.partials.offer', ['section' => $featured, 'plan' => $featured->plan])
            </section>
        @endif

        @include('site.partials.steps')

        @include('site.partials.rows', ['rows' => $sections->reject(fn ($s) => $featured && $s->is($featured))])
    </div>
@endsection

@extends('site.layout')

@section('content')
    @include('site.partials.hero', ['eyebrow' => $section->number().' / '.($section->eyebrow ?: $section->nav_label)])

    <div class="wrap" id="content">
        @include('site.partials.landing', ['showBody' => true])
    </div>
@endsection

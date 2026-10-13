@extends('site.layout')
@section('body_class', 'is-home')

@section('content')
    @include('site.partials.hero', ['big' => true])

    <div class="wrap" id="content">
        @if ($featured)
            @include('site.partials.landing', ['section' => $featured, 'withHeading' => true])
        @else
            @include('site.partials.steps')
        @endif
    </div>
@endsection

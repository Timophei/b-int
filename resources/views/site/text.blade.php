@extends('site.layout')

@section('content')
    <div class="wrap page-text">
        <h1 class="h-big">{{ \App\Support\Typo::title($section->title) }}</h1>
        <div class="prose">{!! $section->body !!}</div>
    </div>
@endsection

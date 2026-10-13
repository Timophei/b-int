@extends('site.layout')

@section('content')
    @php($topic = old('topic', request('tema')))
    @php($privacy = \App\Models\Section::byKey('privacy'))
    <div class="wrap contact">
        <div class="contact-intro">
            @if ($section->eyebrow)<p class="kicker">{{ $section->eyebrow }}</p>@endif
            <h1 class="h-big">{{ \App\Support\Typo::title($section->title) }}</h1>
            @if ($section->lead)<p class="lead">{{ $section->lead }}</p>@endif
            @include('site.partials.contacts')
            @if ($section->body)<div class="prose">{!! $section->body !!}</div>@endif
        </div>

        <div class="contact-form">
            @if (session('sent'))
                <div class="sent" role="status">
                    <svg class="i-check"><use href="#i-check"/></svg>
                    <div><h2>Спасибо, заявка у нас.</h2><p>Скоро свяжемся по указанному контакту.</p></div>
                </div>
            @else
                <form method="post" action="{{ route('lead') }}" class="form" data-lead-form>
                    @csrf
                    <input type="hidden" name="from" value="">
                    <input type="hidden" name="q" value="">
                    <input type="hidden" name="r" value="">
                    <div class="hp" aria-hidden="true"><label>Сайт <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                    @if ($topics->isNotEmpty())
                        <fieldset class="topics">
                            <legend>Что интересует</legend>
                            @foreach ($topics as $item)
                                <label class="chip"><input type="radio" name="topic" value="{{ $item->key }}" @checked($topic === $item->key)><span>{{ $item->nav_label }}</span></label>
                            @endforeach
                            <label class="chip"><input type="radio" name="topic" value="other" @checked($topic === 'other')><span>Другое</span></label>
                        </fieldset>
                    @endif

                    <label class="field">
                        <span>Как к вам обращаться</span>
                        <input type="text" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
                        @error('name')<em class="error">{{ $message }}</em>@enderror
                    </label>
                    <label class="field">
                        <span>Телефон, почта или Telegram</span>
                        <input type="text" name="contact" value="{{ old('contact') }}" required maxlength="190" autocomplete="tel">
                        @error('contact')<em class="error">{{ $message }}</em>@enderror
                    </label>
                    <label class="field">
                        <span>Компания <i>необязательно</i></span>
                        <input type="text" name="company" value="{{ old('company') }}" maxlength="190" autocomplete="organization">
                    </label>
                    <label class="field">
                        <span>Задача <i>необязательно</i></span>
                        <textarea name="message" rows="4" maxlength="5000" placeholder="Например: редакция портала, что беспокоит, есть ли интеграция с 1С">{{ old('message') }}</textarea>
                    </label>
                    <label class="consent">
                        <input type="checkbox" name="consent" value="1" required @checked(old('consent'))>
                        <span>Согласен на обработку персональных данных@if ($privacy && $privacy->is_published) по <a href="{{ $privacy->url() }}" target="_blank">политике конфиденциальности</a>@endif</span>
                    </label>
                    @error('consent')<em class="error">{{ $message }}</em>@enderror
                    <button class="btn" type="submit">{{ $section->cta_label ?: 'Отправить' }}<svg class="i"><use href="#i-arrow-out"/></svg></button>
                </form>
            @endif
        </div>
    </div>
@endsection

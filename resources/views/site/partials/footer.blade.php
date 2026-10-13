@php($privacy = \App\Models\Section::byKey('privacy'))
<footer class="footer">
    <div class="wrap footer-in">
        <a class="logo logo-footer" href="/" aria-label="{{ $settings->company_name }} — на главную">
            <img src="/img/logo-footer.png" alt="{{ $settings->company_name }}" width="146" height="42" loading="lazy">
        </a>
        @include('site.partials.contacts', ['compact' => true])
        <div class="footer-meta">
            <span>© {{ date('Y') }} {{ $settings->company_name }}</span>
            @if ($settings->requisites)<span>{{ $settings->requisites }}</span>@endif
            @if ($privacy && $privacy->is_published)<a href="{{ $privacy->url() }}">{{ $privacy->nav_label }}</a>@endif
        </div>
    </div>
</footer>

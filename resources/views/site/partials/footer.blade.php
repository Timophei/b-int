@php($privacy = \App\Models\Section::byKey('privacy'))
<footer class="footer">
    <div class="wrap footer-in">
        @include('site.partials.brand')
        <div class="footer-contacts">
            @include('site.partials.contacts', ['compact' => true])
        </div>
        <div class="footer-meta">
            <span>© {{ date('Y') }} {{ $settings->company_name }}</span>
            @if ($settings->requisites)<span>{{ $settings->requisites }}</span>@endif
            @if ($privacy && $privacy->is_published)<a href="{{ $privacy->url() }}">{{ $privacy->nav_label }}</a>@endif
        </div>
    </div>
</footer>

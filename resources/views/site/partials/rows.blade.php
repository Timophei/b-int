{{-- Строки разделов: номер, название, короткое описание, стрелка --}}
<div class="rows">
    @foreach ($rows as $row)
        <a class="row" href="{{ $row->url() }}"
           @if ($row->isExternal()) target="_blank" rel="noopener" data-track="external" data-label="{{ $row->nav_label }}" @endif>
            <span class="num">{{ $row->number() }}</span>
            <span class="row-title">{{ $row->nav_label }}@if ($row->isExternal())<svg class="i"><use href="#i-arrow-out"/></svg>@endif</span>
            <span class="row-text">{{ $row->teaser }}</span>
            <span class="row-go"><svg class="i"><use href="{{ $row->isExternal() ? '#i-arrow-out' : '#i-arrow-right' }}"/></svg></span>
        </a>
    @endforeach
</div>

{{-- Услуги, карточка тарифа (или приглашение обсудить) и сноска под карточкой --}}
@php($plan = $plan ?? $section->plans->first())
@php($contact = \App\Models\Section::byKey('contact'))
@php($buttonLabel = $plan?->button_label ?: ($section->cta_label ?: 'Обсудить'))
<div class="offer">
    @if ($section->services->isNotEmpty())
        <ul class="services">
            @foreach ($section->services as $service)
                <li>
                    <h3>{{ $service->title }}</h3>
                    @if ($service->description)<p>{{ $service->description }}</p>@endif
                </li>
            @endforeach
        </ul>
    @endif
    <div class="offer-side">
        <aside class="card" aria-label="{{ $plan ? 'Стоимость' : 'Обсудить' }}">
            @if ($plan)
                @if ($plan->name)<p class="card-name">{{ $plan->name }}</p>@endif
                <p class="price"><b>{{ $plan->price }}</b>@if ($plan->period)<span>{{ $plan->period }}</span>@endif</p>
                @if ($plan->features)
                    <ul class="features">
                        @foreach ($plan->features as $feature)
                            <li><svg class="i-check"><use href="#i-check"/></svg>{{ $feature }}</li>
                        @endforeach
                    </ul>
                @endif
            @else
                <p class="price price-text"><b>Обсудим задачу</b></p>
                <p class="card-text">Посчитаем объём и сроки после короткого знакомства.</p>
            @endif
            @if ($contact)
                <a class="btn" href="{{ $contact->url() }}?tema={{ $section->key }}" data-track="cta" data-label="{{ $section->nav_label }}: {{ $buttonLabel }}">
                    {{ $buttonLabel }}<svg class="i"><use href="#i-arrow-out"/></svg>
                </a>
            @endif
            @if ($plan?->offerPdfUrl())
                <a class="pdf" href="{{ $plan->offerPdfUrl() }}" target="_blank" rel="noopener" data-track="cta" data-label="{{ $section->nav_label }}: оффер PDF">
                    <svg class="i-pdf"><use href="#i-pdf"/></svg><span>{{ $plan->offer_pdf_label ?: 'Скачать оффер · PDF' }}</span>
                </a>
            @endif
        </aside>
        @if ($plan?->note)<p class="card-note">{{ $plan->note }}</p>@endif
    </div>
</div>

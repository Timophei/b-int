{{-- Список услуг раздела и карточка тарифа справа (или приглашение обсудить, если тарифа нет) --}}
@php($plan = $plan ?? $section->plans->first())
@php($contact = \App\Models\Section::byKey('contact'))
<div class="offer">
    <ul class="services">
        @foreach ($section->services as $service)
            <li>
                <h3>{{ $service->title }}</h3>
                @if ($service->description)<p>{{ $service->description }}</p>@endif
            </li>
        @endforeach
    </ul>
    <aside class="plan">
        @if ($plan)
            @if ($plan->name)<p class="plan-name">{{ $plan->name }}</p>@endif
            <p class="plan-price"><b>{{ $plan->price }}</b>@if ($plan->period)<span>{{ $plan->period }}</span>@endif</p>
            @if ($plan->features)
                <ul class="plan-features">
                    @foreach ($plan->features as $feature)
                        <li><svg class="i-check"><use href="#i-check"/></svg>{{ is_array($feature) ? ($feature['text'] ?? '') : $feature }}</li>
                    @endforeach
                </ul>
            @endif
        @else
            <p class="plan-price plan-price-text"><b>Обсудим задачу</b></p>
            <p class="plan-text">Посчитаем объём и сроки после короткого знакомства.</p>
        @endif
        @if ($contact)
            <a class="btn" href="{{ $contact->url() }}?tema={{ $section->key }}" data-track="cta" data-label="{{ $section->nav_label }}: {{ $plan?->button_label ?: ($section->cta_label ?: 'Обсудить') }}">
                {{ $plan?->button_label ?: ($section->cta_label ?: 'Обсудить') }}<svg class="i"><use href="#i-arrow-out"/></svg>
            </a>
        @endif
        @if ($plan?->note)<p class="plan-note">{{ $plan->note }}</p>@endif
    </aside>
</div>

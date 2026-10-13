@if ($section->steps->isNotEmpty())
    <div class="steps-block">
        @if ($section->steps_title)<h2 class="h-mid">{{ \App\Support\Typo::title($section->steps_title) }}</h2>@endif
        <ol class="steps">
            @foreach ($section->steps as $step)
                <li>
                    <span class="num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <h3>{{ $step->title }}</h3>
                        @if ($step->text)<p>{{ $step->text }}</p>@endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
@endif

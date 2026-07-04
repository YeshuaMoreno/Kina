@props(['items' => []])

@if (count($items))
    <div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
        @foreach ($items as $item)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border border-rosa/40 bg-rosa/15 text-malva">
                <span class="w-1.5 h-1.5 rounded-full bg-rosa"></span>
                {{ $item }}
            </span>
        @endforeach
    </div>
@endif

@props(['type' => 'info', 'title' => null])

@php
    $styles = [
        'info' => 'bg-lavanda/10 border-lavanda/30 text-malva dark:text-tiza',
        'success' => 'bg-success/10 border-success/30 text-success',
        'error' => 'bg-error/10 border-error/40 text-error',
    ];
    $classes = $styles[$type] ?? $styles['info'];
@endphp

{{-- Alert Kina: aviso calmado, sin colores chillones. --}}
<div role="alert" {{ $attributes->merge(['class' => 'rounded-xl border p-4 text-sm ' . $classes]) }}>
    @if ($title)
        <p class="font-semibold mb-1">{{ $title }}</p>
    @endif
    <div>{{ $slot }}</div>
</div>
